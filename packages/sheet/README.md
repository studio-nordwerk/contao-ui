# Contao Sheet

Native Browser-Technik für Contao 5.7 und 6.0. Kein Swiper, kein jQuery, keine JavaScript-Laufzeitabhängigkeiten.

Entwicklungsstand: siehe [Roadmap](../../docs/roadmap.md). Noch nicht veröffentlicht.

## Installation

Im Contao Manager nach `nordwerk/contao-sheet-bundle` suchen (nach Veröffentlichung). Alternativ:

```sh
composer require nordwerk/contao-sheet-bundle
vendor/bin/contao-console contao:migrate
```

PHP 8.3+ für Contao 5.7, PHP 8.4+ für Contao 6.0. MIT.

## Verwendung

„Dialog / Sheet“ in einem Artikel anlegen, Dialogtitel, Darstellung und Optionen wählen. Über das Kind-Element-Symbol beliebige Contao-Inhalte einfügen. „Dialog-Button“ auf derselben Seite anlegen und das Ziel-Sheet auswählen. Die Aktion „Öffnen“ erzeugt einen nativen `commandfor`-Button, „Schließen“ eignet sich als Abschluss-Button innerhalb eines Sheets.

Einrastpunkte wie `50,75` sind Prozentwerte der Viewport-Höhe (5–95). Sie gelten für Bottom-Sheets. Ohne „Schließen erlauben“ bleiben Escape, Hintergrund und Wegwischen ohne Wirkung; ein ausdrücklich eingefügter Abschluss-Button darf weiterhin schließen. Deshalb immer einen erreichbaren Abschluss anbieten.

Für Offcanvas zuerst ein Kern-Navigationsmodul anlegen. Danach „Offcanvas-Navigation“ erstellen, das Navigationsmodul und die Seite wählen und das Modul im Layout oder als Inhaltselement einbinden. Der Burger öffnet die native Seitenleiste; die Zurück-Taste schließt sie über das Original-History-Plugin.

Ohne JavaScript funktionieren Öffnen, Schließtaste und Escape mit Invoker Commands (Chromium 135+, Firefox 144+, Safari 26.2+). Ältere Browser erhalten den Fallback mit JS. Hintergrund-Tipp, Fokuskorrekturen, Einrastpunkt beim Öffnen und History sind progressive Verbesserungen.

Templates: `content_element/nw_sheet.html.twig`, `content_element/nw_sheet_button.html.twig`, `frontend_module/nw_offcanvas_navigation.html.twig`, `component/_nw_sheet.html.twig`. Theme über die Custom Properties der [OSS-Komponente](https://www.nordwerk.studio/oss/scroll-sheet). Unveränderte Assets: `@nordwerk/scroll-sheet@0.5.0`, MIT.
