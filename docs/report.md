# Berichte an Arne

## 2026-09-30 · Ausgangslage

Das Repository war leer (initialisiertes Git ohne Commit). Die Roadmap wurde aus dem vollständigen Welle-1-Brief angelegt. Keine Referenzprojekte verändert, kein Remote angelegt.

Prüfungen und abgeschlossene Phasen werden im selben Lauf unten ergänzt.

## Phase 0 / 1 · Gerüst (2026-09-30)

Drei eigenständige Bundles inklusive Manager-Registrierung, Docker mit Contao 5.7.13 / PHP 8.3.35 auf 8101, 6.0-Variante und alle angeforderten Check-Werkzeuge angelegt. Original-npm-Dateien 0.1.5 / 0.5.0 samt MIT-Lizenzen und Prüfsummen eingecheckt. Vier echte Contao-Seiten dienen als Demo-Grundlage.

Validierung: Datenbank mit `make reset` frisch erstellt, anschließend `make check` vollständig grün: ECS, Twig-CS (noch keine Elemente), Composer validate / normalize für alle Pakete und App, Twig-/YAML-/Container-Lints, PHPStan Level 8, PHPUnit (1 Test / 9 Assertions), vp Format/Lint und vier Playwright-Tests mit Axe und Request-Prüfung. In diesem Stadium enthält die Demo noch keine interaktiven Bundle-Elemente.

Seed verwendet Contao-Modelle und dieselbe Datenbankverbindung wie der Kern, damit Transaktionen und virtuelle Felder unterstützt werden. Symfony-Debug-Toolbar in der Demo deaktiviert (deren Status-Badge hatte einen schweren Axe-Kontrastbefund). Kein Mailversand erforderlich, deshalb kein Mailpit; 8121 bleibt frei.

Noch offen: reale 6.0-Installation und sämtliche Funktionsphasen. Keine Produktentscheidung von Arne erforderlich.
