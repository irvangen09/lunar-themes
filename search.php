<?php
/**
 * Search Result template (MVP scope). Covers: the search box, the
 * "Game Title" checkbox filter, the Tipe Konten pill filter, results
 * list, and pagination. The contextual field-sync filters (Tier Alat,
 * Peran, etc. — shown only when a specific Tipe Konten pill is active)
 * are a deliberately separate follow-up, not part of this file.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

global $wp_query;

get_header();

$lunar_search_query   = get_search_query();
$lunar_game_terms      = lunar_get_game_terms();
$lunar_selected_games  = isset( $_GET['games'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['games'] ) ) : array();
$lunar_active_tipe     = sanitize_title( (string) get_query_var( 'tipe_konten' ) );
$lunar_tipe_konten_all = get_terms(
	array(
		'taxonomy'   => 'tipe_konten',
		'hide_empty' => true,
	)
);

if ( ! is_array( $lunar_tipe_konten_all ) ) {
	$lunar_tipe_konten_all = array();
}

// Current full URL (with existing query args) — used as the base for
// pill links so toggling Tipe Konten never discards the active search
// term or Game checkbox selections.
$lunar_current_url = add_query_arg( array(), null );
?>

<main id="main-content" class="lunar-search">

	<section class="lunar-search-hero">
		<p class="lunar-search-hero__label"><?php esc_html_e( 'Hasil Pencarian', 'lunar' ); ?></p>

		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="lunar-search-form">
			<input type="text" name="s" value="<?php echo esc_attr( $lunar_search_query ); ?>" placeholder="<?php esc_attr_e( 'Cari artikel...', 'lunar' ); ?>">

			<?php if ( '' !== $lunar_active_tipe ) : ?>
				<input type="hidden" name="tipe_konten" value="<?php echo esc_attr( $lunar_active_tipe ); ?>">
			<?php endif; ?>

			<?php foreach ( $lunar_game_terms as $lunar_game_term ) : ?>
				<label class="lunar-filter-check">
					<input
						type="checkbox"
						name="games[]"
						value="<?php echo esc_attr( $lunar_game_term->slug ); ?>"
						<?php checked( in_array( $lunar_game_term->slug, $lunar_selected_games, true ) ); ?>
					>
					<?php echo esc_html( $lunar_game_term->name ); ?>
				</label>
			<?php endforeach; ?>

			<button type="submit"><?php esc_html_e( 'Cari', 'lunar' ); ?></button>
		</form>

		<?php if ( '' !== $lunar_search_query ) : ?>
			<p class="lunar-search-result-count">
				<?php
				printf(
					/* translators: 1: number of results, 2: search term */
					esc_html__( '%1$d artikel ditemukan untuk "%2$s"', 'lunar' ),
					(int) $wp_query->found_posts,
					esc_html( $lunar_search_query )
				);
				?>
			</p>
		<?php endif; ?>
	</section>

	<?php if ( ! empty( $lunar_tipe_konten_all ) ) : ?>
		<nav class="lunar-filter-bar" aria-label="<?php esc_attr_e( 'Filter Tipe Konten', 'lunar' ); ?>">
			<a
				class="lunar-filter-pill<?php echo '' === $lunar_active_tipe ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( remove_query_arg( 'tipe_konten', $lunar_current_url ) ); ?>"
			>
				<?php esc_html_e( 'Semua', 'lunar' ); ?>
			</a>
			<?php foreach ( $lunar_tipe_konten_all as $lunar_type ) : ?>
				<a
					class="lunar-filter-pill<?php echo $lunar_active_tipe === $lunar_type->slug ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( add_query_arg( 'tipe_konten', $lunar_type->slug, $lunar_current_url ) ); ?>"
				>
					<?php echo esc_html( $lunar_type->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>

		<div class="lunar-result-list">
			<?php
			while ( have_posts() ) :
				the_post();

				$lunar_game_post_terms  = get_the_terms( get_the_ID(), 'game' );
				$lunar_tipe_post_terms  = get_the_terms( get_the_ID(), 'tipe_konten' );
				?>
				<div class="lunar-result-item">
					<span class="lunar-result-item__game">
						<?php
						if ( is_array( $lunar_game_post_terms ) && ! empty( $lunar_game_post_terms ) ) {
							echo esc_html( $lunar_game_post_terms[0]->name );
						}
						?>
					</span>
					<span class="lunar-result-item__type">
						<?php
						if ( is_array( $lunar_tipe_post_terms ) && ! empty( $lunar_tipe_post_terms ) ) {
							echo esc_html( $lunar_tipe_post_terms[0]->name );
						}
						?>
					</span>
					<a class="lunar-result-item__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>
				<?php
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>

	<?php else : ?>

		<p><?php esc_html_e( 'Tidak ada artikel yang cocok dengan pencarian Anda.', 'lunar' ); ?></p>

	<?php endif; ?>

</main>

<?php
get_footer();
