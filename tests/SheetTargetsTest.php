<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Doctrine\DBAL\Connection;
use Nordwerk\SheetBundle\Backend\SheetTargets;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SheetTargetsTest extends TestCase
{
    public function testTargetListsDoNotExposeRecordsWithoutReadPermission(): void
    {
        $projectDir = getcwd();
        $this->assertIsString($projectDir);
        ContaoKernel::setProjectDir($projectDir);
        $kernel = new ContaoKernel('dev', true);
        $kernel->boot();
        $framework = $kernel->getContainer()->get('contao.framework');
        $this->assertInstanceOf(ContaoFramework::class, $framework);
        $framework->initialize();
        $db = $kernel->getContainer()->get('database_connection');
        $this->assertInstanceOf(Connection::class, $db);
        $db->beginTransaction();

        try {
            $db->insert('tl_content', ['type' => 'nw_sheet', 'nwSheetLabel' => 'Allowed sheet']);
            $sheetId = (int) $db->lastInsertId();
            $db->insert('tl_content', ['type' => 'nw_sheet', 'nwSheetLabel' => 'Confidential sheet']);
            $db->insert('tl_module', ['type' => 'navigation', 'name' => 'Allowed navigation']);
            $navigationId = (int) $db->lastInsertId();
            $db->insert('tl_module', ['type' => 'navigation', 'name' => 'Confidential navigation']);
            $security = $this->createMock(AuthorizationCheckerInterface::class);
            $security
                ->method('isGranted')
                ->willReturnCallback(static fn (string $attribute, mixed $subject): bool => $subject instanceof ReadAction && match ($attribute) {
                    ContaoCorePermissions::DC_PREFIX.'tl_content' => (int) $subject->getCurrentId() === $sheetId,
                    ContaoCorePermissions::DC_PREFIX.'tl_module' => (int) $subject->getCurrentId() === $navigationId,
                    default => false,
                })
            ;
            $targets = new SheetTargets($security);

            $this->assertSame([$sheetId => '#'.$sheetId.' · Allowed sheet'], $targets->sheets());
            $this->assertSame([$navigationId => '#'.$navigationId.' · Allowed navigation'], $targets->navigations());
        } finally {
            $db->rollBack();
            $kernel->shutdown();
        }
    }
}
