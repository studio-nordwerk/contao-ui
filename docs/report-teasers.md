# Teaser und Kundenstimmen

## Was ist neu

Nachrichten, Termine und Kundenstimmen lassen sich als Karten zeigen: nebeneinander,
untereinander oder zum Blättern. Kundinnen und Kunden können ihre Erfahrung über
ein Formular einreichen. Die Betreiberin erhält eine E-Mail und prüft die Stimme
im Backend. Erst nach ihrer Freigabe erscheint sie auf der Website. Vorhandene
Oveleon-Stimmen können zur Prüfung übernommen werden.

## Umsetzung

Zwei eigenständige MIT-Pakete: `nordwerk/contao-teasers-bundle` und
`nordwerk/contao-testimonials-bundle`, Contao ^5.7 || ^6.0, nur Twig. Quellenvertrag
über `nordwerk.teaser_source`; Drittanbieter-Beispiele für neue/passende Mini-Shop-Produkte
sind dokumentiert, keine Shop-Anbindung eingebaut. Themes überschreiben Karten,
Listen oder Formular im Template Studio. Code englisch, sichtbare Texte deutsch,
englische Übersetzungen vorhanden.

Teaser enthält Inhaltselement und Frontend-Modul, News nach Archiv/optional Codefog-Kategorie,
Events mit Wiederholungen aus dem Kern-Generator und Kundenstimmen als getrennt
registrierte Quelle. Größen aus dem Contao Image Studio, Raster/Liste/native Carousel.
Quellen liefern Klartext, Links werden auf sichere URL-Formen begrenzt und Bilder
auf den öffentlichen Contao-Dateibestand geprüft. Private Felder erscheinen nie
im Kartenmodell. Ausgabe ist `private, no-store`, damit Rechte und Freigaben sofort
wirken. Events suchen nächste Startzeitpunkte innerhalb von zwei Jahren, bereits
laufende Termine werden ausgelassen. Keine öffentliche Zusatzcache-Schicht.

Kundenstimmen haben versionierte Archive und Datensätze, öffentlichen Prüfhinweis,
internen Prüfvermerk und explizite Backend-Freigabe. Einreichung erfordert konkrete
Einwilligung und eine veröffentlichte Datenschutzseite. Contao-CSRF, serverseitige
Validierung, Sitzungstoken gegen erneutes Senden und Honeypot. Mailhinweis ohne
Kundentext/E-Mail, ausstehende Benachrichtigungen können per CLI erneut versendet werden.
Keine IP-Speicherung, kein Review-/AggregateRating-Markup. Betreiber legen konkrete
Löschfristen und Verfahren für Widerrufe fest; siehe `docs/rules.md`.

Oveleon-Import: Quelltabellen nur lesen, explizites Quell-/Zielarchiv, Dry-run als
Standard, transaktionaler Import, Herkunftsschlüssel gegen Duplikate, HTML zu
Klartext, lokale öffentliche Bilder auf UUID, externe Bild-URLs nur als Herkunft.
Alte Freigabe/Opt-in-Status werden protokolliert, nicht als Einwilligung übernommen.
Alle Ziele bleiben unveröffentlicht. Details und Quellen: `docs/landscape-teasers.md`.

## Umgebung und bestehende Pakete

Worktree `contao-ui-teasers`, Branch `feat/teasers`. Eigenes Compose-Projekt
`contao-ui-teasers`, Web http://localhost:8102, Mailpit http://localhost:8122.
Demo ausschließlich fiktiv, inklusive Codefog-Testrelation und Oveleon-Testtabellen.
Keine fremden Container ersetzt. Linux-Ownership vor Browsertests bleibt in
`scripts/check.sh` erhalten. Bestehende CI-Matrix prüft beide neuen Pakete mit;
Manager-Artifact-Prüfung wurde auf fünf Pakete erweitert.

Carousel, Sheet und Galerie werden nicht geändert. Carousel-Komponente und
Asset-Funktion lassen sich unverändert aus Twig einbetten. Es wird keine Änderung
an den drei bestehenden Paketen benötigt.

## Prüfung

Gezielte Prüfungen auf Contao 5.7: Nachrichten-Raster/-Carousel, drei nächste Events,
Wiederholungen, Kategorienfilter, Ausschluss geschützter/unveröffentlichter/geplanter/
abgelaufener Inhalte und privater Bilder. Echte Nachrichtenleserlinks, Formular ohne
JavaScript, fehlende Einwilligung, ungültige Sterne, CSRF-Ablehnung, erneutes Senden,
Mailpit-Mail, Backend-Ablehnung ohne Prüfvermerk und anschließende Freigabe/Anzeige.
Import-Dry-run, Rollback bei ungültigem Datensatz und idempotenter Wiederholung.
Template-Studio und deutsche Backend-Felder, Axe in hell/dunkel und Screenshots.
Zusätzliche agent-browser-Formularprüfung: keine Axe-Befunde.

| Installation                           | Ergebnis                                               |
| -------------------------------------- | ------------------------------------------------------ |
| Contao 5.7.13 / PHP 8.3.35             | `make reset && make check`: grün                       |
| Contao 6.0.2 / PHP 8.4.26 / DBAL 4.4.5 | `make check6`: grün, danach 5.7-Demo wiederhergestellt |

Je Installation: ECS, Twig-CS, Composer validate/normalize, Twig/YAML/Container-Lint,
PHPStan Level 8, 18 PHPUnit-Tests (49 Assertions), Vite-Plus-Format/Lint und 47
Browsertests erfolgreich. Alle fünf Manager-ZIPs werden aus dem Git-Stand erzeugt
und auf Manifest, Lizenz, vollständige Exporte und unveränderte Runtime-Assets geprüft.
Die sieben neuen Browsertests sind Teil beider Matrix-Läufe; keine schweren Axe-Befunde.
Das ist eine technische Prüfung, keine Behauptung vollständiger Barrierefreiheit.

Prüfprotokolle: `docs/check-teasers-contao57.txt` und `docs/check-teasers-contao6.txt`.
Der Lock-Stand behält die bestehenden Drittanbieter-Versionen der 5.7-Demo bei;
zusätzlich aufgenommen sind nur die beiden neuen Path-Pakete.
Keine Pushes, Veröffentlichung, Tags oder Änderungen an bestehenden Bundles.
