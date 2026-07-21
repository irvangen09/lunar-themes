<?php
/**
 * Archive per Game template. Matches WordPress's taxonomy-{slug}.php
 * convention, so it only ever runs for the Game taxonomy — never for
 * date archives, author archives, or the Tipe Konten taxonomy on its
 * own, which need different layouts entirely (or none at all, in the
 * case of Tipe Konten browsed standalone, per the decision to only
 * support browsing by Game with a Tipe Konten filter, not the reverse).
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

get_header();

$lunar_term = get_queried_object();

lunar_breadcrumb();
?>

<main id="main-content" class="lunar-archive">

	<?php if ( $lunar_term instanceof WP_Term ) : ?>

		<header class="lunar-archive__header">
			<span class="lunar-archive__icon" aria-hidden="true"><?php echo esc_html( $lunar_term->name ); ?></span>
			<div>
				<h1><?php echo esc_html( $lunar_term->name ); ?></h1>
				<?php if ( ! empty( $lunar_term->description ) ) : ?>
					<p><?php echo esc_html( $lunar_term->description ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<?php
		$lunar_content_types  = lunar_get_content_types_for_game( $lunar_term->term_id );
		$lunar_active_tipe    = sanitize_title( (string) get_query_var( 'tipe_konten' ) );
		$lunar_archive_url    = get_term_link( $lunar_term );
		$lunar_archive_url    = is_wp_error( $lunar_archive_url ) ? '' : $lunar_archive_url;

		if ( ! empty( $lunar_content_types ) && '' !== $lunar_archive_url ) :
			?>
			<nav class="lunar-filter-bar" aria-label="<?php esc_attr_e( 'Filter Tipe Konten', 'lunar' ); ?>">
				<a
					class="lunar-filter-pill<?php echo '' === $lunar_active_tipe ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $lunar_archive_url ); ?>"
				>
					<?php
					printf(
						/* translators: %d: total number of articles */
						esc_html__( 'Semua (%d)', 'lunar' ),
						(int) $lunar_term->count
					);
					?>
				</a>
				<?php foreach ( $lunar_content_types as $lunar_type ) : ?>
					<a
						class="lunar-filter-pill<?php echo $lunar_active_tipe === $lunar_type->slug ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( add_query_arg( 'tipe_konten', $lunar_type->slug, $lunar_archive_url ) ); ?>"
					>
						<?php echo esc_html( $lunar_type->name ); ?> (<?php echo (int) $lunar_type->count; ?>)
					</a>
				<?php endforeach; ?>
			</nav>
			<?php
		endif;
		?>

	<?php endif; ?>

	<?php if ( have_posts() ) : ?>

		<div class="lunar-archive-list">
			<?php
			$lunar_is_franchise_level = $lunar_term instanceof WP_Term && 0 === (int) $lunar_term->parent;

			while ( have_posts() ) :
				the_post();

				$lunar_tipe_konten_terms = get_the_terms( get_the_ID(), 'tipe_konten' );
				$lunar_article_game_term = null;

				if ( $lunar_is_franchise_level ) {
					$lunar_article_game_terms = get_the_terms( get_the_ID(), 'game' );

					if ( is_array( $lunar_article_game_terms ) && ! empty( $lunar_article_game_terms ) ) {
						$lunar_article_game_term = $lunar_article_game_terms[0];
					}
				}
				?>
				<div class="lunar-archive-list-item">
					<?php if ( is_array( $lunar_tipe_konten_terms ) && ! empty( $lunar_tipe_konten_terms ) ) : ?>
						<span class="lunar-archive-list-item__badge">
							<?php echo esc_html( $lunar_tipe_konten_terms[0]->name ); ?>
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

		<p><?php esc_html_e( 'Belum ada artikel untuk judul game ini.', 'lunar' ); ?></p>

	<?php endif; ?>

</main>

<?php
get_footer();