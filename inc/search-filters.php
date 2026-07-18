<?php
/**
 * Modifies the main search query: restricts results to Wiki Artikel
 * only (Pages and any other post type are excluded from search results),
 * and applies the "Game Title" checkbox filter.
 *
 * The Tipe Konten pill filter needs no custom code here — it relies on
 * WordPress's native support for combining a registered taxonomy query
 * var into the main query automatically (same mechanism used on the
 * Game archive page).
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
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
