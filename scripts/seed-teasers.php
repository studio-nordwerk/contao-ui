<?php

declare(strict_types=1);

use Contao\Config;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;

require getcwd().'/vendor/autoload.php';
ContaoKernel::setProjectDir(getcwd());
$kernel = new ContaoKernel('dev', true);
$kernel->boot();
$kernel->getContainer()->get('contao.framework')->initialize();
$db = $kernel->getContainer()->get('database_connection');

if ($db->fetchOne("SELECT id FROM tl_page WHERE alias='teasers'")) {
    echo "Teaser demo exists; seed skipped.\n";

    exit(0);
}

Config::persist('adminEmail', 'operator@example.test');
Config::persist('adminName', 'Demo Betreiberin');
$now = time();
$save = static function (string $table, array $row) use ($db, $now): int {
    $db->insert($table, $row + ['tstamp' => $now]);

    return (int) $db->lastInsertId();
};
$root = (int) $db->fetchOne("SELECT id FROM tl_page WHERE type='root'");
$theme = (int) $db->fetchOne('SELECT id FROM tl_theme');
$image = $db->fetchOne("SELECT uuid FROM tl_files WHERE path='files/contao-ui-gallery/study-1.jpg'");
$pages = [];
$articles = [];

foreach (['teasers' => 'Teaser und Kundenstimmen', 'submit' => 'Kundenstimme einreichen', 'privacy' => 'Datenschutzhinweise', 'news' => 'Nachrichten lesen', 'events' => 'Termine lesen'] as $alias => $title) {
    $pages[$alias] = $save('tl_page', ['pid' => $root, 'sorting' => 1024, 'title' => $title, 'alias' => $alias, 'type' => 'regular', 'published' => 1]);
    $articles[$alias] = $save('tl_article', ['pid' => $pages[$alias], 'title' => $title, 'alias' => $alias, 'inColumn' => 'main', 'published' => 1]);
    $save('tl_content', ['pid' => $articles[$alias], 'ptable' => 'tl_article', 'sorting' => 128, 'type' => 'html', 'html' => '<nav aria-label="Demos"><a href="/home.html">Übersicht</a><a href="/teasers.html">Teaser</a><a href="/submit.html">Kundenstimme einreichen</a></nav><h1>'.$title.'</h1>']);
}
$save('tl_content', ['pid' => $articles['privacy'], 'ptable' => 'tl_article', 'sorting' => 256, 'type' => 'html', 'html' => '<p>Fiktive lokale Demo: Verantwortlich ist Demo Betreiberin. Name, Erfahrung und freiwillige Angaben werden mit Einwilligung zur Prüfung und Veröffentlichung verarbeitet. Die E-Mail bleibt intern. Widerruf und Löschung: operator@example.test. Unveröffentlichte Einreichungen werden nach 30 Tagen gelöscht; veröffentlichte Stimmen bis zum Widerruf oder Ende des Veröffentlichungszwecks. Sie haben Rechte auf Auskunft, Berichtigung, Löschung, Einschränkung und Beschwerde bei einer Datenschutzaufsichtsbehörde. Keine echten Kundendaten eingeben.</p>']);
$newsArchive = $save('tl_news_archive', ['title' => 'Demo Nachrichten', 'jumpTo' => $pages['news']]);
$hiddenArchive = $save('tl_news_archive', ['title' => 'Geschützte Nachrichten', 'jumpTo' => $pages['news'], 'protected' => '1', 'groups' => serialize([9999])]);
$calendar = $save('tl_calendar', ['title' => 'Demo Termine', 'jumpTo' => $pages['events']]);
$repeatingCalendar = $save('tl_calendar', ['title' => 'Wiederkehrende Termine', 'jumpTo' => $pages['events']]);
$archive = $save('tl_nw_testimonial_archive', ['title' => 'Fiktive Kundenstimmen', 'verification' => 'Fiktive Demo. Im Echtbetrieb prüfen wir Erfahrungen anhand des Auftrags und veröffentlichen mit Einwilligung.']);
$importArchive = $save('tl_nw_testimonial_archive', ['title' => 'Import zur Prüfung', 'verification' => 'Importierte Stimmen: Veröffentlichungsrechte und Herkunft werden vor Freigabe geprüft.']);

