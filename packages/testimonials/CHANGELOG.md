# Changelog

## 0.1.1 – 2026-10-05

- Help texts for every field of the form module, the testimonial and the archive.

## 0.1.0 – 2026-10-02

- Bind consent evidence to the issued token; retain base paths and query strings on POST/redirect.
- Generate unique field IDs for repeated module renders while preserving the submission identifier.
- Limit submissions across sessions/modules with a server-side, locked sliding window and daily HMAC client pseudonyms.
- Resolve Oveleon local picker UUID tags and root-relative file paths through the public file guard.
- Document the standalone form and teaser integration contracts and limiter storage requirements.
- Add versioned archives and testimonial records with optional ratings and public review policies.
- Add a Twig submission module requiring consent, server validation and Contao CSRF protection.
- Keep submissions unpublished and require backend review notes before publication.
- Persist consent evidence and retryable operator notification state without logging IP addresses.
- Register the testimonials teaser source without exposing private fields or review schema markup.
- Add a dry-run-first, transactional and idempotent Oveleon recommendation importer.
