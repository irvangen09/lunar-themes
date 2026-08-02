<?php
/**
 * Modifies the main search query: restricts results to Wiki Article
 * only (Pages and any other post type are excluded from search results),
 * applies the "Game Title" checkbox filter, and applies the contextual
 * field-sync filters (Tier Alat, Peran, etc.).
 *
 * The Tipe Konten pill filter needs no custom code here — it relies on
 * WordPress's native support for combining a registered taxonomy query
 * var into the main query automatically (same mechanism used on the
 * Game archive page).
 *
 * The field-sync filters are intentionally data-driven rather than
 * mapped per Tipe Konten anywhere in code: which fields are offered
 * depends entirely on which of them actually have a non-empty value
 * among the posts matching the currently active filters. This keeps
 * the filter set correct automatically as new content types and
 * fields are introduced, with no lookup table to maintain.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Human-readable label for a recognized field-sync field — resolved
 * from the actual admin-defined term name in the Field taxonomy
 * (via the companion plugin's public API), not a locally maintained
 * dictionary. This avoids repeating the exact "dictionary that forgets
 * to stay in sync" problem this refactor just removed on the plugin
 * side: a label map here would need updating every time a field is
 * added, renamed, or removed in wp-admin, with nothing enforcing it.
 *
 * @param string $field Field slug (e.g. 'role').
 * @return string
 */
function lunar_get_field_label( string $field ): string {
	if ( function_exists( 'lunar_core_get_field_label' ) ) {
		return lunar_core_get_field_label( $field );
	}

	return ucwords( str_replace( array( '_', '-' ), ' ', $field ) );
}

/**
 * Sanitized, non-empty list of game term slugs selected via the
 * "games[]" checkboxes on the Search form, if any.
 *
 * Shared by lunar_filter_search_by_game() (applied to the main search
 * query) and lunar_get_active_field_filters() (which runs its own,
 * separate query) so both always agree on what "selected games"
 * means, instead of sanitizing/filtering $_GET['games'] independently
 * in two places that could drift out of sync.
 *
 * @return string[]
 */
function lunar_get_selected_game_slugs(): array {
	if ( ! isset( $_GET['games'] ) ) {
		return array();
	}

	return array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET['games'] ) ) );
}

/**
 * Computes which recognized field-sync fields (and which distinct
 * values of each) actually have data among the posts matching the
 * currently active search term, Game filter, and Tipe Konten filter —
 * excluding the field filters themselves, so this never depends on
 * its own output.
 *
 * @return array<string, string[]> Field slug => list of distinct values.
 */
function lunar_get_active_field_filters(): array {
	if ( ! function_exists( 'lunar_core_get_recognized_fields' ) || ! function_exists( 'lunar_core_get_post_type_slug' ) ) {
		return array();
	}

	$args = array(
		'post_type'      => lunar_core_get_post_type_slug(),
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	);

	$search_term = get_search_query();
	if ( '' !== $search_term ) {
		$args['s'] = $search_term;
	}

	$tax_query = array();

	$content_type_slug = function_exists( 'lunar_core_get_taxonomy_slug_content_type' )
		? lunar_core_get_taxonomy_slug_content_type()
		: '';
	$active_tipe        = $content_type_slug ? sanitize_title( (string) get_query_var( $content_type_slug ) ) : '';

	if ( '' !== $active_tipe ) {
		$tax_query[] = array(
			'taxonomy' => $content_type_slug,
			'field'    => 'slug',
			'terms'    => $active_tipe,
		);
	}

	if ( isset( $_GET['games'] ) ) {
		$selected_games = lunar_get_selected_game_slugs();

		if ( ! empty( $selected_games ) ) {
			$tax_query[] = array(
				'taxonomy' => 'game',
				'field'    => 'slug',
				'terms'    => $selected_games,
			);
		}
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$matching_ids = get_posts( $args );

	if ( empty( $matching_ids ) ) {
		return array();
	}

	global $wpdb;

	$placeholders   = implode( ', ', array_fill( 0, count( $matching_ids ), '%d' ) );
	$active_filters = array();

	foreach ( lunar_core_get_recognized_fields() as $field ) {
		$meta_key = lunar_core_get_field_meta_key( $field );

		if ( null === $meta_key ) {
			continue;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $placeholders is a fixed set of %d tokens, not raw input.
		$query = $wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' AND post_id IN ( {$placeholders} )",
			array_merge( array( $meta_key ), $matching_ids )
		);

		$values = $wpdb->get_col( $query );

		if ( empty( $values ) ) {
			continue;
		}

		// The slug check below assumes WordPress's default term slug
		// sanitization for a term named "Tier Alat" (spaces become
		// hyphens: "tier-alat"). If this field is ever recreated in
		// wp-admin with a manually edited slug that doesn't match, this
		// custom ordering silently falls back to plain alphabetical sort
		// below — it fails gracefully, not with an error, but the tier
		// progression display order would then need this string updated
		// to match whatever slug was actually assigned.
		if ( 'tier-alat' === $field ) {
			$tier_order = array( 'Kayu', 'Perunggu', 'Perak', 'Emas', 'Mystrile' );
			usort(
				$values,
				static function ( $a, $b ) use ( $tier_order ) {
					return array_search( $a, $tier_order, true ) <=> array_search( $b, $tier_order, true );
				}
			);
		} else {
			sort( $values );
		}

		$active_filters[ $field ] = $values;
	}

	return $active_filters;
}

/**
 * Restricts the main search query to the Wiki Article post type.
 *
 * @param WP_Query $query The query being filtered.
 */
function lunar_restrict_search_post_type( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	if ( ! function_exists( 'lunar_core_get_post_type_slug' ) ) {
		return; // Plugin inactive -- leave WordPress's default search post types alone.
	}

	$query->set( 'post_type', lunar_core_get_post_type_slug() );
}
add_action( 'pre_get_posts', 'lunar_restrict_search_post_type' );

/**
 * Applies the "Game Title" checkbox filter (multiple games can be
 * selected at once) to the main search query. Uses a dedicated
 * "games[]" request parameter rather than the "game" taxonomy's own
 * query var, since that one is built for a single term slug (as used
 * on the Game archive page), not a multi-value array.
 *
 * @param WP_Query $query The query being filtered.
 */
function lunar_filter_search_by_game( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$selected_games = lunar_get_selected_game_slugs();

	if ( empty( $selected_games ) ) {
		return;
	}

	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'game',
		'field'    => 'slug',
		'terms'    => $selected_games,
	);

	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'lunar_filter_search_by_game' );

