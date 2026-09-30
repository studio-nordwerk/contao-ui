# Contao UI

Drei native Bausteine für Contao: Carousel, Dialog / Sheet und Galerie. CSS Scroll-Snap und `<dialog>`, ohne Swiper oder jQuery. Die veröffentlichten OSS-Assets liegen versioniert in den Paketen; Contao-Nutzer benötigen keinen Node-Build.

## Entwicklung

Docker und `vp` vorausgesetzt. `make up` startet Contao 5.7 / PHP 8.3 unter http://localhost:8101. `make reset` setzt ausschließlich die lokale Demo-Datenbank dieses Stacks zurück. `make check` prüft PHP, Twig, Composer, Container und Browser. `make down` beendet den Stack.

Demo-Backend: `admin@example.test` / `contao-ui-local-demo` (nur lokal).

`make check6` baut dieselbe Demo mit Contao 6.0 / PHP 8.4 in `app6`, führt Checks aus und stellt danach wieder 5.7 bereit. Kein Mailpit nötig; der Stack versendet keine E-Mails. Port 8121 bleibt reserviert.

[Roadmap](docs/roadmap.md) · [Markt und Kern](docs/landscape.md) · [Berichte](docs/report.md)

Kein Remote eingerichtet, keine Veröffentlichung. MIT.
