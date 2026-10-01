<?php

declare(strict_types=1);

use Contao\Dbafs;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;

require getcwd().'/vendor/autoload.php';
ContaoKernel::setProjectDir(getcwd());
$kernel = new ContaoKernel('dev', true);
$kernel->boot();
$kernel->getContainer()->get('contao.framework')->initialize();
$db = $kernel->getContainer()->get('database_connection');
$action = $argv[1] ?? 'info';

if ('cleanup' === $action) {
    $db->executeStatement("DELETE FROM tl_nw_testimonial WHERE name LIKE 'E2E Teaser %' OR importKey LIKE 'oveleon:91001:%'");
    $db->executeStatement('DELETE FROM tl_recommendation WHERE id=91003');
    echo "{}\n";
} elseif ('private-image' === $action) {
    $folder = 'files/contao-ui-teasers-private';
    if (!is_dir($folder)) {
        mkdir($folder, 0775, true);
    }
    copy('files/contao-ui-gallery/study-1.jpg', $folder.'/protected.jpg');
    $file = Dbafs::addResource($folder.'/protected.jpg');
    $db->executeStatement("UPDATE tl_news SET singleSRC=? WHERE alias='demo-news-1'", [$file->uuid]);
    echo "{}\n";
} elseif ('restore-image' === $action) {
    $uuid = $db->fetchOne("SELECT uuid FROM tl_files WHERE path='files/contao-ui-gallery/study-1.jpg'");
    $db->executeStatement("UPDATE tl_news SET singleSRC=? WHERE alias='demo-news-1'", [$uuid]);
    echo "{}\n";
} elseif ('inspect' === $action) {
    echo json_encode($db->fetchAssociative('SELECT id, pid, name, text, published, consentedAt, consentText, notifiedAt, provenance, stars FROM tl_nw_testimonial WHERE name=?', [$argv[2]]), JSON_THROW_ON_ERROR)."\n";
} elseif ('invalid-import' === $action) {
    $db->insert('tl_recommendation', ['id' => 91003, 'pid' => 91001, 'author' => '', 'text' => 'Invalid fixture', 'date' => time()]);
    echo "{}\n";
} elseif ('remove-invalid-import' === $action) {
    $db->executeStatement('DELETE FROM tl_recommendation WHERE id=91003');
    echo "{}\n";
} else {
    echo json_encode(
        [
            'archive' => (int) $db->fetchOne("SELECT id FROM tl_nw_testimonial_archive WHERE title='Fiktive Kundenstimmen'"),
            'importArchive' => (int) $db->fetchOne("SELECT id FROM tl_nw_testimonial_archive WHERE title='Import zur Prüfung'"),
            'article' => (int) $db->fetchOne("SELECT id FROM tl_article WHERE alias='teasers'"),
            'importCount' => (int) $db->fetchOne("SELECT COUNT(*) FROM tl_nw_testimonial WHERE importKey LIKE 'oveleon:91001:%'"),
        ],
        JSON_THROW_ON_ERROR,
    )."\n";
}
