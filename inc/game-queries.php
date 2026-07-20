<?php
/**
 * Query helpers related to the Game taxonomy as a whole — distinct from
 * game-context.php, which only resolves the CURRENT request's context.
 * This file is for listing/browsing purposes (e.g. the homepage's
 * "Jelajahi Game" tile grid).
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Returns every "Specific Title" game term (child-level only — Franchise
 * parent terms are excluded), across all franchises, alphabetically.
 *
 * @return WP_Term[]
 */
function lunar_get_game_terms(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'game',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_array( $terms ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$terms,
			static function ( $term ) {
				return 0 !== (int) $term->parent;
			}
		)
	);
}

/**
 * Returns every Tipe Konten term actually used by posts under a given
 * Game term — never a hardcoded list, so a new game with an unusual
 * content type vocabulary (e.g. "Kostum" for a non-farming-sim title)
 * automatically gets its own pills without any code change.
 *
 * The 'count' property on each returned term reflects only posts within
 * this specific game term, not the site-wide count.
 *
 * @param int $game_term_id Term ID of the Game (Judul Spesifik or Franchise).
 * @return WP_Term[]
 */
function lunar_get_content_types_for_game( int $game_term_id ): array {
	$post_ids = get_objects_in_term( $game_term_id, 'game' );

	if ( is_wp_error( $post_ids ) || empty( $post_ids ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'tipe_konten',
			'object_ids' => $post_ids,
			'hide_empty' => false,
		)
	);

	return is_array( $terms ) ? $terms : array();
}

/**
 * Returns the destination URL for a Game Tile — a custom URL if one is
 * set on the term (e.g. pointing to a Pillar Post instead of the game
 * archive), otherwise the same default archive link used before this
 * option existed.
 *
 * @param \WP_Term $term A Game term (Judul Spesifik level).
 * @return string
 */
function lunar_get_game_tile_url( \WP_Term $term ): string {
	$custom_url = (string) get_term_meta( $term->term_id, 'lunar_core_game_tile_url', true );

	if ( '' !== $custom_url ) {
		return $custom_url;
	}

	$term_link = get_term_link( $term );

	return is_wp_error( $term_link ) ? '' : $term_link;
}

/**
 * Returns the attachment ID for a Game Tile's custom media, or 0 if
 * none is set — the caller falls back to the default initials
 * placeholder in that case (see front-page.php).
 *
 * @param \WP_Term $term A Game term (Judul Spesifik level).
 * @return int
 */
function lunar_get_game_tile_image_id( \WP_Term $term ): int {
	return (int) get_term_meta( $term->term_id, 'lunar_core_game_tile_image_id', true );
}