// Minimal Codefog-compatible test relation, without installing another bundle.
$db->executeStatement('CREATE TABLE IF NOT EXISTS tl_news_category (id int unsigned NOT NULL PRIMARY KEY, title varchar(255) NOT NULL)');
$db->executeStatement('CREATE TABLE IF NOT EXISTS tl_news_categories (news_id int unsigned NOT NULL, category_id int unsigned NOT NULL, PRIMARY KEY (news_id, category_id))');
$db->insert('tl_news_category', ['id' => 91001, 'title' => 'Fiktive Kategorie']);

for ($i = 1; $i <= 4; ++$i) {
    $news = $save('tl_news', ['pid' => $newsArchive, 'headline' => 'Nachricht '.$i, 'alias' => 'demo-news-'.$i, 'date' => $now - $i * 60, 'time' => $now - $i * 60, 'teaser' => '<p>Neuigkeiten aus der fiktiven Demo '.$i.'.</p>', 'published' => 1, 'addImage' => 1, 'singleSRC' => $image]);
    if ($i <= 2) {
        $db->insert('tl_news_categories', ['news_id' => $news, 'category_id' => 91001]);
    }
    $start = $now + $i * 86400;
    $save('tl_calendar_events', ['pid' => $calendar, 'title' => 'Termin '.$i, 'alias' => 'demo-event-'.$i, 'startDate' => $start, 'endDate' => $start, 'startTime' => $start, 'endTime' => $start + 3600, 'addTime' => 1, 'teaser' => '<p>Ein kommender Demotermin.</p>', 'location' => 'Demostudio', 'published' => 1]);
    $save('tl_nw_testimonial', ['pid' => $archive, 'name' => 'Demo Kundin '.$i, 'role' => 'Fiktive Firma', 'text' => 'Das gemeinsame Projekt war gut organisiert. Fiktive Erfahrung '.$i.'.', 'stars' => 5, 'date' => $now - $i * 86400, 'singleSRC' => $image, 'source' => 'Fiktives Pilotprojekt', 'published' => 1, 'reviewNotes' => 'Fiktive Testdaten, keine echte Bewertung.']);
}

foreach ([['Nicht veröffentlicht', $newsArchive, 0, '', ''], ['Zukünftig veröffentlicht', $newsArchive, '1', (string) ($now + 86400), ''], ['Abgelaufen', $newsArchive, '1', '', (string) ($now - 86400)], ['Geschützte Nachricht', $hiddenArchive, '1', '', '']] as [$title, $pid, $published, $start, $stop]) {
    $save('tl_news', ['pid' => $pid, 'headline' => $title, 'alias' => 'hidden-'.md5($title), 'date' => $now, 'published' => $published, 'start' => $start, 'stop' => $stop]);
}
$past = $now - 10 * 86400;
$save('tl_calendar_events', ['pid' => $calendar, 'title' => 'Vergangener Termin', 'alias' => 'past', 'startDate' => $past, 'endDate' => $past, 'startTime' => $past, 'endTime' => $past + 3600, 'published' => 1]);
$save('tl_calendar_events', ['pid' => $repeatingCalendar, 'title' => 'Wiederkehrender Termin', 'alias' => 'repeating', 'startDate' => $past, 'endDate' => $past, 'startTime' => $past, 'endTime' => $past + 3600, 'addTime' => 1, 'recurring' => 1, 'repeatEach' => serialize(['value' => 2, 'unit' => 'days']), 'recurrences' => 20, 'repeatEnd' => $now + 30 * 86400, 'published' => 1]);

$sorting = 256;

