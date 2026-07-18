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
	if ( is_singular( 'wiki_artikel' ) ) {
		$crumbs = lunar_get_breadcrumb_for_wiki_artikel();
	} elseif ( is_tax( 'game' ) ) {
		$crumbs = lunar_get_breadcrumb_for_game_archive();
	} else {
		return;
	}

	if ( empty( $crumbs ) ) {
		return;
	}
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

/**
 * Builds the breadcrumb trail for a single Wiki Artikel:
 * Beranda > Franchise > Judul Spesifik > Tipe Konten > Judul Artikel.
 *
 * @return array<int, array{label: string, url: string}>
 */
function lunar_get_breadcrumb_for_wiki_artikel(): array {
	$crumbs = array(
		array(
			'label' => __( 'Beranda', 'lunar' ),
			'url'   => home_url( '/' ),
		),
	);

	$game_term = lunar_get_current_game_term();
	$game_url  = '';

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

		$game_url_maybe_error = get_term_link( $game_term );
		$game_url             = is_wp_error( $game_url_maybe_error ) ? '' : $game_url_maybe_error;

		$crumbs[] = array(
			'label' => $game_term->name,
			'url'   => $game_url,
		);
	}

	$tipe_konten_terms = get_the_terms( get_the_ID(), 'tipe_konten' );

	if ( is_array( $tipe_konten_terms ) && ! empty( $tipe_konten_terms ) ) {
		$tipe_konten     = $tipe_konten_terms[0];
		$tipe_konten_url = '';

		// Links to the game's archive page pre-filtered by this content
		// type — relies on WordPress's native support for combining two
		// registered taxonomy query vars (game + tipe_konten) in one
		// request, so no custom pre_get_posts filtering is needed.
		if ( '' !== $game_url ) {
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

	return $crumbs;
}

/**
 * Builds the breadcrumb trail for a Game taxonomy archive:
 * - Franchise-level term:    Beranda > Franchise
 * - Judul Spesifik term:     Beranda > Franchise > Judul Spesifik
 *
 * @return array<int, array{label: string, url: string}>
 */
function lunar_get_breadcrumb_for_game_archive(): array {
	$crumbs = array(
		array(
			'label' => __( 'Beranda', 'lunar' ),
			'url'   => home_url( '/' ),
		),
	);

	$term = get_queried_object();

	if ( ! ( $term instanceof WP_Term ) ) {
		return $crumbs;
	}

	if ( 0 === (int) $term->parent ) {
		// Currently viewing the Franchise itself — last crumb, no link.
		$crumbs[] = array(
			'label' => $term->name,
			'url'   => '',
		);

		return $crumbs;
	}

	$franchise = get_term( $term->parent, 'game' );

	if ( $franchise && ! is_wp_error( $franchise ) ) {
		$crumbs[] = array(
			'label' => $franchise->name,
			'url'   => get_term_link( $franchise ),
		);
	}

	$crumbs[] = array(
		'label' => $term->name,
		'url'   => '',
	);

	return $crumbs;
}
