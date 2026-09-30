# Berichte an Arne

## 2026-09-30 · Ausgangslage

Das Repository war leer (initialisiertes Git ohne Commit). Die Roadmap wurde aus dem vollständigen Welle-1-Brief angelegt. Keine Referenzprojekte verändert, kein Remote angelegt.

Prüfungen und abgeschlossene Phasen werden im selben Lauf unten ergänzt.

## Phase 0 / 1 · Gerüst (2026-09-30)

Drei eigenständige Bundles inklusive Manager-Registrierung, Docker mit Contao 5.7.13 / PHP 8.3.35 auf 8101, 6.0-Variante und alle angeforderten Check-Werkzeuge angelegt. Original-npm-Dateien 0.1.5 / 0.5.0 samt MIT-Lizenzen und Prüfsummen eingecheckt. Vier echte Contao-Seiten dienen als Demo-Grundlage.

Validierung: Datenbank mit `make reset` frisch erstellt, anschließend `make check` vollständig grün: ECS, Twig-CS (noch keine Elemente), Composer validate / normalize für alle Pakete und App, Twig-/YAML-/Container-Lints, PHPStan Level 8, PHPUnit (1 Test / 9 Assertions), vp Format/Lint und vier Playwright-Tests mit Axe und Request-Prüfung. In diesem Stadium enthält die Demo noch keine interaktiven Bundle-Elemente.

Seed verwendet Contao-Modelle und dieselbe Datenbankverbindung wie der Kern, damit Transaktionen und virtuelle Felder unterstützt werden. Symfony-Debug-Toolbar in der Demo deaktiviert (deren Status-Badge hatte einen schweren Axe-Kontrastbefund). Kein Mailversand erforderlich, deshalb kein Mailpit; 8121 bleibt frei.

Noch offen: reale 6.0-Installation und sämtliche Funktionsphasen. Keine Produktentscheidung von Arne erforderlich.

### Ergänzung · Contao 6

Auch das unveränderte Gerüst unter Contao 6.0.2 / PHP 8.4.26 installiert, migriert und mit der gesamten Check-Kette inklusive vier Axe-/Playwright-Seitenprüfungen grün geprüft. Die erzeugte Composer-Anforderung verwendet die normalisierte Form `~6.0.0`. Twig-CS-Cache aus Git entfernt und ignoriert. Danach Entwicklung wieder auf 5.7 zurückgesetzt.

## Phase 2 · Carousel (2026-09-30)

Native Kind-Elemente, drei Container-Breakpoints (unter 600 / ab 600 / ab 960 px), dezimale Slides pro Ansicht, Pfeile, Punkte, Autoplay mit Pause und Mausziehen umgesetzt. Layout und Verhalten stammen aus den unveränderten npm-Dateien; eigenes JS bindet ausschließlich die Original-API und Plugins an. Twig fügt Styles und Module nur auf verwendenden Seiten hinzu und dedupliziert sie. Ausgeblendete Kinder werden vom Kern herausgefiltert.

Der optionale Schalter unter System → Einstellungen ersetzt native Kern-Swiper mit unverändertem Standard-Twig-Template. Standardmäßig aus; in der Demo bewusst an. Eigene Templates und alte Slider-Start-/Stop-Paare bleiben beim Kern. Rücksprung statt geklonter Endlosschleife; Scroll-Geschwindigkeit bestimmt der Browser. Layoutskripte, die ein Betreiber manuell eingebunden hat, werden nicht entfernt.

Validierung nach frischem Reset: gesamte Check-Kette grün, 4 PHPUnit-Tests / 20 Assertions, 11 Playwright-Tests. Geprüft: drei veröffentlichte Kinder, Pfeile, Punkte, Home/End/ArrowRight, echtes CDP-Touch-Wischen, native Wheel-Bedienung ohne JS, responsive Container-Werte, Reduced-Motion-Autoplay mit Play/Pause, assetfreie Übersichtsseite, Kern-Swiper-Ersatz, Axe und keine Swiper-/jQuery-Requests. Der Ersatz-Controller hat zusätzlich Tests für den ausgeschalteten Schalter und eigene Templates.

Keine offenen Produktfragen. Contao-6-Interaktionsprüfung folgt zur finalen Matrix-Abnahme.
