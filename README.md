# Lunar

Lunar is a WordPress theme built for game documentation wikis — clean, content-first, and inspired by the visual language of in-game item rarity systems rather than generic encyclopedia sites.

Lunar handles presentation only: templates, layout, and styling. All content structure — the Wiki Article post type, the Game and Content Type taxonomies, Gutenberg blocks, and metadata — is provided by the companion plugin, [LunarCore](https://github.com/irvangen09/lunar-core). Lunar will not do much on its own without it.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- [LunarCore](https://github.com/irvangen09/lunar-core) plugin, active

## Features

- **Game archive** — per-title article listing with a dynamic Content Type filter and optional custom banner/icon media.
- **Homepage** — a game tile grid (with optional custom artwork and destination URL per title) and a latest-articles feed.
- **Wiki Article template** — a two-column layout with a sticky Infobox sidebar, table of contents support, byline, and author box.
- **Search** — keyword search combined with a Game checkbox filter, a Content Type filter, and contextual filters for whichever structured fields (e.g. tool tier, season) actually have data for the current results.
- **Native WordPress Posts and Pages** — separate templates so a regular blog post or a static page (About, Contact, etc.) doesn't inherit wiki-specific layout it doesn't need.
- Responsive navigation with a desktop dropdown and an off-canvas mobile menu.

## Structure

```
lunar/
├── style.css                  Theme header + base reset
├── functions.php               Bootstrap: requires everything under inc/
├── front-page.php              Homepage (static front page)
├── single-wiki_artikel.php     Wiki Article
├── single.php                  Native WordPress Post
├── page.php                    Native WordPress Page
├── taxonomy-game.php           Archive per Game
├── search.php                  Search results
├── author.php                  Author archive
├── 404.php
├── header.php / footer.php
├── inc/                         Template helpers, hooked logic (breadcrumbs, queries, search filtering, asset loading)
└── assets/
    ├── css/                     One stylesheet per template/concern, loaded conditionally
    └── js/                      Navigation behavior (dropdown + off-canvas menu)
```

Stylesheets are split by page context and only enqueued where they're actually needed — see `inc/enqueue.php`.

## Design system

Lunar's visual identity ("Cozy Almanac") uses a wheat-linen and terracotta palette, with Fraunces for display type, Lora for body copy, and IBM Plex Mono for tabular/technical text. Design tokens (colors, radii, fonts) live in `assets/css/tokens.css`.

## Status

Lunar is under active development and not yet feature-complete (see [CHANGELOG.md](CHANGELOG.md)). Comments are intentionally not implemented yet. Expect breaking changes between minor versions until a 1.0 release.

## License

GPL-2.0-or-later, consistent with WordPress's own licensing. See the license header in `style.css`.
