<?php
/**
 * By default, WordPress's Author Archive query only includes the
 * native 'post' post type — a custom post type like wiki_artikel has
 * to be added explicitly, or it silently shows zero results no matter
 * how many articles that author has actually written.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

add_action( 'pre_get_posts', 'lunar_include_wiki_artikel_in_author_archive' );

/**
 * Adds wiki_artikel alongside the native post type on the Author
 * Archive's main query only — every other query (admin lists, other
 * templates, custom WP_Query calls elsewhere) is left untouched.
 *
 * @param WP_Query $query The query being filtered.
 */
function lunar_include_wiki_artikel_in_author_archive( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() ) {
		return;
	}

	$query->set( 'post_type', array( 'post', 'wiki_artikel' ) );
}