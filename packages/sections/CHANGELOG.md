# Changelog

## 0.1.1 – 2026-10-05

- Hero, page head, picture and text, person and logos offer Contao's picture size field. Left empty, the section keeps its own crop.

## 0.1.0 – 2026-10-02

- Twelve content elements for content pages: hero, page head, promises, picture and text, figures, features, steps, team with nested people (grid or carousel), logos, contact and closing tile.
- Plain editor fields with German and English labels; no CSS classes needed.
- Stylesheet loads once per page from the elements and styles only through the shared `--nw-*` tokens, with neutral fallbacks for any theme, light and dark.
- Contact e-mail fully entity-encoded, route link to OpenStreetMap, lazy pictures with an eager first picture in hero and page head.
