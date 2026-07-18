<?php
/**
 * Single Wiki Artikel template.
 *
 * Renders one column of content only — the Infobox block that authors
 * place at the start of the article is what visually becomes a right
 * column on desktop, purely through CSS (float + sticky), not through
 * any grid/column markup here. See the styling stage for that CSS.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

get_header();

while ( have_posts() ) :
	the_post();

	lunar_breadcrumb();

	$lunar_tipe_konten_terms = get_the_terms( get_the_ID(), 'tipe_konten' );
	$lunar_update_notes      = get_post_meta( get_the_ID(), 'lunar_core_update_notes', true );
	$lunar_update_notes_list = array();

	if ( ! empty( trim( (string) $lunar_update_notes ) ) ) {
		$lunar_update_notes_list = array_filter( array_map( 'trim', explode( "\n", $lunar_update_notes ) ) );
	}
	?>

	<main id="main-content" class="lunar-article">
		<article <?php post_class( 'lunar-article__entry' ); ?> id="post-<?php the_ID(); ?>">

			<?php if ( is_array( $lunar_tipe_konten_terms ) && ! empty( $lunar_tipe_konten_terms ) ) : ?>
				<span class="lunar-badge lunar-badge--category">
					<?php echo esc_html( $lunar_tipe_konten_terms[0]->name ); ?>
				</span>
			<?php endif; ?>

			<h1 class="lunar-article__title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="lunar-article__tagline"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<div class="lunar-article__content">
				<?php the_content(); ?>
			</div>

			<footer class="lunar-article__meta">
				<p class="lunar-article__updated">
					<?php
					printf(
						/* translators: %s: last modified date */
						esc_html__( 'Terakhir diperbarui: %s', 'lunar' ),
						esc_html( get_the_modified_date() )
					);
					?>
				</p>

				<?php if ( ! empty( $lunar_update_notes_list ) ) : ?>
					<ul class="lunar-article__update-notes">
						<?php foreach ( $lunar_update_notes_list as $lunar_note_line ) : ?>
							<li><?php echo esc_html( $lunar_note_line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</footer>

		</article>
	</main>

	<?php
endwhile;

get_footer();
