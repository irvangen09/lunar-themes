<?php
/**
 * Single Post template — for the native WordPress 'post' post type only
 * (blog posts, site announcements). Distinct from Wiki Article, which
 * has its own dedicated single-wiki_article.php: no Infobox sidebar,
 * category badge comes from native WordPress Categories instead of
 * the Tipe Konten taxonomy, and the footer shows Tags plus a Related
 * Posts section instead of a changelog.
 *
 * Reuses the .lunar-article__* typography and spacing rules from
 * single.css on purpose — a long-form post and a wiki article should
 * read identically at the paragraph/heading level; only the metadata
 * around the content differs.
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

	$lunar_post_categories = get_the_category();
	$lunar_post_tags       = get_the_tags();
	?>

	<main id="main-content" class="lunar-article">
		<article <?php post_class( 'lunar-article__entry' ); ?> id="post-<?php the_ID(); ?>">

			<?php if ( ! empty( $lunar_post_categories ) ) : ?>
				<span class="lunar-badge lunar-badge--category">
					<?php echo esc_html( $lunar_post_categories[0]->name ); ?>
				</span>
			<?php endif; ?>

			<h1 class="lunar-article__title"><?php the_title(); ?></h1>

			<?php lunar_render_byline(); ?>

			<?php if ( has_excerpt() ) : ?>
				<p class="lunar-article__tagline"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<div class="lunar-article__layout">
				<div class="lunar-article__content">
					<?php the_content(); ?>
				</div>
			</div>

			<?php lunar_render_author_box(); ?>

			<?php if ( ! empty( $lunar_post_tags ) ) : ?>
				<ul class="lunar-post__tags">
					<?php foreach ( $lunar_post_tags as $lunar_tag ) : ?>
						<li>
							<a href="<?php echo esc_url( get_tag_link( $lunar_tag ) ); ?>">
								<?php echo esc_html( $lunar_tag->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php
			// Related Posts: other 'post' entries sharing at least one
			// Category with this one. Silently omitted if this post has
			// no category or no other post shares one — there is nothing
			// useful to relate it to.
			if ( ! empty( $lunar_post_categories ) ) :
				$lunar_related_query = new WP_Query(
					array(
						'post_type'      => 'post',
						'posts_per_page' => 3,
						'post__not_in'   => array( get_the_ID() ),
						'category__in'   => wp_list_pluck( $lunar_post_categories, 'term_id' ),
						'orderby'        => 'date',
						'order'          => 'DESC',
						'no_found_rows'  => true,
					)
				);

				if ( $lunar_related_query->have_posts() ) :
					?>
					<section class="lunar-related-posts">
						<p class="lunar-related-posts__label">
							<?php esc_html_e( 'Artikel Terkait', 'lunar' ); ?>
						</p>

						<div class="lunar-related-posts__grid">
							<?php
							while ( $lunar_related_query->have_posts() ) :
								$lunar_related_query->the_post();
								?>
								<a class="lunar-related-posts__card" href="<?php the_permalink(); ?>">
									<?php
									$lunar_related_categories = get_the_category();
									if ( ! empty( $lunar_related_categories ) ) :
										?>
										<span class="lunar-badge">
											<?php echo esc_html( $lunar_related_categories[0]->name ); ?>
										</span>
									<?php endif; ?>
									<span class="lunar-related-posts__title"><?php the_title(); ?></span>
								</a>
								<?php
							endwhile;
							?>
						</div>
					</section>
					<?php
				endif;

				wp_reset_postdata();
			endif;
			?>

		</article>
	</main>

	<?php
endwhile;

get_footer();