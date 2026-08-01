<?php
/**
 * Author Archive template.
 *
 * Reuses the same .lunar-archive / .lunar-archive-list markup and
 * styles as the Game archive (taxonomy-game.php) — only the header
 * differs, using the full Author Box instead of an icon/title/
 * description block, since a page that's entirely about one author
 * reads better led by their fuller profile than a stripped-down one.
 *
 * No Tipe Konten filter bar here (unlike the Game archive) — an
 * author's articles can span any game/franchise, so filtering by
 * content type alone wouldn't be as meaningful without also grouping
 * by game first. Can be added later if this page sees real use.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

get_header();

$lunar_author_id = get_queried_object_id();

lunar_breadcrumb();
?>

<main id="main-content" class="lunar-archive">

	<header class="lunar-archive__header lunar-archive__header--author">
		<?php
		// True: this is the page's own top-level heading (the Author
		// Archive otherwise has no h1), unlike the Author Box shown at
		// the end of a single article, which stays a plain paragraph.
		lunar_render_author_box( $lunar_author_id, true );
		?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="lunar-archive-list">
			<?php
			while ( have_posts() ) :
				the_post();

				$lunar_content_type_slug  = function_exists( 'lunar_core_get_taxonomy_slug_content_type' )
					? lunar_core_get_taxonomy_slug_content_type()
					: '';
				$lunar_content_type_terms = $lunar_content_type_slug
					? get_the_terms( get_the_ID(), $lunar_content_type_slug )
					: false;
				$lunar_article_game_term  = null;
				$lunar_article_game_terms = get_the_terms( get_the_ID(), 'game' );

				if ( is_array( $lunar_article_game_terms ) && ! empty( $lunar_article_game_terms ) ) {
					$lunar_article_game_term = $lunar_article_game_terms[0];
				}
				?>
				<div class="lunar-archive-list-item">
					<?php if ( is_array( $lunar_content_type_terms ) && ! empty( $lunar_content_type_terms ) ) : ?>
						<span class="lunar-archive-list-item__badge">
							<?php echo esc_html( $lunar_content_type_terms[0]->name ); ?>
						</span>
					<?php endif; ?>
					<a class="lunar-archive-list-item__title" href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
						<?php if ( $lunar_article_game_term ) : ?>
							<span class="lunar-archive-list-item__game">(<?php echo esc_html( $lunar_article_game_term->name ); ?>)</span>
						<?php endif; ?>
					</a>
				</div>
				<?php
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>

	<?php else : ?>

		<p><?php esc_html_e( 'Belum ada artikel dari penulis ini.', 'lunar' ); ?></p>

	<?php endif; ?>

</main>

<?php
get_footer();