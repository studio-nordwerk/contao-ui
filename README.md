# Contao UI

Drei native Bausteine für Contao: Carousel, Dialog / Sheet und Galerie. CSS Scroll-Snap und `<dialog>`, ohne Swiper oder jQuery. Die veröffentlichten OSS-Assets liegen versioniert in den Paketen; Contao-Nutzer benötigen keinen Node-Build.

## Entwicklung

Docker und `vp` vorausgesetzt. `make up` startet Contao 5.7 / PHP 8.3 unter http://localhost:8101. `make reset` setzt ausschließlich die lokale Demo-Datenbank dieses Stacks zurück. `make check` prüft PHP, Twig, Composer, Container und Browser. `make down` beendet den Stack.

Demo-Backend: `admin@example.test` / `contao-ui-local-demo` (nur lokal).

`make check6` baut dieselbe Demo mit Contao 6.0 / PHP 8.4 in `app6`, führt Checks aus und stellt danach wieder 5.7 bereit. Kein Mailpit nötig; der Stack versendet keine E-Mails. Port 8121 bleibt reserviert.

[Twig-Integration](docs/integration.md) · [Release und Split](docs/releasing.md) · [Roadmap](docs/roadmap.md) · [Markt und Kern](docs/landscape.md) · [Berichte](docs/report.md) · [Audit](docs/audit.md)

`make artifacts` erzeugt drei versionierte Contao-Manager-ZIPs aus `HEAD`. Die CI-Datei bereitet Checks für 5.7/6.0 und ZIPs als Workflow-Artefakte vor.

Die Pakete erscheinen als eigenständige Read-only-Repositories [contao-carousel](https://github.com/studio-nordwerk/contao-carousel), [contao-sheet](https://github.com/studio-nordwerk/contao-sheet) und [contao-gallery](https://github.com/studio-nordwerk/contao-gallery); dieses Monorepo ist die einzige Schreibquelle. MIT.
