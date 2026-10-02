# Berichte an Arne

## 2026-09-30 · Ausgangslage

Das Repository war leer (initialisiertes Git ohne Commit). Die Roadmap wurde aus dem vollständigen Welle-1-Brief angelegt. Keine Referenzprojekte verändert, kein Remote angelegt.

Prüfungen und abgeschlossene Phasen werden im selben Lauf unten ergänzt.

## Phase 0 / 1 · Gerüst (2026-09-30)

Drei eigenständige Bundles inklusive Manager-Registrierung, Docker mit Contao 5.7.13 / PHP 8.3.35 auf 8101, 6.0-Variante und alle angeforderten Check-Werkzeuge angelegt. Original-npm-Dateien 0.1.5 / 0.5.0 samt MIT-Lizenzen und Prüfsummen eingecheckt. Vier echte Contao-Seiten dienen als Demo-Grundlage.

Validierung: Datenbank mit `make reset` frisch erstellt, anschließend `make check` vollständig grün: ECS, Twig-CS (noch keine Elemente), Composer validate / normalize für alle Pakete und App, Twig-/YAML-/Container-Lints, PHPStan Level 8, PHPUnit (1 Test / 9 Assertions), vp Format/Lint und vier Playwright-Tests mit Axe und Request-Prüfung. In diesem Stadium enthält die Demo noch keine interaktiven Bundle-Elemente.

Seed verwendet Contao-Modelle und dieselbe Datenbankverbindung wie der Kern, damit Transaktionen und virtuelle Felder unterstützt werden. Symfony-Debug-Toolbar in der Demo deaktiviert (deren Status-Badge hatte einen schweren Axe-Kontrastbefund). Mailhinweise der Kundenstimmen gehen nur an das lokale Mailpit (Port 8131, `CONTAO_UI_MAIL_PORT`).

Noch offen: reale 6.0-Installation und sämtliche Funktionsphasen. Keine Produktentscheidung von Arne erforderlich.

### Ergänzung · Contao 6

Auch das unveränderte Gerüst unter Contao 6.0.2 / PHP 8.4.26 installiert, migriert und mit der gesamten Check-Kette inklusive vier Axe-/Playwright-Seitenprüfungen grün geprüft. Die erzeugte Composer-Anforderung verwendet die normalisierte Form `~6.0.0`. Twig-CS-Cache aus Git entfernt und ignoriert. Danach Entwicklung wieder auf 5.7 zurückgesetzt.

## Phase 2 · Carousel (2026-09-30)

Native Kind-Elemente, drei Container-Breakpoints (unter 600 / ab 600 / ab 960 px), dezimale Slides pro Ansicht, Pfeile, Punkte, Autoplay mit Pause und Mausziehen umgesetzt. Layout und Verhalten stammen aus den unveränderten npm-Dateien; eigenes JS bindet ausschließlich die Original-API und Plugins an. Twig fügt Styles und Module nur auf verwendenden Seiten hinzu und dedupliziert sie. Ausgeblendete Kinder werden vom Kern herausgefiltert.

Der optionale Schalter unter System → Einstellungen ersetzt native Kern-Swiper mit unverändertem Standard-Twig-Template. Standardmäßig aus; in der Demo bewusst an. Eigene Templates und alte Slider-Start-/Stop-Paare bleiben beim Kern. Rücksprung statt geklonter Endlosschleife; Scroll-Geschwindigkeit bestimmt der Browser. Layoutskripte, die ein Betreiber manuell eingebunden hat, werden nicht entfernt.

Validierung nach frischem Reset: gesamte Check-Kette grün, 4 PHPUnit-Tests / 20 Assertions, 11 Playwright-Tests. Geprüft: drei veröffentlichte Kinder, Pfeile, Punkte, Home/End/ArrowRight, echtes CDP-Touch-Wischen, native Wheel-Bedienung ohne JS, responsive Container-Werte, Reduced-Motion-Autoplay mit Play/Pause, assetfreie Übersichtsseite, Kern-Swiper-Ersatz, Axe und keine Swiper-/jQuery-Requests. Der Ersatz-Controller hat zusätzlich Tests für den ausgeschalteten Schalter und eigene Templates.

Keine offenen Produktfragen. Contao-6-Interaktionsprüfung folgt zur finalen Matrix-Abnahme.

