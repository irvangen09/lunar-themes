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
