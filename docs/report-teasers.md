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
Validierung, Sitzungstoken mit gebundenem Einwilligungsnachweis, Honeypot und
serverseitige Drosselung auf fünf Sendeversuche je Clientadresse in 15 Minuten. Mailhinweis ohne
Kundentext/E-Mail, ausstehende Benachrichtigungen können per CLI erneut versendet werden.
Keine Speicherung von IPs im Klartext: Der kurzlebige Drosselungszustand verwendet
täglich wechselnde HMAC-Pseudonyme. Kein Review-/AggregateRating-Markup. Betreiber legen konkrete
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
Zusätzliche agent-browser-Formularprüfung aus der Erstabnahme: keine Axe-Befunde.
Nach der Review-Nacharbeit wurden Reset, Gesamtprüfung und Matrix erneut ausgeführt.

| Installation                           | Ergebnis                                               |
| -------------------------------------- | ------------------------------------------------------ |
| Contao 5.7.13 / PHP 8.3.35             | `make reset && make check`: grün                       |
| Contao 6.0.2 / PHP 8.4.26 / DBAL 4.4.5 | `make check6`: grün, danach 5.7-Demo wiederhergestellt |

Je Installation: ECS, Twig-CS, Composer validate/normalize, Twig/YAML/Container-Lint,
PHPStan Level 8, 29 PHPUnit-Tests (125 Assertions), Vite-Plus-Format/Lint und 47
Browsertests erfolgreich. Alle fünf Manager-ZIPs werden aus dem Git-Stand erzeugt
und auf Manifest, Lizenz, vollständige Exporte und unveränderte Runtime-Assets geprüft.
Die sieben neuen Browsertests sind Teil beider Matrix-Läufe; keine schweren Axe-Befunde.
Das ist eine technische Prüfung, keine Behauptung vollständiger Barrierefreiheit.

Prüfprotokolle: `docs/check-teasers-contao57.txt` und `docs/check-teasers-contao6.txt`.
Der Lock-Stand behält die bestehenden Drittanbieter-Versionen der 5.7-Demo bei;
zusätzlich aufgenommen sind nur die beiden neuen Path-Pakete.
Keine Pushes, Veröffentlichung, Tags oder Änderungen an bestehenden Bundles.

## Review-Nacharbeit

Die acht Review-Befunde wurden jeweils zuerst durch einen Regressionstest
reproduziert (PHPUnit beziehungsweise Paketexport-Prüfung) und dann behoben:

1. Der Sitzungstoken bindet Einwilligungstext und Datenschutzlink an die Ausgabe.
   Eine Konfigurationsänderung zwischen Anzeige und Absenden verändert den Nachweis nicht.
2. News- und Kalenderoptionen prüfen dieselben Contao-Berechtigungen wie die Kernmodule;
   eingeschränkte Redakteure sehen nur erlaubte Archive, Administratoren alle.
3. Ein serverseitiger Symfony Rate Limiter begrenzt neue GET/POST-Zyklen auch mit
   frischen Sitzungen und wechselnden Modulen. Abgewiesene Versuche erzeugen keinen
   Datensatz und rufen die Betreiberbenachrichtigung nicht auf. Cache-Zustand und
   Dateisperren enthalten nur täglich wechselnde, geheime HMAC-Pseudonyme, keine IP
   im Klartext. Das gleitende Fenster ist 15 Minuten lang, Zustand verfällt nach
   spätestens 30 Minuten. Mehrere PHP-Hosts benötigen gemeinsame Cache-/Lock-Speicherung.
4. Formularaktion und Redirect bewahren `/cms/` und Queryparameter.
5. Der Import löst `{{file::UUID}}`, `files/…` und `/files/…` über denselben
   öffentlichen Dateischutz auf. Fehlende, ungültige und externe Referenzen bleiben leer.
6. Jede Renderinstanz hat eigene DOM-IDs für Felder, Labels und Fehler; die
   POST-Modulkennung ist separat als `form_submit` verfügbar.
7. Die Registry akzeptiert ausschließlich speicherbare, eindeutige Schlüssel mit
   1–64 ASCII-Zeichen. Interface und README dokumentieren diese Grenze. Das README
   zeigt einen vollständigen Fremd-Quelldienst mit Beispiel-Schema, Registrierung,
   Backend-/Frontend-Rechten, Sortierung, Begrenzung und Kartenmapping. Es benennt
   die vom Fremdpaket benötigten Shop-Voter ausdrücklich.
8. Beide Integrationsdokumentationen beschreiben ihre tatsächlichen Quellen-, Karten-,
   Renderer- und Formularverträge. Die ZIP-Prüfung stellt sicher, dass eigenständige
   Pakete diese Dokumente enthalten und keine unverfügbaren Komponenten versprechen.

Die neuen Formular-/Importtests nutzen ausschließlich fiktive Demo-Dateien und
-konfiguration; Schreibzugriffe und Benachrichtigungen werden in PHPUnit abgefangen.
Das Fremdquellen-PHP-Beispiel im README wurde zusätzlich mit `php -l` geprüft.
