<?php
/**
 * Site footer — footer link menu (managed via Appearance > Menus,
 * assigned to the "Footer Menu" location) and copyright line. Closes
 * the wrapper opened in header.php.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}
?>

	<footer class="lunar-site-footer">
		<span class="lunar-site-footer__copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</span>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>

			<?php
			wp_nav_menu(
				array(
					'theme_location'  => 'footer',
					'container'       => 'nav',
					'container_class' => 'lunar-site-footer__links',
					'menu_class'      => 'lunar-site-footer__links-list',
					'depth'           => 1,
					'fallback_cb'     => false,
				)
			);
			?>

		<?php else : ?>

			<p class="lunar-site-footer__notice">
				<?php
				esc_html_e(
					'No footer menu assigned yet — create one under Appearance > Menus and assign it to the "Footer Menu" location.',
					'lunar'
				);
				?>
			</p>

		<?php endif; ?>
	</footer>

</div><!-- .lunar-site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
