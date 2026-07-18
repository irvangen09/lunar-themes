<?php
/**
 * Breadcrumb helper. Currently only handles the single Wiki Artikel
 * context (Beranda > Franchise > Judul Spesifik > Tipe Konten > Judul
 * Artikel). Other contexts (Archive per Game, Search) will extend this
 * same function when those templates are built.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Outputs the breadcrumb trail for the current request.
 */
function lunar_breadcrumb(): void {
	if ( ! is_singular( 'wiki_artikel' ) ) {
		return;
	}

	$crumbs = array(
		array(
			'label' => __( 'Beranda', 'lunar' ),
			'url'   => home_url( '/' ),
		),
	);

	$game_term = lunar_get_current_game_term();

	if ( $game_term ) {
		if ( 0 !== (int) $game_term->parent ) {
			$franchise = get_term( $game_term->parent, 'game' );

			if ( $franchise && ! is_wp_error( $franchise ) ) {
				$crumbs[] = array(
					'label' => $franchise->name,
					'url'   => get_term_link( $franchise ),
				);
			}
		}

		$game_url = get_term_link( $game_term );

		$crumbs[] = array(
			'label' => $game_term->name,
			'url'   => is_wp_error( $game_url ) ? '' : $game_url,
		);
	}

	$tipe_konten_terms = get_the_terms( get_the_ID(), 'tipe_konten' );

	if ( is_array( $tipe_konten_terms ) && ! empty( $tipe_konten_terms ) ) {
		$tipe_konten = $tipe_konten_terms[0];
		$tipe_konten_url = '';

		// Links to the game's archive page pre-filtered by this content
		// type. The filtering itself is wired up when archive.php is built.
		if ( $game_term && ! is_wp_error( $game_url ) ) {
			$tipe_konten_url = add_query_arg( 'tipe_konten', $tipe_konten->slug, $game_url );
		}

		$crumbs[] = array(
			'label' => $tipe_konten->name,
			'url'   => $tipe_konten_url,
		);
	}

	$crumbs[] = array(
		'label' => get_the_title(),
		'url'   => '',
	);
	?>
	<nav class="lunar-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'lunar' ); ?>">
		<ol class="lunar-breadcrumb__list">
			<?php foreach ( $crumbs as $crumb ) : ?>
				<li class="lunar-breadcrumb__item">
					<?php if ( ! empty( $crumb['url'] ) ) : ?>
						<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<?php else : ?>
						<span aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}
