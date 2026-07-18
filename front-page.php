<?php
/**
 * Homepage template. Used automatically by WordPress when a static Page
 * is set as the front page (Settings > Reading). The hero title and
 * description come from that Page's own title and Excerpt — editable
 * by the site owner without touching this file.
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

		<section class="lunar-hero">
			<h1 class="lunar-hero__title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="lunar-hero__description"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</section>

		<?php
		$lunar_game_terms = lunar_get_game_terms();

		if ( ! empty( $lunar_game_terms ) ) :
			?>
			<section class="lunar-section">
				<p class="lunar-section__label"><?php esc_html_e( 'Jelajahi Game', 'lunar' ); ?></p>
				<h2 class="lunar-section__title"><?php esc_html_e( 'Pilih judul game', 'lunar' ); ?></h2>

				<div class="lunar-game-tile-grid">
					<?php foreach ( $lunar_game_terms as $lunar_game_term ) : ?>
						<a class="lunar-game-tile" href="<?php echo esc_url( get_term_link( $lunar_game_term ) ); ?>">
							<span class="lunar-game-tile__icon" aria-hidden="true">
								<?php echo esc_html( $lunar_game_term->name ); ?>
							</span>
							<span class="lunar-game-tile__label"><?php echo esc_html( $lunar_game_term->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
			<?php
		endif;

		$lunar_latest_articles = new WP_Query(
			array(
				'post_type'      => 'wiki_artikel',
				'posts_per_page' => 3,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
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
			</section>
			<?php
		endif;
		?>

	</main>

	<?php
endwhile;

get_footer();
