# Release und Split-Vorbereitung

Aktuell lokales Monorepo ohne Remote, ohne Push und ohne Veröffentlichung. Die Workflow-Datei wurde lokal geprüft; ein GitHub-Actions-Lauf setzt ein später bewusst eingerichtetes Repository voraus.

## Checks und Manager-ZIPs

`make reset && make check` prüft Contao 5.7/PHP 8.3, `make check6` die gesamte Kette mit Contao 6.0/PHP 8.4 und stellt anschließend die 5.7-Demo wieder her. In GitHub Actions laufen beide Varianten unabhängig; nur nach beiden grünen Jobs entstehen drei Manager-ZIPs als Workflow-Artefakt. Keine Uploads an Produktionsdienste.

```sh
python3 scripts/check-artifacts.py
python3 scripts/build-artifacts.py 0.1.0-dev dist
```

Ein neuer Ausgabepfad ist erforderlich: vorhandene ZIPs werden nicht überschrieben. Der Builder verwendet ausschließlich die eingecheckten Paketbäume von `HEAD`, respektiert `.gitattributes` und setzt die ZIP-Version im Root-Composer-Manifest. Jedes ZIP enthält genau ein Paket ohne Monorepo-Präfix, Demo, Tests oder Entwicklungswerkzeuge. MIT-Lizenzen der vendorten npm-Pakete bleiben dabei. Screenshots und Integrationsdokumentation sind innerhalb des Pakets verlinkt. Der Audit prüft Inhalt, Versionsformat, Abhängigkeiten und Asset-Prüfsummen.

Nach Veröffentlichung können die Pakete über den Contao Manager/Packagist installiert werden. Vorher sind lokale Path-Repositories die getestete Entwicklungsinstallation; für die Galerie müssen auch die beiden abhängigen Pakete verfügbar sein. Ein Manager-ZIP ersetzt nicht die Paketregistrierung oder Abhängigkeitsauflösung. Keine lokale Manager-Upload-UI im Docker-Demo-Stack installiert.

## Vorgesehener Split, noch nicht ausführen

| Monorepo-Pfad       | Vorgesehenes Read-only-Repository | Composer-Paket                    |
| ------------------- | --------------------------------- | --------------------------------- |
| `packages/carousel` | `studio-nordwerk/contao-carousel` | `nordwerk/contao-carousel-bundle` |
| `packages/sheet`    | `studio-nordwerk/contao-sheet`    | `nordwerk/contao-sheet-bundle`    |
| `packages/gallery`  | `studio-nordwerk/contao-gallery`  | `nordwerk/contao-gallery-bundle`  |

Das Monorepo bleibt die einzige Schreibquelle. Ein späterer, gesondert autorisierter Workflow kann nach beiden Checks für jedes Paket `git subtree split --prefix=packages/<paket>` erzeugen und diese Commits mit einem auf die drei Ziele begrenzten Token pushen. Tags erst nach erfolgreichem Split synchronisieren, anschließend die Read-only-Repositories bei Packagist anmelden. Keine Remotes oder Token sind hier eingerichtet, kein Split wurde ausgeführt.

Versionen zunächst gemeinsam (0.1.x), damit die Galerie-Abhängigkeiten zu beiden Bundles passen. Bei unabhängigem Versionieren nur die tatsächlich benötigten Mindestversionen erhöhen. Release-Version und PHP-/Contao-Grenzen müssen in allen drei Composer-Dateien, CHANGELOGs und Manager-Artefakten übereinstimmen. Die Root-CI bleibt im Monorepo; Paket-Exports benötigen keinen eigenen Build.

## Assets und Screenshots

`make assets` holt exakt die gepinnten npm-Tarballs und prüft Integrität sowie jede Datei aus `assets.lock.json`. `python3 scripts/asset-sizes.py` misst tatsächlich ausgelieferte, einzeln gzippte Dateien inklusive der statischen ESM-Importe. Keine zusätzliche Minifizierung oder Nachimplementierung der OSS-Dateien. Bei Wrapper-Änderungen README-Größen neu messen.

`vp exec playwright test e2e/release.spec.ts` erstellt überprüfte Frontend-Screenshots in den Paket-`docs/`-Verzeichnissen (je Hell/Dunkel) sowie Backend-Nachweise unter `test-results/`. Die Testbilder sind selbst erzeugte geometrische Studien. Integrationstext in allen drei Paket-`docs/integration.md` ist eine Kopie von `docs/integration.md` für eigenständige Exports; bei Vertragsänderungen alle Kopien aktualisieren. Der Artefaktcheck prüft die Kopien gegen die Quelle.
