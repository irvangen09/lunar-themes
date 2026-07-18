<?php
/**
 * Helper functions to determine the current "game context" (which
 * specific game title, if any, the current request belongs to) and to
 * resolve that game's secondary navigation menu.
 *
 * Reads the taxonomy slug and term meta key as plain string literals —
 * treated as a stable public data contract, not a dependency on the
 * companion plugin's internal PHP classes.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Returns the specific game term (child-level, e.g. "Friends of Mineral
 * Town") relevant to the current request, or null outside of any game
 * context (e.g. homepage, search results, a franchise-level archive).
 *
 * @return WP_Term|null
 */
function lunar_get_current_game_term(): ?WP_Term {
	if ( is_singular( 'wiki_artikel' ) ) {
		$terms = get_the_terms( get_the_ID(), 'game' );

		if ( ! is_array( $terms ) ) {
			return null;
		}

		foreach ( $terms as $term ) {
			if ( 0 !== (int) $term->parent ) {
				return $term;
			}
		}

		return null;
	}

	if ( is_tax( 'game' ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && 0 !== (int) $term->parent ) {
			return $term;
		}
	}

	return null;
}

/**
 * Returns the WordPress menu ID assigned to the current game term, or
 * null if there is no game context, no menu was assigned, or the
 * assigned menu no longer exists.
 *
 * @return int|null
 */
function lunar_get_game_secondary_menu_id(): ?int {
	$term = lunar_get_current_game_term();

	if ( ! $term ) {
		return null;
	}

	$menu_id = (int) get_term_meta( $term->term_id, 'lunar_core_secondary_menu_id', true );

	if ( $menu_id <= 0 || ! wp_get_nav_menu_object( $menu_id ) ) {
		return null;
	}

	return $menu_id;
}
