<?php
/**
 * Modifies the main search query: restricts results to Wiki Artikel
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
 * Human-readable label for a recognized field-sync field. Falls back to
 * a generic title-cased label for any field not explicitly listed here,
 * so a newly recognized field still displays reasonably without a
 * required update to this map.
 *
 * @param string $field Field slug (e.g. 'tier_alat').
 * @return string
 */
function lunar_get_field_label( string $field ): string {
	$labels = array(
		'peran'        => __( 'Peran', 'lunar' ),
		'tier_alat'    => __( 'Tier Alat', 'lunar' ),
		'musim'        => __( 'Musim', 'lunar' ),
		'waktu_muncul' => __( 'Waktu Muncul', 'lunar' ),
		'jenis_hasil'  => __( 'Jenis Hasil', 'lunar' ),
	);

	return $labels[ $field ] ?? ucwords( str_replace( '_', ' ', $field ) );
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
	if ( ! class_exists( '\Lunar\Content\Meta_Fields' ) ) {
		return array();
	}

	$args = array(
		'post_type'      => 'wiki_artikel',
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

	$active_tipe = sanitize_title( (string) get_query_var( 'tipe_konten' ) );
	if ( '' !== $active_tipe ) {
		$tax_query[] = array(
			'taxonomy' => 'tipe_konten',
			'field'    => 'slug',
			'terms'    => $active_tipe,
		);
	}

	if ( isset( $_GET['games'] ) ) {
		$selected_games = array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET['games'] ) ) );

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

	foreach ( \Lunar\Content\Meta_Fields::get_recognized_fields() as $field ) {
		$meta_key = \Lunar\Content\Meta_Fields::get_meta_key( $field );

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

		if ( 'tier_alat' === $field ) {
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
 * Restricts the main search query to the Wiki Artikel post type.
 *
 * @param WP_Query $query The query being filtered.
 */
function lunar_restrict_search_post_type( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', 'wiki_artikel' );
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

	if ( ! isset( $_GET['games'] ) ) {
		return;
	}

	$selected_games = array_map( 'sanitize_title', (array) wp_unslash( $_GET['games'] ) );
	$selected_games = array_filter( $selected_games );

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

	if ( ! class_exists( '\Lunar\Content\Meta_Fields' ) ) {
		return;
	}

	$recognized_fields  = \Lunar\Content\Meta_Fields::get_recognized_fields();
	$submitted_fields   = wp_unslash( $_GET['fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$meta_query         = (array) $query->get( 'meta_query' );
	$applied_any_filter = false;

	foreach ( $submitted_fields as $field => $values ) {
		$field = sanitize_key( $field );

		if ( ! in_array( $field, $recognized_fields, true ) ) {
			continue;
		}

		$meta_key = \Lunar\Content\Meta_Fields::get_meta_key( $field );

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
 * Prevents WordPress's automatic redirect_canonical from turning a
 * Tipe Konten pill click (on the Search page or the Game archive page)
 * into a request for the plain Tipe Konten taxonomy archive URL
 * (e.g. /tipe-konten/karakter/).
 *
 * That archive was intentionally never built (a cross-game Tipe Konten
 * listing mixes unrelated content and was cancelled early on), so it
 * falls back to a generic, unstyled template — and worse, the redirect
 * silently discards every other active filter (search term, Game
 * checkboxes, field-sync checkboxes, or the Game archive context
 * itself) in the process.
 *
 * Checking for "tipe_konten" as a $_GET key (query-string form) rather
 * than requiring "s" to also be present is deliberate and still safe:
 * a genuine visit to the pretty-permalink archive URL never populates
 * $_GET with it — rewrite rules resolve straight to query vars — so
 * this only ever matches links our own pill filters generate.
 */
function lunar_prevent_search_canonical_redirect(): void {
	if ( isset( $_GET['tipe_konten'] ) ) {
		remove_action( 'template_redirect', 'redirect_canonical' );
	}
}
add_action( 'template_redirect', 'lunar_prevent_search_canonical_redirect', 0 );

/**
 * Redirects a direct visit to the plain Tipe Konten taxonomy archive
 * (e.g. /tipe-konten/karakter/) to the Search page with that Tipe
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
function lunar_redirect_tipe_konten_archive(): void {
	if ( is_admin() || is_search() || ! is_tax( 'tipe_konten' ) ) {
		return;
	}

	$term = get_queried_object();

	if ( ! ( $term instanceof WP_Term ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/?s=&tipe_konten=' . rawurlencode( $term->slug ) ), 301 );
	exit;
}
add_action( 'template_redirect', 'lunar_redirect_tipe_konten_archive' );