## Phase 3 · Sheet (2026-09-30)

Bottom-Sheet, linke/rechte Seitenleiste und zentrierter Dialog mit nativen Kind-Elementen umgesetzt. Einrastpunkte werden als geordnete, eindeutige Viewport-Prozentwerte von 5–95 gerendert. Eigenes Dialog-Button-Element erzeugt `commandfor` für Öffnen oder bewusstes Schließen. Die Option „Schließen erlauben“ steuert Standard-Schließtaste, Escape, Hintergrund und Wisch-Dismissal; ein expliziter Abschluss-Button darf auch einen nicht wegklickbaren Dialog schließen. Das native `closedby` schützt diesen Dialog zusätzlich ohne JS.

Offcanvas-Modul bettet ein vorhandenes **Kern-Navigationsmodul** ein (keine eigene Menürekursion), mit Burger-Button, Original-History-Plugin und Fokus-Rückgabe. Alle sichtbaren Labels deutsch / englisch übersetzt; Backend verwendet ausschließlich native Contao-Widgets. Original-Sheet-JS/CSS bleiben unverändert, Plugins laden nur nach Bedarf.

Validierung: vollständiges `make check` grün, 5 PHPUnit-Tests / 23 Assertions und 19 Playwright-Tests. Alle vier Darstellungen öffnen, schließen mit Escape, geben Fokus zurück und bestehen Axe. Hintergrund-Tipp, Invoker Commands ohne JS, nicht wegklickbarer Dialog mit Abschluss-Button sowie mobile Kern-Navigation mit Zurück-Taste und Linknavigation sind geprüft. Demo wurde für die Phase frisch aufgebaut.

Browser ohne Invoker Commands benötigen die progressive JS-Ergänzung; dokumentierte Browsergrenzen der OSS-Komponente gelten. Keine offenen Produktfragen.

## Phase 4 · Gallery (2026-09-30)

Datei- und Ordnerauswahl über Contaos virtuelle Dateiverwaltung, Dublettenfilter, manuelle/Name-/Datum-Sortierung und FigureBuilder umgesetzt. Contao-Bildgrößen erzeugen responsive Picture-/Srcset-Ausgaben; Alt-Texte, Titel und Bildunterschriften stammen aus den Metadaten. Vorschaubilder laden lazy. Raster, wechselnde Formate und Carousel-Leiste verwenden dieselbe Reihenfolge. Die Vollbild-Lightbox kombiniert unveränderte OSS-APIs von Sheet und Carousel; der gewählte Link öffnet direkt sein Bild. Nicht aktive Großbilder sind inert und für Screenreader ausgeblendet; Escape und Schließen geben Fokus zurück.

Zusätzlich besitzen alle drei Bundles kleine Twig-Asset-Funktionen. Includes/Embeds laden automatisch und dokumentweit genau einmal; Produktbilder-Beispiel und vollständige Template-Verträge stehen in `docs/integration.md`. Andere Bundle-Repositories bleiben unverändert.

Validierung nach frischem Demo-Reset: vollständiges `make check` grün, 5 PHPUnit-Tests / 23 Assertions und 26 Playwright-Tests. Sieben neue Galerie-Tests prüfen acht Dateien, Datei-/Ordnerauswahl mit Dubletten und Nicht-Bild-Datei, drei Sortierungen, responsive Quellen, Metadaten, Vollbild, spätere Startbilder und Wiederöffnen, Pfeile/Home/End, echtes CDP-Touch-Wischen, Fokus, Axe, Bilderlinks und native Leiste ohne JS. Die bestehenden Request-Prüfungen bleiben grün. Demo-Bilder sind lokal erzeugte geometrische Studien, keine externen Downloads. Der Seed publiziert die neu erzeugten Dateien jetzt auch per `contao:symlinks`.

Abweichungen: CSS-Raster mit wechselnden Formaten statt Spalten-Masonry bewahrt Lese- und Tab-Reihenfolge. Der optionale globale Kern-Lightbox-Ersatz ist begründet zurückgestellt (Roadmap): eigene Templates, Pagination, externe Metadatenlinks und manuell eingebundene Skripte müssen für einen sicheren Ersatz gesondert geprüft werden. Kein Produktentscheid von Arne erforderlich. Contao-6-Funktionsprüfung und Release-Artefakte folgen in Phase 5.

