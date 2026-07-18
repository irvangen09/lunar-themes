<?php
/**
 * Generic Page template — used for every static Page except the one
 * set as the front page (which uses front-page.php instead). Intended
 * for simple content pages: About, Contact Us, Privacy, Terms,
 * Editorial, Disclaimer, etc. Just a title and its content, nothing more.
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
	?>

	<main id="main-content" class="lunar-page">
		<article <?php post_class( 'lunar-page__entry' ); ?> id="post-<?php the_ID(); ?>">
			<h1 class="lunar-page__title"><?php the_title(); ?></h1>

			<div class="lunar-page__content">
				<?php the_content(); ?>
			</div>
		</article>
	</main>

	<?php
endwhile;

get_footer();
