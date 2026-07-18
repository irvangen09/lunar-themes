<?php
/**
 * The mandatory fallback template required by WordPress's template
 * hierarchy. In practice this theme relies on more specific templates
 * (single.php, page.php, archive.php, search.php, 404.php) for every
 * real page on the site — this file only catches edge cases WordPress
 * itself defines (e.g. an unexpected post type with no matching
 * template higher in the hierarchy).
 *
 * @package Lunar
 */

get_header();
?>

<main class="lunar-content" id="main-content">

	<?php if ( have_posts() ) : ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'lunar-fallback-entry' ); ?> id="post-<?php the_ID(); ?>">
				<h1 class="lunar-fallback-entry__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h1>
				<div class="lunar-fallback-entry__excerpt">
					<?php the_excerpt(); ?>
				</div>
			</article>
			<?php
		endwhile;

		the_posts_pagination();

	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'lunar' ); ?></p>
		<?php
	endif;
	?>

</main>

<?php
get_footer();
