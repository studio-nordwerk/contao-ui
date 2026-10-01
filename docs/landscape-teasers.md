# Teaser und Kundenstimmen: Recherche

Stand: 1. Oktober 2026. Primärquellen und Quellcode geprüft; Firecrawl war ohne Credits,
daher Websuche und lokale, nur gelesene Git-Checkouts. Keine Übernahme fremder Implementierungen.

## Oveleon Recommendation

[Repository](https://github.com/oveleon/contao-recommendation-bundle), geprüfter
[Commit d3c0a74](https://github.com/oveleon/contao-recommendation-bundle/tree/d3c0a74b98d56d6b3ec5578a479504ccfc33c139)
vom 14.10.2025. MIT. Hauptzweig: PHP ^8.3, Contao ^5.3, Doctrine DBAL ^3.3;
Contao 5.7 liegt im erlaubten Bereich, Contao 6 nicht. README nennt daneben 4.13/5.3:
für Installation zählt das jeweilige Composer-Manifest. Letzter Hauptzweig-Commit rund
ein Jahr alt; daraus folgt keine Aussage über künftige Pflege oder Support.

Archive, Listen mit Filter/Paginierung, Leser und Einreichformular sind vorhanden.
Hinzu kommen optionale Moderation, Datenschutz-Checkbox, Mailhinweise, E-Mail-Opt-in,
Zugriffsschutz, Sitemap und Cache-Invalidierung. Gut: vertrautes Contao-Backend,
mehrere Archive, kein IP-Logging. Datenschutz und Moderation sind konfigurierbar.
Unsere Einreichung erzwingt Einwilligung und Prüfung vor Veröffentlichung.

Der geprüfte Code enthält PHP-Templates und native Dialoge, keinen eingebauten
Swiper-/jQuery-Slider. Ein Slider auf einer Pilotseite kann aus dem Theme stammen.
Die Dialogvariante braucht JavaScript, um Volltexte zu öffnen. Eine vollständige
Barrierefreiheitsbewertung der Erweiterung ist damit nicht erfolgt. Unsere Karten
bleiben einschließlich Text und Link ohne JavaScript erreichbar; Carousel ist eine
Verbesserung der Ausgabe. Fremde Templates werden nicht kopiert.

### Importvertrag aus dem DCA

Primärquelle: [tl_recommendation.php](https://github.com/oveleon/contao-recommendation-bundle/blob/d3c0a74b98d56d6b3ec5578a479504ccfc33c139/contao/dca/tl_recommendation.php).

| Oveleon                                               | Ziel / Umgang                                                                        |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------ |
| tl_recommendation_archive.id/title                    | explizit ausgewähltes Quellarchiv; Zielarchiv bleibt separat                         |
| tl_recommendation.id/pid                              | Herkunftsschlüssel; wiederholter Import überspringt bestehende Zeilen                |
| author                                                | name                                                                                 |
| customField                                           | role (Bedeutung im Altprojekt vor Import prüfen)                                     |
| text                                                  | text, HTML in Klartext umwandeln                                                     |
| rating                                                | stars, nur 1–5                                                                       |
| date                                                  | date (enthält nach Oveleon-Speicherung auch die Zeit)                                |
| email                                                 | private email                                                                        |
| location                                              | source / Anlass                                                                      |
| imageUrl                                              | Herkunftsnotiz; keine externen Downloads, lokale öffentliche Dateien auf UUID prüfen |
| published, verified, start, stop                      | im Herkunftsprotokoll erhalten; Ziel immer unveröffentlicht                          |
| title, alias, teaser, scope, featured, cssClass, time | Herkunftsprotokoll, kein Leser-/Sitemap-Nachbau                                      |

`verified` ist Oveleons Opt-in-Status, kein Nachweis eines Kaufs und kein übertragener
Einwilligungsnachweis. Import erfindet keine Einwilligung. Freigabe und Rechte an
Text/Bild müssen vor Veröffentlichung geprüft werden. Testdaten sind fiktiv.

## Bestehende Listen und Teaser

Contao hat [Nachrichtenlisten](https://docs.contao.org/5.x/manual/en/core-extensions/news/frontend-modules/)
und [Eventlisten](https://docs.contao.org/5.x/manual/en/core-extensions/calendar/frontend-modules/):
Archive/Kalender, Sortierung, Anzahl, Leserlinks und Contao-Bildgrößen sind bereits
etablierte Funktionen. Für Events verwenden wir den Contao-Generator inklusive
Wiederholungen. Die Teaser-Ausgabe ergänzt ein gemeinsames Kartenmodell und drei
Ansichten über verschiedene Quellen; sie ersetzt keine Nachrichten-/Eventleser.

[Codefog News Categories](https://github.com/codefog/contao-news_categories) ergänzt
Kategorien. Im geprüften Hauptzweig: Relation `tl_news_categories(news_id, category_id)`,
Kategorien `tl_news_category`; Contao ^5.1.9, also keine zugesagte Contao-6-Freigabe.
Unser optionaler Filter liest diese Relation, ohne die Erweiterung einzubetten.
Fehlt sie, gibt ein gesetzter Kategorienfilter keine ungefilterten Ergebnisse aus.

[RockSolid Custom Elements](https://github.com/madeyourday/contao-rocksolid-custom-elements)
ermöglicht frei definierte Inhaltselemente; [Heimrich & Hannot List Bundle](https://github.com/heimrichhannot/contao-list-bundle)
ist ein umfangreicher generischer Listenbaukasten. Unser bewusst kleiner Vertrag
besteht aus einem getaggten Quellendienst, Abfrage und Kartenobjekt. Theme-Anpassung
über Twig, keine Abhängigkeit von diesen Erweiterungen. Mini-Shop-Produkte werden
nur als Beispiel für eigene Quellen dokumentiert.

## Bewertungssterne in der Suche

[Google Review Snippets](https://developers.google.com/search/docs/appearance/structured-data/review-snippet):
Bewertungen über das eigene Unternehmen auf dessen Website sind bei Organization /
LocalBusiness „self-serving“, auch bei eingebundenen Drittanbieterbewertungen.
Dafür sind Bewertungssterne in Suchergebnissen nicht zulässig. Sichtbare Sterne auf
der Website sind davon zu unterscheiden. Kein Review-/AggregateRating-Markup wird
mitgeliefert. Individuelles Product-Markup für spätere Shop-Quellen muss das tatsächlich
bewertete Produkt betreffen und separat auf die Google-Regeln geprüft werden.
