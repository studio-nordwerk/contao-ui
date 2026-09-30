<?php

declare(strict_types=1);

use Contao\Config;
use Contao\Dbafs;
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
    $imageSizeId = $save('tl_image_size', ['pid' => $themeId, 'tstamp' => $now, 'name' => 'Galeriebilder', 'width' => 640, 'height' => 0, 'resizeMode' => 'proportional', 'densities' => '1,2', 'sizes' => '(max-width: 599px) 50vw, 33vw', 'lazyLoading' => 1]);
    $save('tl_image_size_item', ['pid' => $imageSizeId, 'sorting' => 128, 'tstamp' => $now, 'media' => '(max-width: 599px)', 'width' => 320, 'height' => 0, 'resizeMode' => 'proportional', 'densities' => '1,2', 'sizes' => '50vw']);
    $imageUuids = [];
    $folder = 'files/contao-ui-gallery';
    if (!is_dir($folder)) {
        mkdir($folder, 0775, true);
    }
    file_put_contents($folder.'/.public', '');

    foreach ([[28, 78, 105], [45, 93, 70], [168, 92, 45], [105, 69, 124], [52, 111, 130], [136, 58, 79], [63, 81, 129], [132, 111, 54]] as $index => $rgb) {
        $height = 0 === $index % 3 ? 1200 : (1 === $index % 3 ? 1600 : 1000);
        $image = imagecreatetruecolor(1600, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, ...$rgb));
        $paper = imagecolorallocate($image, 232, 234, 224);
        $ink = imagecolorallocate($image, 22, 39, 49);
        imagefilledrectangle($image, 120, 120, 900, $height - 120, $paper);
        imagefilledellipse($image, 1000, (int) ($height / 2), 780, 780, $ink);
        imagefilledrectangle($image, 940, 120, 1460, 210, $paper);
        $path = $folder.'/study-'.($index + 1).'.jpg';
        imagejpeg($image, $path, 85);
        imagedestroy($image);
        $file = Dbafs::addResource($path);
        $file->meta = serialize(['de' => ['alt' => 'Geometrische Studie '.($index + 1), 'title' => 'Studie '.($index + 1), 'caption' => 'Studie '.($index + 1).' – Form und Farbe'], 'en' => ['alt' => 'Geometric study '.($index + 1), 'title' => 'Study '.($index + 1), 'caption' => 'Study '.($index + 1).' – form and colour']]);
        $file->save();
        $imageUuids[] = $file->uuid;
    }
    file_put_contents($folder.'/notes.txt', 'Non-image files must not appear in a gallery.');
    $folderModel = Dbafs::addResource($folder);
    $pageId = $save('tl_page', ['tstamp' => $now, 'title' => 'Contao UI', 'type' => 'root', 'alias' => 'root', 'language' => 'de', 'fallback' => 1, 'useSSL' => 0, 'urlSuffix' => '.html', 'published' => 1, 'includeLayout' => 1, 'layout' => $layoutId]);
    $rootId = $pageId;

    foreach (['home' => 'Native Bausteine für Contao', 'carousel' => 'Carousel', 'sheet' => 'Dialog / Sheet', 'gallery' => 'Galerie'] as $alias => $title) {
        $pageId = $save('tl_page', ['pid' => $rootId, 'sorting' => ('home' === $alias ? 128 : ('carousel' === $alias ? 256 : ('sheet' === $alias ? 384 : 512))), 'tstamp' => $now, 'title' => $title, 'type' => 'regular', 'alias' => $alias, 'published' => 1]);
        $articleId = $save('tl_article', ['pid' => $pageId, 'tstamp' => $now, 'title' => $title, 'alias' => $alias, 'inColumn' => 'main', 'published' => 1]);
        $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 128, 'type' => 'html', 'html' => '<nav aria-label="Demos"><a href="/home.html">Übersicht</a><a href="/carousel.html">Carousel</a><a href="/sheet.html">Sheet</a><a href="/gallery.html">Galerie</a></nav><p class="demo-kicker">Studio Nordwerk · Contao UI</p><h1>'.$title.'</h1><p>Kein Swiper. Kein jQuery. Native Browser-Technik, mit wenigen kB JavaScript verbessert.</p>']);
        if ('gallery' === $alias) {
            foreach (['grid' => 'Raster', 'mosaic' => 'Wechselnde Formate', 'rail' => 'Bilderleiste'] as $index => $label) {
                $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 256, 'type' => 'nw_gallery', 'headline' => serialize(['value' => $label, 'unit' => 'h2']), 'nwGalleryLabel' => $label, 'nwGalleryLayout' => $index, 'multiSRC' => serialize('grid' === $index ? [...array_reverse($imageUuids), $imageUuids[0]] : [$folderModel->uuid, $imageUuids[0]]), 'sortBy' => 'rail' === $index ? 'name_desc' : ('mosaic' === $index ? 'name_asc' : 'custom'), 'perRow' => 3, 'size' => serialize([0, 0, $imageSizeId]), 'fullsize' => 1]);
            }
            $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 512, 'type' => 'nw_gallery', 'headline' => serialize(['value' => 'Bilder ohne Lightbox', 'unit' => 'h2']), 'nwGalleryLabel' => 'Bilder ohne Lightbox', 'nwGalleryLayout' => 'grid', 'multiSRC' => serialize([$imageUuids[0]]), 'size' => serialize([0, 0, $imageSizeId]), 'fullsize' => 0]);
        }
        if ('sheet' === $alias) {
            foreach (['bottom' => 'Sheet von unten', 'start' => 'Seitenleiste links', 'end' => 'Seitenleiste rechts', 'center' => 'Zentrierter Dialog'] as $presentation => $label) {
                $sheetId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 512, 'type' => 'nw_sheet', 'nwSheetLabel' => $label, 'nwSheetPresentation' => $presentation, 'nwSheetSnapPoints' => '50,75', 'nwSheetDrag' => 1]);
                $save('tl_content', ['pid' => $sheetId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => 128, 'type' => 'html', 'html' => '<p>Ein natives Dialog-Element mit Contao-Inhalten. Escape schließt, und der Fokus kehrt zum Auslöser zurück.</p><p><a href="/carousel.html">Zum Carousel</a></p>']);
                $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 256, 'type' => 'nw_sheet_button', 'nwSheetTarget' => $sheetId, 'nwSheetButtonLabel' => $label.' öffnen']);
            }
            $lockedId = $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 512, 'type' => 'nw_sheet', 'nwSheetLabel' => 'Bewusst schließen', 'nwSheetPresentation' => 'center', 'nwSheetDismissible' => 0]);
            $save('tl_content', ['pid' => $lockedId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => 128, 'type' => 'html', 'html' => '<p>Dieser Dialog bleibt bei Escape und Hintergrund-Tipp offen. Der Abschluss-Button schließt ausdrücklich.</p>']);
            $save('tl_content', ['pid' => $lockedId, 'ptable' => 'tl_content', 'tstamp' => $now, 'sorting' => 256, 'type' => 'nw_sheet_button', 'nwSheetTarget' => $lockedId, 'nwSheetAction' => 'close', 'nwSheetButtonLabel' => 'Abschließen']);
            $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 256, 'type' => 'nw_sheet_button', 'nwSheetTarget' => $lockedId, 'nwSheetButtonLabel' => 'Bewusst schließen öffnen']);
            $navigationId = $save('tl_module', ['pid' => $themeId, 'tstamp' => $now, 'name' => 'Kern-Navigation', 'type' => 'navigation', 'levelOffset' => 0, 'showLevel' => 0]);
            $offcanvasId = $save('tl_module', ['pid' => $themeId, 'tstamp' => $now, 'name' => 'Offcanvas-Navigation', 'type' => 'nw_offcanvas_navigation', 'nwSheetNavigation' => $navigationId, 'nwSheetPresentation' => 'end']);
            $save('tl_content', ['pid' => $articleId, 'ptable' => 'tl_article', 'tstamp' => $now, 'sorting' => 384, 'type' => 'module', 'module' => $offcanvasId]);
        }

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
