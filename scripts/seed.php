<?php

declare(strict_types=1);

use Contao\Config;
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

Config::persist('nwCarouselReplaceSwiper', true);
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
        if ('carousel' === $alias) {
            $coreId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 768, 'type' => 'swiper', 'headline' => serialize(['value' => 'Kern-Swiper ohne Swiper', 'unit' => 'h2']), 'sliderContinuous' => 1]);

            for ($slide = 1; $slide <= 3; ++$slide) {
                $save('tl_content', ['pid' => $coreId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => $slide * 128, 'type' => 'html', 'html' => '<section class="demo-card"><h3>Kern-Inhalt '.$slide.'</h3><p>Dieses Element bleibt im Backend ein Kern-Swiper.</p></section>']);
            }

            foreach ([['Inhalte zum Blättern', 0, 1], ['Responsive Ansichten', 0, 3], ['Automatisch mit Pause', 1500, 1]] as $index => [$label, $delay, $views]) {
                $parentId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 256 + $index * 128, 'type' => 'nw_carousel', 'headline' => serialize(['value' => $label, 'unit' => 'h2']), 'nwCarouselLabel' => $label, 'nwCarouselSmall' => 1, 'nwCarouselMedium' => 1 === $views ? 1 : 2, 'nwCarouselLarge' => $views, 'nwCarouselArrows' => 1, 'nwCarouselDots' => 1, 'nwCarouselDrag' => 1, 'nwCarouselAutoplay' => $delay]);

                for ($slide = 1; $slide <= (1 === $views ? 3 : 5); ++$slide) {
                    $save('tl_content', ['pid' => $parentId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => $slide * 128, 'type' => 'html', 'html' => '<section class="demo-card"><h3>Inhalt '.$slide.'</h3><p>Ein natives Contao-Kind-Element. Texte, Bilder und Links bleiben bedienbar.</p><a href="/sheet.html">Zum Sheet</a></section>']);
                }
                $save('tl_content', ['pid' => $parentId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => 1024, 'type' => 'html', 'invisible' => 1, 'html' => '<p>Hidden child must not render.</p>']);
            }
        }
    }

    $db->commit();
    echo "Contao UI demo seeded.\n";
} catch (Throwable $exception) {
    $db->rollBack();

    throw $exception;
}
