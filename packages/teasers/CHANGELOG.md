# Changelog

## 0.1.1 – 2026-10-05

- The element and the module show only the filters of the chosen source: news categories with the Codefog extension, stars for testimonials.
- Plain help texts, compact two-column form, and the list name is optional (falls back to the headline).

## 0.1.0 – 2026-10-02

- Apply Contao news/calendar archive permissions to backend choices.
- Reject source keys exceeding the DCA's 64-character capacity and document a complete third-party source service.
- Replace unrelated component integration documentation with the source, renderer and card contracts.
- Add Twig content element and frontend module for source-driven grid, list and carousel cards.
- Introduce the `nordwerk.teaser_source` service contract, immutable cards and bounded queries.
- Add news archive/category filtering and permission-aware publication checks.
- Use Contao's calendar generator for upcoming event occurrences and recurrences.
- Render public Contao Studio images; escape plain text and restrict link schemes.
- Reuse the existing native Carousel component without changing its bundle.
