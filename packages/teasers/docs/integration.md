# Twig-Bausteine für andere Bundles

Die Bausteine benötigen kein Inhaltselement. Sie funktionieren in einem Contao-Frontend-Request und laden ihre Assets automatisch über `nw_carousel_assets()`, `nw_sheet_assets(dismissible = true)` und `nw_gallery_assets(interactive = true)`. Bei eigenem Markup dürfen diese Funktionen mit `{% do … %}` aufgerufen werden. Benannte Contao-Twig-Sammlungen binden CSS und Module je Dokument einmal ein, auch bei mehreren verschachtelten Komponenten. Plugins laden nach Bedarf. Keine manuelle Einbindung im Seitenlayout nötig.

IDs müssen im Dokument eindeutig sein und dürfen nur ASCII-Buchstaben, Ziffern und Bindestriche enthalten (mit Buchstaben beginnen). Optionen sind vertrauenswürdige Template-Konfiguration, keine ungeprüften Formulardaten.

## Carousel

`@Contao/component/_nw_carousel.html.twig`: `carousel_id`, `carousel_label` und `carousel_options`. Block `carousel_slides`: genau ein direktes HTML-Element je Slide. `small`, `medium`, `large` sind Zahlen von 1–12 (Bruchteile erlaubt), Container-Breakpoints 600/960 px. Optionen: `arrows`, `dots`, `drag` (bool), `autoplay` (0 oder Millisekunden 1000–60000), `initial` (0-basierter Index), `rewind` (bool). Deutsche/englische Steuerungslabels kommen aus der aktuellen Locale.

```twig
{% embed '@Contao/component/_nw_carousel.html.twig' with {
    carousel_id: 'featured-products', carousel_label: 'Unsere Produkte',
    carousel_options: {small: 1, medium: 2, large: 3, drag: true}
} %}
    {% block carousel_slides %}
        {% for product in products %}<article>{{ product.name }}</article>{% endfor %}
    {% endblock %}
{% endembed %}
```

## Sheet

`@Contao/component/_nw_sheet.html.twig`: `sheet_id`, `sheet_label`, optional `sheet_class`, `sheet_options`. Block `sheet_body`: beliebiger HTML-Inhalt. Optionen: `presentation` (`bottom`, `start`, `end`, `center`), `dismissible`, `drag`, `history` (bool), `snaps` (aufsteigende Viewport-Prozentwerte zwischen 5 und 95, z. B. `[50, 75]`). Keine Snaps für zentrierte Dialoge oder Seitenleisten nötig. Trigger können überall im Dokument stehen.

```twig
<button type="button" commandfor="mini-cart" command="show-modal">Warenkorb öffnen</button>
{% embed '@Contao/component/_nw_sheet.html.twig' with {
    sheet_id: 'mini-cart', sheet_label: 'Warenkorb',
    sheet_options: {presentation: 'end', dismissible: true, snaps: [], drag: false, history: true}
} %}
    {% block sheet_body %}{% include '@MyShop/cart.html.twig' %}{% endblock %}
{% endembed %}
```

Native Invoker Commands öffnen ohne JavaScript in unterstützten Browsern; die Original-Komponente ergänzt ältere Browser. Für einen nicht wegklickbaren Dialog einen expliziten Button mit `command="close"` anbieten. Links können stattdessen im eigenen JS die dokumentierte Sheet-API aufrufen; ein echter Button mit `commandfor` ist der native Auslöser.

## Produktbilder mit Lightbox

`@Contao/component/_nw_gallery.html.twig`: `gallery_id`, `gallery_label`, `gallery_images` (Liste von Contao-`Figure`-Objekten), optional `gallery_layout` (`grid`, `mosaic`, `rail`), `gallery_columns` (1–6), `gallery_lightbox` (bool, Standard `true`). Thumbnail, Vollbild und Bildunterschrift nutzen die Kern-Figure-/Picture-Blöcke. Metadatenlinks auf externe Ziele bleiben Links. Nur Figures mit `hasLightbox` und `lightbox.hasImage` werden als Lightbox-Bilder behandelt.

Ein Controller kann den Dienst `Nordwerk\GalleryBundle\Image\GalleryFigures` injizieren:

```php
$images = $galleryFigures->build(
    $productImageUuids, // Liste binärer/String-UUIDs oder serialisierte Multi-Source
    [0, 0, $contaoImageSizeId],
    'custom', // Auswahlreihenfolge; alternativ name_asc/desc, date_asc/desc
    lightbox: true,
);
$template->set('product_images', $images);
```

```twig
{% include '@Contao/component/_nw_gallery.html.twig' with {
    gallery_id: 'product-images-' ~ product.id,
    gallery_label: 'Produktbilder: ' ~ product.name,
    gallery_images: product_images,
    gallery_layout: 'rail', gallery_columns: 3, gallery_lightbox: true
} %}
```

Einzeln erzeugte Figures sind ebenfalls möglich: `figure(uuid, [800, 600, 'proportional'], {enableLightbox: true})`. Die Lightbox-Bildgröße kommt wie im Kern aus dem Seitenlayout. Ohne JavaScript zeigt die Galerie alle Vorschaubilder und öffnet große Bilder als normale Links; die Leiste bleibt scrollbar. Mit JavaScript öffnet der gewählte Link direkt sein Bild im Vollbild-Sheet. Pfeile, Home/End, Escape und Wischen verwenden die Original-APIs. Der Fokus kehrt zum Link zurück.

Die Galerie verwendet ein Raster mit wechselnden Formaten statt Spalten-Masonry: visuelle Reihenfolge, Tab-Reihenfolge und Dateisortierung bleiben gleich. Themes können die eigenen Galerie-Regeln und die dokumentierten `--sc-*`-/`--ss-*`-Tokens überschreiben. Die Original-npm-Dateien bleiben unverändert.

Die Mini-Shop- und Seminar-Repositories wurden nicht verändert. Grundlage für FigureBuilder und responsive Metadaten: [Contao Image Studio](https://docs.contao.org/5.x/dev/framework/image-processing/image-studio/).
