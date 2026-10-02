# Contao UI

Native Bausteine für Contao: Carousel, Dialog / Sheet, Galerie, Abschnitte für Inhaltsseiten sowie Teaser und Kundenstimmen. CSS Scroll-Snap und `<dialog>`, ohne Swiper oder jQuery. Die veröffentlichten OSS-Assets liegen versioniert in den Paketen; Contao-Nutzer benötigen keinen Node-Build.

## Entwicklung

Docker und `vp` vorausgesetzt. `make up` startet Contao 5.7 / PHP 8.3 unter http://localhost:8101. Ein zweiter Stack, etwa in einem Worktree, setzt `CONTAO_UI_PROJECT` und `CONTAO_UI_PORT`; Playwright folgt dem Port. `make reset` setzt ausschließlich die lokale Demo-Datenbank dieses Stacks zurück. `make check` prüft PHP, Twig, Composer, Container und Browser. `make down` beendet den Stack.

Demo-Backend: `admin@example.test` / `contao-ui-local-demo` (nur lokal).

`make check6` baut dieselbe Demo mit Contao 6.0 / PHP 8.4 in `app6`, führt Checks aus und stellt danach wieder 5.7 bereit. Mailhinweise der Kundenstimmen landen ausschließlich im lokalen Mailpit unter http://localhost:8131 (`CONTAO_UI_MAIL_PORT`).

[Twig-Integration](docs/integration.md) · [Release und Split](docs/releasing.md) · [Roadmap](docs/roadmap.md) · [Markt und Kern](docs/landscape.md) · [Berichte](docs/report.md) · [Audit](docs/audit.md)

`make artifacts` erzeugt je Paket ein versioniertes Contao-Manager-ZIP aus `HEAD`. Die CI-Datei bereitet Checks für 5.7/6.0 und ZIPs als Workflow-Artefakte vor.

Die Pakete erscheinen als eigenständige Read-only-Repositories [contao-carousel](https://github.com/studio-nordwerk/contao-carousel), [contao-sheet](https://github.com/studio-nordwerk/contao-sheet), [contao-gallery](https://github.com/studio-nordwerk/contao-gallery), [contao-sections](https://github.com/studio-nordwerk/contao-sections), [contao-teasers](https://github.com/studio-nordwerk/contao-teasers) und [contao-testimonials](https://github.com/studio-nordwerk/contao-testimonials); dieses Monorepo ist die einzige Schreibquelle. MIT.

Teaser und Kundenstimmen: [Teasers](packages/teasers/README.md), [Testimonials](packages/testimonials/README.md). Demo: `/teasers.html` und `/submit.html`; nur fiktive Daten. Bericht: [docs/report-teasers.md](docs/report-teasers.md).
