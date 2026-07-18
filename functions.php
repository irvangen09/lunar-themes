<?php
/**
 * Theme bootstrap. Intentionally holds no logic of its own — only
 * requires files from inc/, each with a single responsibility.
 *
 * More inc/ files will be added here as later template work introduces
 * them (e.g. template-tags.php for breadcrumb helpers, taxonomy-related
 * helpers, etc.) — each require added on its own line, never merged
 * into an existing file that already has a clear purpose.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

require get_template_directory() . '/inc/theme-setup.php';
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/game-context.php';
require get_template_directory() . '/inc/game-queries.php';
require get_template_directory() . '/inc/breadcrumb.php';
require get_template_directory() . '/inc/search-filters.php';
