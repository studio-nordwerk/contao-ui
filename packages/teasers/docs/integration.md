# Teaser-Integration

Das Paket installiert Contao News, Calendar und `nordwerk/contao-carousel-bundle`.
Kundenstimmen benötigen zusätzlich `nordwerk/contao-testimonials-bundle`.
Die hier beschriebenen Templates und Dienste sind mit diesen Abhängigkeiten verfügbar.

## Quellenvertrag

Ein Dienst implementiert `Nordwerk\TeasersBundle\Source\TeaserSourceInterface`
und erhält das Tag `nordwerk.teaser_source` (bei Autoconfiguration automatisch).
`getKey()` liefert einen eindeutigen Schlüssel nach `[a-z][a-z0-9_]{0,63}` mit
höchstens 64 ASCII-Zeichen; ungültige/mehrfach verwendete Schlüssel brechen den
Registry-Aufbau ab. `getLabel()` ist ein Übersetzungsschlüssel aus `messages`.
`getArchives()` liefert `{positive ID: Name}` ausschließlich für vom aktuellen
Backend-Benutzer auswählbare Archive/Kategorien. Die eingebauten Quellen prüfen
Contao-News- beziehungsweise Kalenderberechtigungen. Eigene Quellen müssen ihre
Backend-Berechtigungen selbst anwenden.

`fetch(TeaserQuery $query)` liefert höchstens `limit` öffentliche, für den aktuellen
Frontend-Benutzer berechtigte `Card`-Objekte in der gewünschten Reihenfolge.
Leere Archivauswahl liefert keine Einträge. `TeaserQuery` enthält `archives`,
`categories`, `limit` (1–100), `sort` (`date_desc`, `date_asc`, `title_asc`,
`stars_desc`) und `minStars`. Nicht unterstützte Filter und Sortierungen dokumentieren.
Keine Benutzer-/Requestzustände dauerhaft im Quelldienst behalten.

`Card`: `title`, `text` (Klartext), `image` (öffentliche lokale Contao-UUID/Pfad),
`link` (HTTP(S), absoluter lokaler Pfad oder Fragment), `date` (`DateTimeImmutable`
oder null), `meta` (String-Zuordnung), `price` (fertiger Anzeige-String oder null),
`stars` (1–5 oder null). Keine privaten Einreichungsdaten in Karten ausgeben.
Ein vollständiger Fremd-Quelldienst einschließlich Registrierung steht im [README](../README.md#eigene-quellen).

## Twig-Vertrag

Das Template Studio kann `content_element/nw_teaser.html.twig`,
`frontend_module/nw_teaser.html.twig`, `component/_nw_teasers.html.twig` und
`component/_nw_teaser_card.html.twig` überschreiben.

Die Liste erhält `cards` als Liste von `{card, figure}`, `teaser_id` (eindeutige
DOM-ID), `teaser_label`, `teaser_layout` (`grid`, `list`, `carousel`) und
`teaser_columns` (1–6). `figure` ist eine Contao-Studio-Figure oder null; der Renderer
prüft den öffentlichen Dateibestand und nutzt die konfigurierte Contao-Bildgröße.
Der Renderer setzt `private, no-store`, damit Veröffentlichung und Mitgliedsrechte
sofort wirken. Eigene Controller müssen diesen Cache-Vertrag ebenfalls beachten.

Die Karte bietet `card_image`, `card_title`, `card_date`, `card_text`, `card_meta`,
`card_stars` und `card_price`. Twig escaped den Quellen-Klartext automatisch.

```twig
{% extends '@Contao/component/_nw_teaser_card.html.twig' %}

{% block card_price %}
    {% if card.price %}<p class="product-price">{{ card.price }}</p>{% endif %}
{% endblock %}
```

Für eigene Controller kann `Nordwerk\TeasersBundle\Rendering\TeaserRenderer`
injiziert werden: `render($template, $data, $uniqueDomId)` erwartet die DCA-Felder
`nwTeaserSource`, `nwTeaserArchives`, `nwTeaserCategories`, `nwTeaserLimit`,
`nwTeaserSort`, `nwTeaserMinStars`, `nwTeaserLayout`, `nwTeaserColumns`,
`nwTeaserLabel` und `size` (Contao-Bildgröße). Rückgabe ist eine private Response.

Die Carousel-Ansicht bindet `@Contao/component/_nw_carousel.html.twig` aus der
installierten Carousel-Abhängigkeit ein; Assets werden automatisch geladen.
Ohne JavaScript bleiben alle Karten und Links erreichbar. Es entsteht kein
Review-/AggregateRating-Markup.
