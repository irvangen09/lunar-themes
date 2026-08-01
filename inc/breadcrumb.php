<?php
/**
 * Breadcrumb helper. Handles the single Wiki Article, native Post,
 * Game archive, and Author archive contexts. Search and Pages (Laman)
 * are intentionally not handled here — lunar_breadcrumb() is a no-op
 * for both, by design, not because they're pending.
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
	if ( function_exists( 'lunar_core_is_wiki_article' ) && lunar_core_is_wiki_article() ) {
		$crumbs = lunar_get_breadcrumb_for_wiki_article();
	} elseif ( is_singular( 'post' ) ) {
		$crumbs = lunar_get_breadcrumb_for_post();
	} elseif ( is_tax( 'game' ) ) {
		$crumbs = lunar_get_breadcrumb_for_game_archive();
	} elseif ( is_author() ) {
		$crumbs = lunar_get_breadcrumb_for_author_archive();
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
 * Builds the breadcrumb trail for a single Wiki Article:
 * Beranda > Franchise > Judul Spesifik > Tipe Konten > Judul Artikel.
 *
 * @return array<int, array{label: string, url: string}>
 */
function lunar_get_breadcrumb_for_wiki_article(): array {
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

	$content_type_slug  = function_exists( 'lunar_core_get_taxonomy_slug_content_type' )
		? lunar_core_get_taxonomy_slug_content_type()
		: '';
	$content_type_terms = $content_type_slug
		? get_the_terms( get_the_ID(), $content_type_slug )
		: false;

	if ( is_array( $content_type_terms ) && ! empty( $content_type_terms ) ) {
		$content_type     = $content_type_terms[0];
		$content_type_url = '';

		// Links to the game's archive page pre-filtered by this content
		// type — relies on WordPress's native support for combining two
		// registered taxonomy query vars (game + content type) in one
		// request, so no custom pre_get_posts filtering is needed.
		if ( '' !== $game_url ) {
			$content_type_url = add_query_arg( $content_type_slug, $content_type->slug, $game_url );
		}

		$crumbs[] = array(
			'label' => $content_type->name,
			'url'   => $content_type_url,
		);
	}

	$crumbs[] = array(
		'label' => get_the_title(),
		'url'   => '',
	);

	return $crumbs;
}

/**
 * Builds the breadcrumb trail for a native Post: Beranda > Category > Judul Post.
 * Uses only the first category if a post has several, same convention
 * as the category badge shown on the Post template itself.
 *
 * @return array<int, array{label: string, url: string}>
 */
function lunar_get_breadcrumb_for_post(): array {
	$crumbs = array(
		array(
			'label' => __( 'Beranda', 'lunar' ),
			'url'   => home_url( '/' ),
		),
	);

	$categories = get_the_category();

	if ( ! empty( $categories ) ) {
		$crumbs[] = array(
			'label' => $categories[0]->name,
			'url'   => get_category_link( $categories[0] ),
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

/**
 * Builds the breadcrumb trail for an Author archive: Beranda > Nama Penulis.
 *
 * @return array<int, array{label: string, url: string}>
 */
function lunar_get_breadcrumb_for_author_archive(): array {
	$crumbs = array(
		array(
			'label' => __( 'Beranda', 'lunar' ),
			'url'   => home_url( '/' ),
		),
	);

	$author = get_queried_object();

	if ( $author instanceof WP_User ) {
		$crumbs[] = array(
			'label' => $author->display_name,
			'url'   => '',
		);
	}

	return $crumbs;
}