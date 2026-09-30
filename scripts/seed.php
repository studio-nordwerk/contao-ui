<?php

declare(strict_types=1);

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\Model;

require getcwd().'/vendor/autoload.php';

ContaoKernel::setProjectDir(getcwd());
$kernel = new ContaoKernel('dev', true);
$kernel->boot();
$kernel->getContainer()->get('contao.framework')->initialize();

$save = static function (string $table, array $data): int {
    $class = Model::getClassFromTable($table);
    $model = new $class();
    $model->setRow($data);
    $model->save();

    return (int) $model->id;
};

$db = $kernel->getContainer()->get('database_connection');

if (0 < (int) $db->fetchOne('SELECT COUNT(*) FROM tl_page')) {
    echo "Existing demo found; seed skipped.\n";

    exit(0);
}

$now = time();
$db->beginTransaction();

try {
    $save('tl_user', ['tstamp' => $now, 'username' => 'admin@example.test', 'name' => 'Demo Admin', 'email' => 'admin@example.test', 'password' => password_hash('contao-ui-local-demo', PASSWORD_BCRYPT), 'admin' => 1, 'language' => 'de', 'dateAdded' => $now]);
    $themeId = $save('tl_theme', ['tstamp' => $now, 'name' => 'Contao UI', 'author' => 'Nordwerk']);
    $layoutId = $save('tl_layout', [
        'pid' => $themeId,
        'tstamp' => $now,
        'name' => 'Native UI demo',
        'rows' => '1rw',
        'cols' => '1cl',
        'modules' => serialize([['mod' => 0, 'col' => 'main', 'enable' => 1]]),
        'head' => '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><style>:root{color-scheme:light dark}body{font:1.1rem/1.6 system-ui;margin:0;background:Canvas;color:CanvasText}#wrapper{max-width:68rem;margin:auto;padding:clamp(1rem,4vw,3rem)}h1{font-size:clamp(2rem,5vw,4rem);line-height:1.1}h2{line-height:1.2}a{color:LinkText}nav{display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:3rem}button{font:inherit;cursor:pointer}button:focus-visible,a:focus-visible{outline:3px solid Highlight;outline-offset:4px}.demo-card{padding:3rem;background:color-mix(in srgb,CanvasText 6%,Canvas);border:1px solid color-mix(in srgb,CanvasText 20%,Canvas);border-radius:1rem}.demo-kicker{letter-spacing:.12em;text-transform:uppercase;font-size:.8rem}</style>',
    ]);
    $pageId = $save('tl_page', ['tstamp' => $now, 'title' => 'Contao UI', 'type' => 'root', 'alias' => 'root', 'language' => 'de', 'fallback' => 1, 'useSSL' => 0, 'urlSuffix' => '.html', 'published' => 1, 'includeLayout' => 1, 'layout' => $layoutId]);
    $rootId = $pageId;

    foreach (['home' => 'Native Bausteine für Contao', 'carousel' => 'Carousel', 'sheet' => 'Dialog / Sheet', 'gallery' => 'Galerie'] as $alias => $title) {
        $pageId = $save('tl_page', ['pid' => $rootId, 'sorting' => ('home' === $alias ? 128 : ('carousel' === $alias ? 256 : ('sheet' === $alias ? 384 : 512))), 'tstamp' => $now, 'title' => $title, 'type' => 'regular', 'alias' => $alias, 'published' => 1]);
        $articleId = $save('tl_article', ['pid' => $pageId, 'tstamp' => $now, 'title' => $title, 'alias' => $alias, 'inColumn' => 'main', 'published' => 1]);
        $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 128, 'type' => 'html', 'html' => '<nav aria-label="Demos"><a href="/home.html">Übersicht</a><a href="/carousel.html">Carousel</a><a href="/sheet.html">Sheet</a><a href="/gallery.html">Galerie</a></nav><p class="demo-kicker">Studio Nordwerk · Contao UI</p><h1>'.$title.'</h1><p>Kein Swiper. Kein jQuery. Native Browser-Technik, mit wenigen kB JavaScript verbessert.</p>']);
    }

    $db->commit();
    echo "Contao UI demo seeded.\n";
} catch (Throwable $exception) {
    $db->rollBack();

    throw $exception;
}
