# Contao Gallery

Native Browser-Technik für Contao 5.7 und 6.0. Kein Swiper, kein jQuery, keine JavaScript-Laufzeitabhängigkeiten.

Entwicklungsstand: siehe [Roadmap](../../docs/roadmap.md). Noch nicht veröffentlicht.

## Installation

Im Contao Manager nach `nordwerk/contao-gallery-bundle` suchen (nach Veröffentlichung). Alternativ:

```sh
composer require nordwerk/contao-gallery-bundle
vendor/bin/contao-console contao:migrate
```

PHP 8.3+ für Contao 5.7, PHP 8.4+ für Contao 6.0. MIT.

## Verwendung

Im Artikel das Inhaltselement **Nordwerk UI → Galerie** anlegen, Dateien oder einen Ordner auswählen, sortieren und eine Contao-Bildgröße wählen. Raster, wechselnde Formate und Leiste stehen zur Auswahl; „Großansicht/Neues Fenster“ aktiviert die native Sheet-Lightbox. Alt-Texte und Bildunterschriften stammen aus der Dateiverwaltung. Die Lightbox übernimmt die Großansichtsgröße des Seitenlayouts.

Die Leiste nutzt `@nordwerk/scroll-carousel` 0.1.5, die Lightbox `@nordwerk/scroll-sheet` 0.5.0. Deren veröffentlichte Originaldateien liefern Layout und Verhalten; das Galerie-Bundle verbindet die APIs. Es installiert beide Contao-Bundles automatisch. Ohne JavaScript bleiben Raster und Leiste sichtbar und große Bilder über normale Links erreichbar. Mit JavaScript: direkt zum gewählten Bild, Pfeile, Tastatur, Touch-Wischen, Escape und Fokus-Rückgabe.

Wechselnde Formate verwenden ein CSS-Raster mit stabiler Bildreihenfolge. Externe Metadatenlinks bleiben normale Links. Der globale Ersatz vorhandener Kern-Lightboxen ist zurückgestellt, siehe Roadmap. Twig-Bausteine und ein Beispiel „Produktbilder mit Lightbox“ stehen in [docs/integration.md](../../docs/integration.md).
