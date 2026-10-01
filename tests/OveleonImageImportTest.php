<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Rendering\PublicFiles;
use Nordwerk\TestimonialsBundle\Import\OveleonImporter;
use PHPUnit\Framework\TestCase;

final class OveleonImageImportTest extends TestCase
{
    public function testImportsLocalPickerTagsAndRootRelativePathsWithoutFetchingRemoteImages(): void
    {
        $projectDir = getcwd();
        $this->assertIsString($projectDir);
        ContaoKernel::setProjectDir($projectDir);
        $kernel = new ContaoKernel('dev', true);
        $kernel->boot();
        $framework = $kernel->getContainer()->get('contao.framework');
        $this->assertInstanceOf(ContaoFramework::class, $framework);
        $framework->initialize();
        $file = FilesModel::findByPath('files/contao-ui-gallery/study-1.jpg');
        $this->assertInstanceOf(FilesModel::class, $file);
        $uuid = StringUtil::binToUuid($file->uuid);
        $importer = new OveleonImporter($this->createMock(Connection::class), new PublicFiles());
        $map = new \ReflectionMethod($importer, 'map');

        foreach ([
            'files/contao-ui-gallery/study-1.jpg' => $file->uuid,
            '/files/contao-ui-gallery/study-1.jpg' => $file->uuid,
            '{{file::'.$uuid.'}}' => $file->uuid,
            '{{file::00000000-0000-0000-0000-000000000000}}' => null,
            '/files/../config/parameters.yaml' => null,
            'https://example.test/image.jpg' => null,
            '//example.test/image.jpg' => null,
        ] as $identifier => $expected) {
            $record = $map->invoke($importer, ['id' => 1, 'pid' => 1, 'author' => 'Fictional reviewer', 'text' => 'Fictional experience', 'rating' => '4', 'date' => time(), 'imageUrl' => $identifier], 2, 'oveleon:1:1');
            $this->assertSame($expected, $record['singleSRC'], $identifier);
        }
    }
}
