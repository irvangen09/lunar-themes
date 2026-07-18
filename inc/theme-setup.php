<?php
/**
 * Core theme setup — theme supports, nav menu locations, content width,
 * and text domain loading. Kept separate from functions.php so that
 * file only holds require statements.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Registers theme supports and nav menu locations.
 *
 * Runs on 'after_setup_theme' as required by WordPress for most
 * add_theme_support() calls and translations to load correctly.
 */
function lunar_theme_setup(): void {
	load_theme_textdomain( 'lunar', get_template_directory() . '/languages' );

	// Let WordPress manage the <title> tag instead of hardcoding it in header.php.
	add_theme_support( 'title-tag' );

	// Featured images — used for the article's main image and Infobox media.
	add_theme_support( 'post-thumbnails' );

	// Cleaner markup for built-in template parts we may end up using.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Represent the frontend as closely as possible inside the block editor.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/tokens.css' );

	// Allow wide/full alignment for core blocks used inside Wiki Artikel content.
	add_theme_support( 'align-wide' );

	// Responsive embeds (e.g. YouTube gameplay clips referenced in articles).
	add_theme_support( 'responsive-embeds' );

	// Native Pages don't support Excerpt by default. Enabled here so the
	// Homepage (a static Page) can have an editable hero description,
	// independent of the Page's main content blocks.
	add_post_type_support( 'page', 'excerpt' );

	// No nav menu location is registered here. The site header renders
	// either a static link or a specific menu resolved by ID (see
	// inc/game-context.php + header.php) — there is no fixed "primary"
	// location for an admin to manually assign a menu to.

	// The footer links (About, Contact, Privacy, etc.) ARE a normal,
	// admin-managed menu location — unlike the header nav, there is no
	// per-context logic involved here.
	register_nav_menus(
		array(
			'footer' => __( 'Footer Menu', 'lunar' ),
		)
	);
}
add_action( 'after_setup_theme', 'lunar_theme_setup' );

/**
 * Sets a sensible default content width for oEmbeds and media sizing.
 * Classic-theme convention — has no visual effect on its own, only
 * affects max sizes WordPress assumes when embedding external content.
 */
function lunar_content_width(): void {
	$GLOBALS['content_width'] = apply_filters( 'lunar_content_width', 700 );
}
add_action( 'after_setup_theme', 'lunar_content_width', 0 );
