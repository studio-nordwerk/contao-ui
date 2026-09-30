# Contao-UI-Familie · Welle 1

Alle Anforderungen aus `brief-2026-09-30-contao-ui-welle1.md` gelten. Qualität vor Umfang. Jede Phase wird erst nach grünen Checks abgehakt und separat committet. Keine Remotes, kein Push. Referenzprojekte und OSS-Quellen nur lesen.

## 0 · Markt und Kern

- [x] Marktvergleich, Galerie und Lightbox im Kern dokumentieren.
- [x] Verschachtelung in 5.7 und 6.0 prüfen und Markup-Verträge der npm-Pakete lesen.

## 1 · Gerüst

- [x] Drei eigenständige MIT-Bundles mit Manager-Plugins, Composer, README, CHANGELOG.
- [x] Docker / Contao 5.7 / PHP 8.3, Web 8101; Variante 6.0 / PHP 8.4.
- [x] Veröffentlichte npm-Assets gepinnt, Prüfsummen geprüft, Kopierskript, eingecheckt.
- [x] `make up/down/reset/check/e2e`; ECS, Twig-CS, Composer validate/normalize, Contao-Lints, PHPStan, PHPUnit, Playwright via vp.
- [x] Demo-Frontend für jede Phase; `make reset && make check` grün.

## 2 · Carousel

- [x] Verschachtelte beliebige Inhalte; Slides pro Ansicht je Breakpoint, Pfeile, Punkte, Autoplay mit Pause / Reduced Motion, Mausziehen.
- [x] Assets nur auf verwendenden Seiten.
- [x] Optionaler globaler Schalter für Kern-Swiper oder begründete Zurückstellung.
- [x] E2E: drei Inhalte, Pfeile, Tastatur, Wischen, ohne JS scrollbar, Axe, keine Swiper-/jQuery-Requests.

## 3 · Sheet

- [ ] Dialog / Sheet mit Kind-Elementen; unten, links, rechts, zentriert, Einrastpunkte, schließbar ja/nein.
- [ ] Button-Inhaltselement mit `commandfor`, ohne JS funktionsfähig.
- [ ] Offcanvas-Navigationsmodul mit Kern-Navigation und History-Plugin.
- [ ] E2E: Öffnen ohne JS, Escape, Hintergrund, Fokus-Rückgabe, mobile Navigation, Axe.

## 4 · Gallery

- [ ] Contao-Dateiauswahl, Sortierung, Bildgröße, Figure-Builder, responsive Bilder, Lazy-Loading, Metadaten.
- [ ] Raster, wechselnde Formate / Mauerwerk, Carousel-Leiste.
- [ ] Sheet-Lightbox: Vollbild, Tastatur und Wischen.
- [ ] Optionaler globaler Kern-Lightbox-Ersatz oder begründete Zurückstellung.
- [ ] E2E: acht Bilder, Öffnen, Blättern, Schließen, Axe.

## 5 · Release-fähig

- [ ] Paket-READMEs mit Installation, Positionierung, gzip-Tabelle, E2E-Screenshots, OSS-Quellen.
- [ ] GitHub Actions Matrix 5.7 / 6.0, Manager-ZIPs je Paket.
- [ ] Split-Vorbereitung dokumentiert; `.gitattributes` export-ignore.
- [ ] Backend Hell/Dunkel nativ; Smoke-Test Contao 6.0; finale Reset- und Check-Abnahme.

## Laufabschluss

- [ ] `docs/report.md` je Phase mit Abweichungen und offenen Fragen für Arne.
- [ ] `docs/goal-status.txt`: `DONE` nur wenn alle Phasen grün, sonst `NEXT: <nächster Schritt>`; mitcommitten.
