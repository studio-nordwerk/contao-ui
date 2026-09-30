# Contao Carousel

Native Browser-Technik für Contao 5.7 und 6.0. Kein Swiper, kein jQuery, keine JavaScript-Laufzeitabhängigkeiten.

Entwicklungsstand: siehe [Roadmap](../../docs/roadmap.md). Noch nicht veröffentlicht.

## Installation

Im Contao Manager nach `nordwerk/contao-carousel-bundle` suchen (nach Veröffentlichung). Alternativ:

```sh
composer require nordwerk/contao-carousel-bundle
vendor/bin/contao-console contao:migrate
```

PHP 8.3+ für Contao 5.7, PHP 8.4+ für Contao 6.0. MIT.

## Verwendung

In einem Artikel „Carousel“ anlegen, einen Namen für Hilfstechnologien und die Optionen setzen. Über das Kind-Element-Symbol beliebige Inhalte einfügen; ein direktes Kind ist ein Slide. Eine Elementgruppe bündelt mehrere Inhalte zu einem Slide. Die Anzahl pro Ansicht bezieht sich auf die **Containerbreite**, mit Breakpoints 600 und 960 px; Dezimalwerte sind möglich.

Pfeile und Punkte sind optional. Autoplay `0` deaktiviert die Wiedergabe; aktive Intervalle liegen bei 1000–60000 ms. Der Pausenknopf gehört immer dazu. Die Originalkomponente stoppt bei Tastaturfokus und startet bei reduzierter Bewegung pausiert. Touch-Wischen und Scroll-Snap funktionieren ohne JS. Mausziehen ist eine optionale Verbesserung.

System → Einstellungen → Nordwerk Carousel enthält den separaten Schalter für den Kern-Swiper. Er ist standardmäßig aus. Er unterstützt native Swiper-Elemente mit Standard-Twig-Template; eigene Templates und alte Start-/Stop-Slider werden respektiert. Endlosschleifen werden zu Rücksprung, eine frei gewählte Animationsgeschwindigkeit ist mit nativem Scrollen nicht verfügbar. Manuell im Layout aktivierte Swiper-Skripte bitte selbst entfernen.

Templates: `content_element/nw_carousel.html.twig` und `component/_nw_carousel.html.twig`. CSS-Theme über die Custom Properties der [OSS-Komponente](https://www.nordwerk.studio/oss/scroll-carousel). Die Assets sind `@nordwerk/scroll-carousel@0.1.5`, MIT, unverändert vendort.
