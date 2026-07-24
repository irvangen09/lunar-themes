<?php
/**
 * Renders the Author Box: avatar, name (linked to their archive),
 * role, bio, and social links.
 *
 * Used in two places: at the end of a single article, and as the
 * header of the Author Archive template — same markup in both, since
 * an author archive page is, in effect, entirely about that one
 * author, so the fuller version reads better there too rather than a
 * separate stripped-down header.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Outputs the Author Box for a given user.
 *
 * Role and social links come from the companion plugin (LunarCore).
 * If that class isn't available for any reason, those two pieces are
 * simply omitted — name, avatar, and bio (all native WordPress) still
 * render on their own.
 *
 * @param int $user_id User ID. Defaults to the current post's author
 *                      when called from inside the Loop.
 */
function lunar_render_author_box( int $user_id = 0 ): void {
	if ( 0 === $user_id ) {
		$user_id = (int) get_the_author_meta( 'ID' );
	}

	if ( 0 === $user_id ) {
		return;
	}

	$display_name = get_the_author_meta( 'display_name', $user_id );
	$bio          = get_the_author_meta( 'description', $user_id );
	$archive_url  = get_author_posts_url( $user_id );

	$role  = '';
	$links = array();

	if ( class_exists( '\Lunar\Users\Author_Fields' ) ) {
		$role  = \Lunar\Users\Author_Fields::get_role( $user_id );
		$links = \Lunar\Users\Author_Fields::get_social_links( $user_id );
	}
	?>
	<div class="lunar-author-box">
		<?php echo get_avatar( $user_id, 96, '', '', array( 'class' => 'lunar-author-box__avatar' ) ); ?>

		<div class="lunar-author-box__body">
			<p class="lunar-author-box__name">
				<a href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $display_name ); ?></a>
				<?php if ( '' !== $role ) : ?>
					<span class="lunar-author-box__role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
			</p>

			<?php if ( '' !== $bio ) : ?>
				<p class="lunar-author-box__bio"><?php echo esc_html( $bio ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $links ) ) : ?>
				<ul class="lunar-author-box__social">
					<?php foreach ( $links as $link ) : ?>
						<li>
							<a
								href="<?php echo esc_url( $link['url'] ); ?>"
								class="lunar-author-box__social-link dashicons <?php echo esc_attr( $link['icon'] ); ?>"
								aria-label="<?php echo esc_attr( $link['label'] ); ?>"
								rel="me noopener noreferrer"
								target="_blank"
							></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php
}