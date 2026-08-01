<?php
/**
 * Search Result template. Covers: the search box, the "Game Title"
 * checkbox filter, the Tipe Konten pill filter, the contextual
 * field-sync filters (Tier Alat, Peran, etc. — rendered only when
 * lunar_get_active_field_filters() finds at least one field with real
 * data among the currently matching results), results list, and
 * pagination.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

global $wp_query;

get_header();

$lunar_search_query      = get_search_query();
$lunar_game_terms        = lunar_get_game_terms();
$lunar_selected_games    = isset( $_GET['games'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['games'] ) ) : array();

// Computed once and reused below — the slug is used as the taxonomy name,
// the query var name, the hidden form field name, and the
// add_query_arg()/remove_query_arg() key (they're all the same value
// here, since the taxonomy is registered with query_var => true).
$lunar_content_type_slug = function_exists( 'lunar_core_get_taxonomy_slug_content_type' )
	? lunar_core_get_taxonomy_slug_content_type()
	: '';
$lunar_active_tipe       = sanitize_title( (string) get_query_var( $lunar_content_type_slug ) );
$lunar_content_type_all  = $lunar_content_type_slug
	? get_terms(
		array(
			'taxonomy'   => $lunar_content_type_slug,
			'hide_empty' => true,
		)
	)
	: array();

if ( ! is_array( $lunar_content_type_all ) ) {
	$lunar_content_type_all = array();
}

// Current full URL (with existing query args) — used as the base for
// pill links so toggling Tipe Konten never discards the active search
// term or Game checkbox selections.
// Note: the second argument must be omitted entirely (or literally
// `false`) to fall back to the current URL. Passing `null` explicitly
// does NOT count as "omitted" and makes add_query_arg() return an
// empty string instead — silently breaking every link built from it.
$lunar_current_url = add_query_arg( array() );

$lunar_active_field_filters = lunar_get_active_field_filters();
$lunar_selected_fields      = array();

if ( isset( $_GET['fields'] ) && is_array( $_GET['fields'] ) ) {
	foreach ( wp_unslash( $_GET['fields'] ) as $lunar_field_key => $lunar_field_values ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification.Recommended
		$lunar_selected_fields[ sanitize_key( $lunar_field_key ) ] = array_map( 'sanitize_text_field', (array) $lunar_field_values );
	}
}
?>

<main id="main-content" class="lunar-search">

	<section class="lunar-search-hero">
		<h1 class="lunar-search-hero__label"><?php esc_html_e( 'Hasil Pencarian', 'lunar' ); ?></h1>

		<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="lunar-search-form">
			<input type="text" name="s" value="<?php echo esc_attr( $lunar_search_query ); ?>" placeholder="<?php esc_attr_e( 'Cari artikel...', 'lunar' ); ?>">

			<?php if ( '' !== $lunar_active_tipe ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $lunar_content_type_slug ); ?>" value="<?php echo esc_attr( $lunar_active_tipe ); ?>">
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

			<?php if ( ! empty( $lunar_active_field_filters ) ) : ?>
				<div class="lunar-search-form__fields">
					<?php foreach ( $lunar_active_field_filters as $lunar_field_slug => $lunar_field_values ) : ?>
						<fieldset class="lunar-field-filter">
							<legend class="lunar-field-filter__label">
								<?php echo esc_html( lunar_get_field_label( $lunar_field_slug ) ); ?>
							</legend>
							<?php foreach ( $lunar_field_values as $lunar_field_value ) : ?>
								<label class="lunar-filter-check">
									<input
										type="checkbox"
										name="fields[<?php echo esc_attr( $lunar_field_slug ); ?>][]"
										value="<?php echo esc_attr( $lunar_field_value ); ?>"
										<?php checked( in_array( $lunar_field_value, $lunar_selected_fields[ $lunar_field_slug ] ?? array(), true ) ); ?>
									>
									<?php echo esc_html( $lunar_field_value ); ?>
								</label>
							<?php endforeach; ?>
						</fieldset>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

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

	<?php if ( ! empty( $lunar_content_type_all ) ) : ?>
		<nav class="lunar-filter-bar" aria-label="<?php esc_attr_e( 'Filter Tipe Konten', 'lunar' ); ?>">
			<a
				class="lunar-filter-pill<?php echo '' === $lunar_active_tipe ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( remove_query_arg( $lunar_content_type_slug, $lunar_current_url ) ); ?>"
			>
				<?php esc_html_e( 'Semua', 'lunar' ); ?>
			</a>
			<?php foreach ( $lunar_content_type_all as $lunar_type ) : ?>
				<a
					class="lunar-filter-pill<?php echo $lunar_active_tipe === $lunar_type->slug ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( add_query_arg( $lunar_content_type_slug, $lunar_type->slug, $lunar_current_url ) ); ?>"
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

				$lunar_game_post_terms         = get_the_terms( get_the_ID(), 'game' );
				$lunar_content_type_post_terms = $lunar_content_type_slug
					? get_the_terms( get_the_ID(), $lunar_content_type_slug )
					: false;
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
						if ( is_array( $lunar_content_type_post_terms ) && ! empty( $lunar_content_type_post_terms ) ) {
							echo esc_html( $lunar_content_type_post_terms[0]->name );
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