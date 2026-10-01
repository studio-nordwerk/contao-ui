<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\CalendarBundle\Generator\CalendarEventsGenerator;
use Contao\CalendarBundle\Security\ContaoCalendarPermissions;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\NewsBundle\Security\ContaoNewsPermissions;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Source\ArchiveAccess;
use Nordwerk\TeasersBundle\Source\EventsSource;
use Nordwerk\TeasersBundle\Source\NewsSource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class TeaserArchivePermissionsTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function permissions(): iterable
    {
        foreach (['tl_news_archive' => ContaoNewsPermissions::USER_CAN_EDIT_ARCHIVE, 'tl_calendar' => ContaoCalendarPermissions::USER_CAN_EDIT_CALENDAR] as $table => $permission) {
            yield $table.' editor' => [$table, $permission, false];
            yield $table.' admin' => [$table, $permission, true];
        }
    }

    #[DataProvider('permissions')]
    public function testBackendChoicesUseTheCoreArchivePermission(string $table, string $permission, bool $admin): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->method('fetchAllKeyValue')
            ->with('SELECT id, title FROM '.$table.' ORDER BY title')
            ->willReturn([1 => 'Allowed', 2 => 'Restricted'])
        ;
        $security = $this->createMock(Security::class);
        $security
            ->expects($this->exactly(2))
            ->method('isGranted')
            ->willReturnCallback(
                function (string $attribute, mixed $id) use ($permission, $admin): bool {
                    $this->assertSame($permission, $attribute);

                    return $admin || 1 === (int) $id;
                },
            )
        ;
        $access = new ArchiveAccess($connection, $security);
        $source = 'tl_calendar' === $table
            ? new EventsSource($connection, $access, $this->createMock(CalendarEventsGenerator::class))
            : new NewsSource($connection, $access, $this->createMock(ContentUrlGenerator::class));
        $this->assertSame($admin ? [1 => 'Allowed', 2 => 'Restricted'] : [1 => 'Allowed'], $source->getArchives());
    }
}
