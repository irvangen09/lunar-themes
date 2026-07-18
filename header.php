<?php
/**
 * Site header — logo, the single context-aware nav slot (static "Semua
 * Game" link by default, swapped for the current game's own menu when
 * inside a game context), and the search icon.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

$lunar_secondary_menu_id = lunar_get_game_secondary_menu_id();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="lunar-skip-link screen-reader-text" href="#main-content">
	<?php esc_html_e( 'Skip to content', 'lunar' ); ?>
</a>

<div id="page" class="lunar-site-wrapper">

	<header class="lunar-site-header">
		<a class="lunar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="lunar-brand__mark" aria-hidden="true">&#9789;</span>
			<?php bloginfo( 'name' ); ?>
		</a>

		<?php if ( $lunar_secondary_menu_id ) : ?>

			<?php
			wp_nav_menu(
				array(
					'menu'            => $lunar_secondary_menu_id,
					'container'       => 'nav',
					'container_class' => 'lunar-site-nav',
					'menu_class'      => 'lunar-site-nav__list',
					'fallback_cb'     => false,
				)
			);
			?>

		<?php else : ?>

			<nav class="lunar-site-nav">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Semua Game', 'lunar' ); ?>
				</a>
			</nav>

		<?php endif; ?>

		<a class="lunar-search-icon" href="<?php echo esc_url( get_search_link() ); ?>" aria-label="<?php esc_attr_e( 'Search', 'lunar' ); ?>">
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="11" cy="11" r="7"></circle>
				<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
			</svg>
		</a>
	</header>