foreach ([['news', 'grid', $newsArchive, 'Nachrichten im Raster', []], ['news', 'carousel', $newsArchive, 'Nachrichten im Carousel', []], ['events', 'list', $calendar, 'Die nächsten drei Termine', []], ['testimonials', 'carousel', $archive, 'Kundenstimmen', []], ['news', 'list', $newsArchive, 'Nachrichten einer Kategorie', [91001]], ['news', 'list', $hiddenArchive, 'Geschützte Inhalte', []], ['events', 'list', $repeatingCalendar, 'Wiederkehrende Termine', []], ['testimonials', 'list', $importArchive, 'Importierte Kundenstimmen', []]] as [$source, $layout, $pid, $label, $categories]) {
    $data = ['nwTeaserSource' => $source, 'nwTeaserLayout' => $layout, 'nwTeaserArchives' => serialize([$pid]), 'nwTeaserCategories' => serialize($categories), 'nwTeaserSort' => 'events' === $source ? 'date_asc' : 'date_desc', 'nwTeaserLimit' => 3, 'nwTeaserColumns' => 3, 'nwTeaserLabel' => $label, 'size' => serialize([480, 320, 'crop'])];
    if ('events' === $source && $pid === $calendar) {
        $data['imgSize'] = $data['size'];
        unset($data['size']);
        $module = $save('tl_module', $data + ['pid' => $theme, 'name' => $label, 'type' => 'nw_teaser', 'headline' => serialize(['value' => $label, 'unit' => 'h2'])]);
        $save('tl_content', ['pid' => $articles['teasers'], 'ptable' => 'tl_article', 'sorting' => $sorting, 'type' => 'module', 'module' => $module]);
    } else {
        $save('tl_content', $data + ['pid' => $articles['teasers'], 'ptable' => 'tl_article', 'sorting' => $sorting, 'type' => 'nw_teaser', 'headline' => serialize(['value' => $label, 'unit' => 'h2'])]);
    }
    $sorting += 128;
}
$module = $save('tl_module', ['pid' => $theme, 'name' => 'Kundenstimme einreichen', 'type' => 'nw_testimonial_form', 'nwTestimonialArchive' => $archive, 'nwTestimonialRecipient' => 'operator@example.test', 'nwTestimonialConsent' => 'Ich willige in die Verarbeitung meiner Angaben zur Prüfung und Veröffentlichung meiner Kundenstimme ein. Meine E-Mail bleibt intern. Ich kann die Einwilligung jederzeit für die Zukunft widerrufen.', 'nwTestimonialPrivacy' => $pages['privacy']]);
$save('tl_content', ['pid' => $articles['submit'], 'ptable' => 'tl_article', 'sorting' => 256, 'type' => 'module', 'module' => $module]);

foreach (['news' => ['newsreader', 'news_archives', $newsArchive], 'events' => ['eventreader', 'cal_calendar', $calendar]] as $page => [$type, $field, $pid]) {
    $module = $save('tl_module', ['pid' => $theme, 'name' => 'Demo reader '.$page, 'type' => $type, $field => serialize([$pid])]);
    $save('tl_content', ['pid' => $articles[$page], 'ptable' => 'tl_article', 'sorting' => 256, 'type' => 'module', 'module' => $module]);
}

// Oveleon fixture: isolated demo database, fictions only. Import never changes
// these tables.
$db->executeStatement('CREATE TABLE IF NOT EXISTS tl_recommendation_archive (id int unsigned NOT NULL PRIMARY KEY, title varchar(255) NOT NULL)');
$db->executeStatement("CREATE TABLE IF NOT EXISTS tl_recommendation (id int unsigned NOT NULL PRIMARY KEY, pid int unsigned NOT NULL, author varchar(128) NOT NULL, customField varchar(255) NOT NULL default '', text mediumtext NULL, rating char(1) NOT NULL default '', date int unsigned NOT NULL, email varchar(255) NOT NULL default '', location varchar(128) NOT NULL default '', imageUrl varchar(255) NOT NULL default '', published char(1) NOT NULL default '', verified char(1) NOT NULL default '1', start varchar(10) NOT NULL default '', stop varchar(10) NOT NULL default '')");
$db->insert('tl_recommendation_archive', ['id' => 91001, 'title' => 'Fiktives Oveleon-Archiv']);
$db->insert('tl_recommendation', ['id' => 91001, 'pid' => 91001, 'author' => 'Import Demo Kundin', 'customField' => 'Fiktive Rolle', 'text' => '<p>Fiktive importierte Erfahrung.</p><p>Mit zweitem Absatz.</p>', 'rating' => '4', 'date' => $now - 86400, 'email' => 'fiction@example.test', 'location' => 'Fiktiver Anlass', 'imageUrl' => 'files/contao-ui-gallery/study-1.jpg', 'published' => '1', 'verified' => '1']);
$db->insert('tl_recommendation', ['id' => 91002, 'pid' => 91001, 'author' => 'Import Unbestätigt', 'text' => '<p>Fiktive ungeprüfte Erfahrung.</p>', 'rating' => '5', 'date' => $now, 'published' => '', 'verified' => '']);
echo "Teaser and testimonial demo seeded (fictional data only).\n";
