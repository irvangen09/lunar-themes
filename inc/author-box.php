<?php
/**
 * Renders author-related bits shared across templates:
 *
 * - lunar_render_byline(): compact avatar + name + published date,
 *   shown right under the article title.
 * - lunar_render_author_box(): fuller card (avatar, name, role, bio,
 *   social links), shown at the end of a single article and reused as
 *   the Author Archive's header.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

/**
 * Outputs the byline: small avatar, author name (linked to their
 * archive), and the post's published date.
 *
 * @param int $user_id User ID. Defaults to the current post's author
 *                      when called from inside the Loop.
 */
function lunar_render_byline( int $user_id = 0 ): void {
	if ( 0 === $user_id ) {
		$user_id = (int) get_the_author_meta( 'ID' );
	}

	if ( 0 === $user_id ) {
		return;
	}

	$display_name = get_the_author_meta( 'display_name', $user_id );
	$archive_url  = get_author_posts_url( $user_id );
	?>
	<div class="lunar-article__byline">
		<?php echo get_avatar( $user_id, 32, '', '', array( 'class' => 'lunar-article__byline-avatar' ) ); ?>
		<span class="lunar-article__byline-text">
			<?php
			printf(
				/* translators: 1: author name link, 2: published date */
				esc_html__( 'Oleh %1$s — %2$s', 'lunar' ),
				'<a href="' . esc_url( $archive_url ) . '">' . esc_html( $display_name ) . '</a>',
				esc_html( get_the_date() )
			);
			?>
		</span>
	</div>
	<?php
}

/**
 * Outputs the Author Box for a given user.
 *
 * Role and social links come from the companion plugin's (LunarCore)
 * public API functions. If those functions aren't available for any
 * reason (e.g. the plugin is inactive), those two pieces are simply
 * omitted — name, avatar, and bio (all native WordPress) still render
 * on their own.
 *
 * @param int  $user_id            User ID. Defaults to the current post's
 *                                 author when called from inside the Loop.
 * @param bool $is_archive_heading Whether this call is the Author Archive's
 *                                 page header rather than the box shown at
 *                                 the end of a single article. When true,
 *                                 the author's name is rendered as the
 *                                 page's h1 instead of a plain paragraph,
 *                                 since the Archive template otherwise has
 *                                 no top-level heading of its own.
 */
function lunar_render_author_box( int $user_id = 0, bool $is_archive_heading = false ): void {
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

	if ( function_exists( 'lunar_core_get_author_role' ) ) {
		$role = lunar_core_get_author_role( $user_id );
	}

	if ( function_exists( 'lunar_core_get_author_social_links' ) ) {
		$links = lunar_core_get_author_social_links( $user_id );
	}

	$lunar_name_tag = $is_archive_heading ? 'h1' : 'p';
	?>
	<div class="lunar-author-box">
		<?php echo get_avatar( $user_id, 96, '', '', array( 'class' => 'lunar-author-box__avatar' ) ); ?>

		<div class="lunar-author-box__body">
			<<?php echo tag_escape( $lunar_name_tag ); ?> class="lunar-author-box__name">
				<a href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $display_name ); ?></a>
				<?php if ( '' !== $role ) : ?>
					<span class="lunar-author-box__role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
			</<?php echo tag_escape( $lunar_name_tag ); ?>>

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