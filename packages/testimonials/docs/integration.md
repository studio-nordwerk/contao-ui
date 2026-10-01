# Kundenstimmen-Integration

Das Paket installiert `nordwerk/contao-teasers-bundle` samt dessen News-, Calendar-
und Carousel-Abhängigkeiten. Es registriert die Quelle `testimonials` und das
Frontend-Modul `nw_testimonial_form` für die moderierte Einreichung.

## Einreichformular

Das Template Studio überschreibt `frontend_module/nw_testimonial_form.html.twig`.
Der Controller liefert:

| Variable                      | Vertrag                                                                                                                    |
| ----------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `configured`                  | Nur bei gültigem Archiv, Empfänger, Einwilligungstext und veröffentlichter Datenschutzseite wahr.                          |
| `form_id`                     | Eindeutige DOM-ID dieser Renderinstanz; Präfix für Felder, Labels und Fehlerzusammenfassung.                               |
| `form_submit`                 | Stabile Modulkennung für das versteckte `FORM_SUBMIT`; unabhängig von DOM-IDs.                                             |
| `form_action`                 | Lokale Request-URI einschließlich Installations-Unterverzeichnis und Queryparametern.                                      |
| `submission_nonce`            | Sitzungstoken, an den ausgegebenen Einwilligungstext und Datenschutzlink gebunden; zwei Stunden gültig, einmal verwendbar. |
| `values`                      | Klartextwerte für erneute Ausgabe nach Validierungsfehlern.                                                                |
| `errors`                      | Liste von Übersetzungsschlüsseln aus `messages`.                                                                           |
| `success`                     | Bestätigung der Annahme nach Redirect, keine Veröffentlichungszusage.                                                      |
| `consent_text`, `privacy_url` | Aktuell angezeigter Einwilligungstext und Link für das neue Token.                                                         |

Bei Anpassungen diese versteckten Felder beibehalten:

```twig
<input type="hidden" name="FORM_SUBMIT" value="{{ form_submit }}">
<input type="hidden" name="REQUEST_TOKEN" value="{{ contao.request_token }}">
<input type="hidden" name="submission_nonce" value="{{ submission_nonce }}">
```

Feldnamen: `name`, `text`, `email`, `role`, `source`, `stars`, `consent`, `website`
(Honeypot). Name und Erfahrung sind Pflicht, Sterne optional (1–5). Die erforderliche
Einwilligungscheckbox hat `name="consent"`, `value="1"` und ist anfangs leer.
Labels, Pflichtfelder und Fehlerzusammenfassung zugänglich lassen; `form_id` für
jedes `id`/`for` verwenden. Templates benötigen kein eigenes JavaScript.
Responses sind `private, no-store`; Contao-CSRF, serverseitige Validierung und
Tokenprüfung bleiben aktiv. Erfolgreiche Einreichungen bleiben unveröffentlicht.

Fünf Sendeversuche je Clientadresse in 15 Minuten werden serverseitig erlaubt,
unabhängig von neuen Sitzungen, Modulen und Tokens. Bei Überschreitung erscheint
`nw.testimonials.error.throttled`; weder Datensatz noch Betreiber-Mail entstehen.
Der Symfony-Cache enthält nur täglich wechselnde HMAC-Pseudonyme und kurzlebige
Zähler. Cache-/Lock-Vertrag und Proxy-Konfiguration siehe [README](../README.md).

## Ausgabe und Import

Die Teaser-Quelle liefert Name als Kartentitel, Erfahrung als Klartext, Rolle,
Quelle und öffentlichen Prüfhinweis als `meta`, Bild/Datum und optionale Sterne.
E-Mail, Einwilligungsnachweis, Herkunft und interner Prüfvermerk bleiben intern.
Die Kartentemplates und `Card`/`TeaserQuery` gehören zur installierten
Teaser-Abhängigkeit; siehe deren `docs/integration.md` im installierten Teaser-Paket.

```twig
{% extends '@Contao/component/_nw_teaser_card.html.twig' %}

{% block card_text %}
    <blockquote><p class="nw-teaser-text">{{ card.text }}</p></blockquote>
{% endblock %}
```

Der Oveleon-Import liest Quelltabellen derselben Datenbank, standardmäßig als Dry-run.
Lokale `files/…`, `/files/…` und `{{file::UUID}}` werden nur auf öffentliche Dateien
im Contao-Dateibestand aufgelöst. Externe URLs werden nicht heruntergeladen.
Alle Ziele bleiben zur Prüfung unveröffentlicht; Details und CLI-Befehle im [README](../README.md#umzug-von-oveleon).
