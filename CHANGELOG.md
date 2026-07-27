# Changelog

All notable changes to Lunar are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

Nothing yet.

## [0.1.1] - 2026-07-27

### Fixed

- The 404 page's search link no longer resolves to a broken URL.
- Clicking a Tipe Konten filter pill no longer risks losing other active filters — the canonical-redirect cancellation now only cancels the one specific redirect it needs to, instead of disabling WordPress's canonical redirect handling for the whole request.
- Homepage, Search results, and Author archive pages now each have a proper top-level heading, improving screen reader and search engine support.
- Site and footer navigation are now labeled for assistive technology so they can be told apart.
- The navigation dropdown's expanded/collapsed state is now announced correctly to screen readers on desktop, not only on the mobile accordion.

### Changed

- The theme no longer references LunarCore's internal classes directly; it now calls a small set of public functions the plugin exposes instead (pairs with LunarCore 0.3.0).
- Minor internal cleanup: removed an unused CSS utility class, consolidated duplicated filter logic in the Search template, removed a no-op breadcrumb call on Pages, normalized inconsistent line endings, and translated a leftover English string.

## [0.1.0] - 2026-07-26

First tagged release.

### Added

- Homepage (static front page) with a game tile grid — each title supports an optional custom image and destination URL — plus a latest-articles feed.
- Header navigation with a desktop dropdown for per-game secondary menus and an off-canvas menu on mobile.
- Archive per Game template with a dynamic Content Type filter (built from whichever content types actually exist for that game, not a fixed list) and optional custom header media.
- Wiki Article template with a two-column layout, sticky Infobox sidebar, table of contents support, byline, and author box.
- Native WordPress Post template with category/tag display and a related-posts section.
- Native WordPress Page template with styled core Gutenberg blocks (headings, lists, quotes, tables, buttons, images, and more) for static pages like About or Contact.
- Search results template combining keyword search, a Game checkbox filter, a Content Type filter, and contextual filters for structured fields (e.g. tool tier, season) that only appear when the current results actually have data for them.
- Author archive template.
- 404 template.
- "Cozy Almanac" design system: color tokens, typography (Fraunces / Lora / IBM Plex Mono), spacing, and border-radius scale.

### Known limitations

- Comments are not implemented yet.