/**
 * Applies the selected contextual field-sync filters (e.g. "Tier Alat")
 * to the main search query, using a "fields[field_slug][]" request
 * parameter per field. Only field slugs LunarCore actually recognizes
 * are honored — anything else is silently ignored, since a forged or
 * outdated field name has no corresponding meta key to filter on.
 *
 * @param WP_Query $query The query being filtered.
 */
function lunar_filter_search_by_fields( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	if ( ! isset( $_GET['fields'] ) || ! is_array( $_GET['fields'] ) ) {
		return;
	}

	if ( ! function_exists( 'lunar_core_get_recognized_fields' ) ) {
		return;
	}

	$recognized_fields  = lunar_core_get_recognized_fields();
	$submitted_fields   = wp_unslash( $_GET['fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$meta_query         = (array) $query->get( 'meta_query' );
	$applied_any_filter = false;

	foreach ( $submitted_fields as $field => $values ) {
		$field = sanitize_key( $field );

		if ( ! in_array( $field, $recognized_fields, true ) ) {
			continue;
		}

		$meta_key = lunar_core_get_field_meta_key( $field );

		if ( null === $meta_key ) {
			continue;
		}

		$values = array_filter( array_map( 'sanitize_text_field', (array) $values ) );

		if ( empty( $values ) ) {
			continue;
		}

		$meta_query[]       = array(
			'key'     => $meta_key,
			'value'   => $values,
			'compare' => 'IN',
		);
		$applied_any_filter = true;
	}

	if ( ! $applied_any_filter ) {
		return;
	}

	if ( count( $meta_query ) > 1 && ! isset( $meta_query['relation'] ) ) {
		$meta_query['relation'] = 'AND';
	}

	$query->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'lunar_filter_search_by_fields' );

/**
 * Cancels WordPress's automatic canonical redirect specifically when it
 * would turn a Tipe Konten pill click (on the Search page or the Game
 * archive page) into a request for the plain Content Type taxonomy
 * archive URL (e.g. /content-type/karakter/).
 *
 * That archive was intentionally never built (a cross-game Tipe Konten
 * listing mixes unrelated content and was cancelled early on), so
 * letting the redirect through would send visitors to a generic,
 * unstyled template — and worse, silently discard every other active
 * filter (search term, Game checkboxes, field-sync checkboxes, or the
 * Game archive context itself) in the process.
 *
 * This hooks the redirect_canonical filter rather than removing the
 * redirect_canonical action outright: redirect_canonical() still runs
 * its full logic for every other reason it might redirect a request
 * (trailing slash, wrong case, etc.) — only the one specific,
 * already-computed redirect toward the Content Type archive is
 * cancelled, and only when it's actually that redirect.
 *
 * Checking for the content type slug as a $_GET key (query-string form)
 * rather than requiring "s" to also be present is deliberate and still
 * safe: a genuine visit to the pretty-permalink archive URL never
 * populates $_GET with it — rewrite rules resolve straight to query
 * vars — so this only ever matches links our own pill filters generate.
 *
 * @param string|false $redirect_url  The redirect target WordPress computed, or false.
 * @param string       $requested_url The originally requested URL.
 * @return string|false
 */
function lunar_prevent_search_canonical_redirect( $redirect_url, string $requested_url ) {
	if ( false === $redirect_url || ! function_exists( 'lunar_core_get_taxonomy_slug_content_type' ) ) {
		return $redirect_url;
	}

	$content_type_slug = lunar_core_get_taxonomy_slug_content_type();

	if ( ! isset( $_GET[ $content_type_slug ] ) ) {
		return $redirect_url;
	}

	$content_type_tax = get_taxonomy( $content_type_slug );

	if ( ! ( $content_type_tax instanceof WP_Taxonomy ) ) {
		return $redirect_url;
	}

	$archive_slug = ( is_array( $content_type_tax->rewrite ) && ! empty( $content_type_tax->rewrite['slug'] ) )
		? $content_type_tax->rewrite['slug']
		: $content_type_tax->name;

	$redirect_path = (string) wp_parse_url( $redirect_url, PHP_URL_PATH );

	if ( false === strpos( $redirect_path, '/' . $archive_slug . '/' ) ) {
		return $redirect_url;
	}

	return false;
}
add_filter( 'redirect_canonical', 'lunar_prevent_search_canonical_redirect', 10, 2 );

/**
 * Redirects a direct visit to the plain Content Type taxonomy archive
 * (e.g. /content-type/karakter/) to the Search page with that Tipe
 * Konten filter already active, instead of letting it fall through to
 * the generic, unstyled archive template.
 *
 * A cross-game Tipe Konten listing was intentionally never built (it
 * would mix unrelated game content together), but the taxonomy is
 * still public and its archive URL is still reachable — this keeps
 * that URL useful instead of a dead end.
 *
 * Only fires when NOT already arriving via Search: a pill click from
 * the Search page always carries "s" in the query string, which makes
 * is_search() true and takes priority over is_tax() in the Template
 * Hierarchy — so that case reaches search.php on its own and is left
 * alone here.
 */
function lunar_redirect_content_type_archive(): void {
	if ( ! function_exists( 'lunar_core_get_taxonomy_slug_content_type' ) ) {
		return;
	}

	$content_type_slug = lunar_core_get_taxonomy_slug_content_type();

	if ( is_admin() || is_search() || ! is_tax( $content_type_slug ) ) {
		return;
	}

	$term = get_queried_object();

	if ( ! ( $term instanceof WP_Term ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/?s=&' . $content_type_slug . '=' . rawurlencode( $term->slug ) ), 301 );
	exit;
}
add_action( 'template_redirect', 'lunar_redirect_content_type_archive' );

/**
 * Forces a proper 404 for any URL starting with the Field taxonomy's
 * slug (e.g. /wiki_field/role/), instead of whatever WordPress happens
 * to fall back to when a path segment doesn't match any registered
 * rewrite rule.
 *
 * The Field taxonomy is intentionally registered without a public
 * rewrite rule at all (it's an internal-only taxonomy, see the
 * companion plugin's Taxonomies class) — so this isn't a redirect away
 * from a real archive like the Content Type guard above, it's purely a
 * defensive net for a URL pattern that was never meant to resolve to
 * anything. Checking the raw request path directly (rather than
 * relying on is_tax()/get_query_var(), which this unregistered rewrite
 * never populates) is what makes this reliable regardless of how
 * WordPress's default routing happens to handle the unmatched path.
 */
function lunar_block_field_taxonomy_urls(): void {
	if ( is_admin() || ! function_exists( 'lunar_core_get_taxonomy_slug_field' ) ) {
		return;
	}

	$field_slug = lunar_core_get_taxonomy_slug_field();

	$home_path = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	$home_path = '/' . trim( $home_path, '/' );
	if ( '/' === $home_path ) {
		$home_path = '';
	}

	$request_path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
	$request_path = rtrim( (string) $request_path, '/' );

	$field_prefix = $home_path . '/' . $field_slug;

	if ( $request_path !== $field_prefix && 0 !== strpos( $request_path, $field_prefix . '/' ) ) {
		return;
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
add_action( 'template_redirect', 'lunar_block_field_taxonomy_urls' );