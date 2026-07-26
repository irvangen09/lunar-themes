# Changelog

All notable changes to Lunar are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

Nothing yet.

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
