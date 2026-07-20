<?php
/**
 * Frontend asset loading — Google Fonts, design tokens, and the main
 * stylesheet. Assets are only ever loaded here, never inline in
 * template files, so there is a single place to check what is
 * being loaded.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Adds preconnect resource hints for Google Fonts.
 *
 * Skips a DNS lookup + TLS handshake round trip before the font
 * stylesheet request is even made.
 *
 * @param string[] $hints          Existing resource hint URLs.
 * @param string   $relation_type  Type of hint being filtered (e.g. 'preconnect').
 * @return string[]
 */
function lunar_resource_hints( array $hints, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $hints;
}
add_filter( 'wp_resource_hints', 'lunar_resource_hints', 10, 2 );

/**
 * Enqueues frontend assets in dependency order:
 * fonts -> design tokens -> main stylesheet (which relies on both).
 */
function lunar_enqueue_assets(): void {
	wp_enqueue_style(
		'lunar-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Lora:ital,wght@0,400;0,500;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'lunar-tokens',
		get_template_directory_uri() . '/assets/css/tokens.css',
		array( 'lunar-fonts' ),
		filemtime( get_template_directory() . '/assets/css/tokens.css' )
	);

	wp_enqueue_style(
		'lunar-layout',
		get_template_directory_uri() . '/assets/css/layout.css',
		array( 'lunar-tokens' ),
		filemtime( get_template_directory() . '/assets/css/layout.css' )
	);

	wp_enqueue_style(
		'lunar-style',
		get_stylesheet_uri(),
		array( 'lunar-layout' ),
		wp_get_theme()->get( 'Version' )
	);

	// The header nav (and its mobile toggle) appears on every page, so
	// this script is not gated behind a template conditional like the
	// page-specific stylesheets below.
	wp_enqueue_script(
		'lunar-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/navigation.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Page-specific stylesheets — only loaded on the template that needs them.
	if ( is_singular( 'wiki_artikel' ) ) {
		wp_enqueue_style(
			'lunar-single',
			get_template_directory_uri() . '/assets/css/single.css',
			array( 'lunar-style' ),
			filemtime( get_template_directory() . '/assets/css/single.css' )
		);
	}

	if ( is_front_page() ) {
		wp_enqueue_style(
			'lunar-homepage',
			get_template_directory_uri() . '/assets/css/homepage.css',
			array( 'lunar-style' ),
			filemtime( get_template_directory() . '/assets/css/homepage.css' )
		);
	}

	if ( is_tax( 'game' ) ) {
		wp_enqueue_style(
			'lunar-archive',
			get_template_directory_uri() . '/assets/css/archive.css',
			array( 'lunar-style' ),
			filemtime( get_template_directory() . '/assets/css/archive.css' )
		);
	}

	if ( is_search() ) {
		wp_enqueue_style(
			'lunar-search',
			get_template_directory_uri() . '/assets/css/search.css',
			array( 'lunar-style' ),
			filemtime( get_template_directory() . '/assets/css/search.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lunar_enqueue_assets' );