<?php
/**
 * Homepage template. Used automatically by WordPress when a static Page
 * is set as the front page (Settings > Reading). Shows the Game Tiles
 * grid and the latest Wiki Artikel entries — no page title or hero
 * text, by design.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="main-content">

		<?php
		$lunar_game_terms = lunar_get_game_terms();

		if ( ! empty( $lunar_game_terms ) ) :
			?>
			<section class="lunar-section">
				<p class="lunar-section__label"><?php esc_html_e( 'Jelajahi Game', 'lunar' ); ?></p>
				<h2 class="lunar-section__title"><?php esc_html_e( 'Pilih judul game', 'lunar' ); ?></h2>

				<div class="lunar-game-tile-grid">
					<?php
					foreach ( $lunar_game_terms as $lunar_game_term ) :
						$lunar_tile_image_id = lunar_get_game_tile_image_id( $lunar_game_term );
						?>
						<a class="lunar-game-tile" href="<?php echo esc_url( lunar_get_game_tile_url( $lunar_game_term ) ); ?>">
							<?php if ( $lunar_tile_image_id > 0 ) : ?>
								<span class="lunar-game-tile__icon lunar-game-tile__icon--image" aria-hidden="true">
									<?php echo wp_get_attachment_image( $lunar_tile_image_id, 'thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
								</span>
							<?php else : ?>
								<span class="lunar-game-tile__icon" aria-hidden="true">
									<?php echo esc_html( $lunar_game_term->name ); ?>
								</span>
							<?php endif; ?>
							<span class="lunar-game-tile__label"><?php echo esc_html( $lunar_game_term->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
			<?php
		endif;

		// On the static front page, WordPress stores a secondary loop's
		// page number under the "page" query var, not "paged" (that one
		// is reserved for the main blog/archive query). Checking both
		// keeps this robust if the front page setting ever changes.
		$lunar_articles_paged = (int) get_query_var( 'page' );

		if ( ! $lunar_articles_paged ) {
			$lunar_articles_paged = (int) get_query_var( 'paged' );
		}

		$lunar_articles_paged = max( 1, $lunar_articles_paged );

		$lunar_latest_articles = new WP_Query(
			array(
				'post_type'      => 'wiki_artikel',
				'posts_per_page' => 3,
				'paged'          => $lunar_articles_paged,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( $lunar_latest_articles->have_posts() ) :
			?>
			<section class="lunar-section">
				<p class="lunar-section__label"><?php esc_html_e( 'Baru diperbarui', 'lunar' ); ?></p>
				<h2 class="lunar-section__title"><?php esc_html_e( 'Artikel terbaru', 'lunar' ); ?></h2>

				<div class="lunar-article-grid">
					<?php
					while ( $lunar_latest_articles->have_posts() ) :
						$lunar_latest_articles->the_post();

						$lunar_tipe_konten_terms = get_the_terms( get_the_ID(), 'tipe_konten' );
						?>
						<article class="lunar-article-card">
							<?php if ( is_array( $lunar_tipe_konten_terms ) && ! empty( $lunar_tipe_konten_terms ) ) : ?>
								<span class="lunar-badge">
									<?php echo esc_html( $lunar_tipe_konten_terms[0]->name ); ?>
								</span>
							<?php endif; ?>

							<h3 class="lunar-article-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>

							<?php if ( has_excerpt() ) : ?>
								<p class="lunar-article-card__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
							<?php endif; ?>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>

				<?php if ( $lunar_latest_articles->max_num_pages > 1 ) : ?>
					<nav class="lunar-article-nav" aria-label="<?php esc_attr_e( 'Navigasi artikel terbaru', 'lunar' ); ?>">
						<?php if ( $lunar_articles_paged > 1 ) : ?>
							<a class="lunar-article-nav__prev" href="<?php echo esc_url( get_pagenum_link( $lunar_articles_paged - 1 ) ); ?>">
								&lsaquo; <?php esc_html_e( 'Sebelumnya', 'lunar' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $lunar_articles_paged < $lunar_latest_articles->max_num_pages ) : ?>
							<a class="lunar-article-nav__next" href="<?php echo esc_url( get_pagenum_link( $lunar_articles_paged + 1 ) ); ?>">
								<?php esc_html_e( 'Berikutnya', 'lunar' ); ?> &rsaquo;
							</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>
			</section>
			<?php
		endif;
		?>

	</main>

	<?php
endwhile;

get_footer();