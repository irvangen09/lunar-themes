# Contributing to Lunar

Thanks for your interest in Lunar. This theme is primarily built for one specific documentation website and is currently maintained by a single person, so response times on issues and pull requests may vary. That said, contributions that fit the project's direction are welcome.

## Before you start

Lunar and its companion plugin, [LunarCore](https://github.com/irvangen09/lunar-core), have a clear division of responsibility:

- **Lunar** (this repo) — templates, layout, styling. No stored data, no Gutenberg blocks, no business logic.
- **LunarCore** — the Wiki Article post type, taxonomies, Gutenberg blocks, and metadata.

If you're not sure whether something belongs in the theme or the plugin, open an issue first before writing code — it saves everyone a rewritten pull request.

## Reporting bugs

Open an issue and include:

- WordPress, PHP, and Lunar (and LunarCore, if relevant) versions
- Steps to reproduce
- Expected vs. actual behavior
- Screenshots, if it's a visual issue

If the bug is about stored data, taxonomies, or block behavior rather than layout or styling, it likely belongs in the [LunarCore repository](https://github.com/irvangen09/lunar-core) instead.

## Proposing features

Open an issue describing the actual use case, not just the feature itself. Some things are intentionally out of scope right now — a cross-game content-type archive, for example, was considered and rejected because it mixes unrelated games together in a confusing way. A concrete use case helps avoid re-proposing things that were already deliberately left out.

## Coding conventions

- PHP 8.0+, following the [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).
- All comments, docblocks, and commit messages in English.
- One stylesheet per template/concern under `assets/css/`, enqueued conditionally in `inc/enqueue.php` — a page should never load CSS it doesn't use.
- Native WordPress Posts, Pages, and Wiki Articles each have their own template file rather than sharing one with conditional branches inside it — keep that separation when adding new templates.
- Escape all output (`esc_html()`, `esc_url()`, `esc_attr()`, etc.) and verify nonces on anything that writes data.
- No JavaScript build step is required for this theme — `assets/js/` is loaded as-is.

## Submitting a pull request

1. Fork the repository and branch from `main`.
2. Keep the change scoped to what the pull request describes — unrelated cleanup should be its own PR.
3. Describe which templates or styles are affected, and include screenshots for anything visual.
4. If your change affects how existing content renders (e.g. altering shared classes also used by LunarCore blocks), call that out explicitly.

## Code of conduct

Be respectful and constructive in issues and pull requests. Disagree with ideas, not people.
