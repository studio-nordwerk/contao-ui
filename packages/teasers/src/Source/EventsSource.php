<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Source;

use Contao\CalendarBundle\Generator\CalendarEventsGenerator;
use Contao\CalendarBundle\Security\ContaoCalendarPermissions;
use Doctrine\DBAL\Connection;
use Nordwerk\TeasersBundle\Card\Card;
use Nordwerk\TeasersBundle\Query\TeaserQuery;

final class EventsSource implements TeaserSourceInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ArchiveAccess $access,
        private readonly CalendarEventsGenerator $events,
    ) {
    }

    public function getKey(): string
    {
        return 'events';
    }

    public function getLabel(): string
    {
        return 'nw.teasers.source.events';
    }

    public function getArchives(): array
    {
        return $this->access->backendChoices(array_map('strval', $this->connection->fetchAllKeyValue('SELECT id, title FROM tl_calendar ORDER BY title')), ContaoCalendarPermissions::USER_CAN_EDIT_CALENDAR);
    }

    public function fetch(TeaserQuery $query): iterable
    {
        $now = new \DateTimeImmutable();
        $entries = $this->events->getAllEvents($this->access->allowed('tl_calendar', $query->archives), $now, $now->modify('+2 years'), noSpan: true);
        $cards = [];

        foreach ($entries as $day) {
            foreach ($day as $occurrences) {
                foreach ($occurrences as $event) {
                    // The core generator includes the whole current day and old recurring originals.
                    if ((int) $event['begin'] < $now->getTimestamp()) {
                        continue;
                    }

                    $key = $event['id'].'-'.$event['begin'];
                    $cards[$key] = new Card(
                        html_entity_decode((string) $event['title'], ENT_QUOTES | ENT_HTML5),
                        html_entity_decode(strip_tags((string) $event['teaser']), ENT_QUOTES | ENT_HTML5),
                        $event['addImage'] && $event['singleSRC'] ? (string) $event['singleSRC'] : null,
                        $event['href'],
                        $now->setTimestamp((int) $event['begin']),
                        ['location' => html_entity_decode(strip_tags((string) $event['location']), ENT_QUOTES | ENT_HTML5)],
                    );
                }
            }
        }

        $cards = array_values($cards);
        usort($cards, static fn (Card $a, Card $b): int => 'title_asc' === $query->sort ? strnatcasecmp($a->title, $b->title) : ($a->date <=> $b->date));

        return \array_slice($cards, 0, $query->limit);
    }
}
