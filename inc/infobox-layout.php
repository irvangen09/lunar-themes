<?php
/**
 * Splits a Wiki Artikel's content into its Infobox and the rest.
 *
 * The theme presents Infobox as a right-hand sidebar next to the main
 * article body. Since editors always place it as the first block, this
 * pulls that first block out of the block list and renders it
 * separately from everything that follows, instead of relying on CSS
 * (float) to fake a two-column layout out of content that is really
 * just one long stream of blocks.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Builds the Infobox / content split for the current singular post.
 *
 * Mirrors the_content()'s reliance on the global $post — this is only
 * meant to be called from inside the main loop of a singular template,
 * the same place the_content() itself would normally be called.
 *
 * @return array{
 *     has_infobox: bool,
 *     infobox_html: string,
 *     content_html: string,
 * }
 */
function lunar_get_article_layout(): array {
	$post = get_post();

	if ( ! $post instanceof WP_Post ) {
		return array(
			'has_infobox'  => false,
			'infobox_html' => '',
			'content_html' => '',
		);
	}

	$blocks = parse_blocks( $post->post_content );

	// parse_blocks() can return a leading entry with blockName === null
	// for stray whitespace between block comments, so the first "real"
	// block isn't always at index 0.
	$first_key = null;

	foreach ( $blocks as $key => $block ) {
		if ( null !== $block['blockName'] ) {
			$first_key = $key;
			break;
		}
	}

	$has_infobox = null !== $first_key
		&& 'lunar-core/infobox' === $blocks[ $first_key ]['blockName'];

	if ( ! $has_infobox ) {
		return array(
			'has_infobox'  => false,
			'infobox_html' => '',
			'content_html' => apply_filters( 'the_content', $post->post_content ),
		);
	}

	$infobox_block = $blocks[ $first_key ];
	unset( $blocks[ $first_key ] );

	// Re-serializing the remaining blocks (instead of rendering each one
	// directly) means they still go through the normal the_content
	// pipeline below — dynamic blocks, embeds, and shortcodes keep
	// working exactly as they would if Infobox were never removed.
	$remaining_content = serialize_blocks( array_values( $blocks ) );

	return array(
		'has_infobox'  => true,
		'infobox_html' => render_block( $infobox_block ),
		'content_html' => apply_filters( 'the_content', $remaining_content ),
	);
}