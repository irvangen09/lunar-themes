<?php
/**
 * Single Wiki Artikel template.
 *
 * Infobox is split out of the content by lunar_get_article_layout()
 * and rendered as its own sidebar element, sitting next to the main
 * content in a two-column grid. Positioning, spacing, and sticky
 * behavior for that grid live entirely in single.css — this template
 * only decides *whether* the sidebar exists (an article without an
 * Infobox simply gets no sidebar markup at all, falling back to a
 * single full-width column).
 *
 * The sidebar markup is placed BEFORE the content on purpose: on
 * mobile the grid collapses to a plain block layout, so source order
 * is what decides visual order there, and Infobox is meant to appear
 * near the top. On desktop, single.css assigns explicit grid columns
 * so the visual left/right position doesn't depend on this order.
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

	$lunar_article_layout = lunar_get_article_layout();
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

			<div class="lunar-article__layout">
				<?php if ( $lunar_article_layout['has_infobox'] ) : ?>
					<aside class="lunar-article__sidebar">
						<?php echo $lunar_article_layout['infobox_html']; ?>
					</aside>
				<?php endif; ?>

				<div class="lunar-article__content">
					<?php echo $lunar_article_layout['content_html']; ?>
				</div>
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