## Phase 5 · Release-fähig (2026-09-30)

Alle Paket-READMEs enthalten Manager-/Composer-Installation nach Veröffentlichung, Positionierung, reproduzierbare gzip-Größen, Hell-/Dunkel-Screenshots aus Playwright und die OSS-Quellen. Die Twig-Integration liegt auch in jedem eigenständigen Paket. Die drei Original-Symbole aus `Icon.astro` sind als SVG und native Backend-Vorschau eingebunden; CSS-Masken übernehmen Contaos Hell-/Dunkel-Farben. Sheet-Panels verwenden System-Farb-Tokens. Die Galerie aktiviert Contaos nativen Datei- und Sortier-Widget-Modus; ihre Feldübersetzungen sind sichtbar geprüft.

GitHub Actions prüft 5.7/PHP 8.3 und 6.0/PHP 8.4 in getrennten Jobs und baut danach drei versionierte Manager-ZIPs samt SHA-256-Datei. Lokal erstellen `make artifacts` und die Python-Skripte dieselben Paket-Exports aus dem eingecheckten Git-Baum. Der Artefaktcheck prüft erlaubte Dateien, Manager-Manifeste, Galerie-Abhängigkeiten, eigenständige Dokumentationslinks und unveränderte npm-Prüfsummen. `.gitattributes` entfernt Entwicklungsdateien; Split-Ziele und späterer Ablauf sind in `docs/releasing.md` dokumentiert. Kein Split oder GitHub-Lauf ausgeführt, kein Remote oder Push.

Finale Validierung: vollständiges `make check6` unter Contao 6.0.2/PHP 8.4.26 grün; danach `make reset && make check` unter Contao 5.7.13/PHP 8.3.35 grün. Je Variante 5 PHPUnit-Tests / 23 Assertions und 34 Playwright-Tests sowie ECS, Twig-CS, Composer validate/normalize, Twig-/YAML-/Container-Lints, PHPStan Level 8, vp Format/Lint, Asset- und ZIP-Audit. Neue Tests prüfen Frontend und Backend in Hell/Dunkel, native Icons und editierbare Felder, übersetzte Galerie-Labels, fehlende Asset-/JavaScript-Fehler und Axe ohne schwere Befunde. Die Funktions- und No-JavaScript-Tests sowie die Prüfung gegen Swiper-/jQuery-Requests laufen auf beiden Contao-Versionen. Screenshots wurden visuell kontrolliert.

Die Backend-Abnahme deckte zwei Demo-Stack-Probleme auf: `contao-component-dir` fehlte für die Installation der Kern-Backend-Assets, und die Dev-Konfiguration importierte die gemeinsame Konfiguration nicht. Beide sind korrigiert. Symfony-Locks liegen jetzt in `var/locks`, Runtime-Verzeichnisse sind für PHP-FPM beschreibbar. `make check6` stellt auch nach einem Fehler die 5.7-Installation wieder her.

Abweichungen bleiben die in Phase 4 begründete Raster-Variante und der zurückgestellte optionale Kern-Lightbox-Ersatz. Die Pakete sind lokal release-fähig, noch nicht auf Packagist veröffentlicht. Manager-ZIPs wurden strukturell geprüft; die Demo enthält keine Manager-Upload-UI. Keine offenen Produktfragen für Arne. Alle Welle-1-Phasen abgeschlossen; Entwicklungsdemo läuft wieder auf 5.7 unter Port 8101.

## Audit und Nachbesserung · 30.09.2026

Das anschließende [Audit](audit.md) dokumentiert fünf mit Regressionstests behobene Fehler: Rechte der Zielauswahl, öffentliche Bindung der lokalen Demo, Steuerelemente und Mausziehen verschachtelter Carousels sowie kombinierte Tastenkürzel in der Galerie. Zusätzliche Tests prüfen Cache-Trennung, HTML-artige Beschriftungen, manipulierte Backend-Anfragen, Template Studio und alle Dialogdarstellungen ohne JavaScript. Die abschließende Matrix, vollständige Prüfausgaben und neu aufgeworfene Fragen an Arne stehen im Audit; die frühere Aussage zu offenen Produktfragen gilt damit nur für die Welle-1-Abnahme.
