<?php
/**
 * 404 (Not Found) template. Deliberately simple — a short message and a
 * couple of helpful links, no wireframe-level design was planned for
 * this page since it doesn't need one.
 *
 * @package Lunar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}

get_header();
?>

<main id="main-content" class="lunar-404">
	<h1><?php esc_html_e( 'Halaman Tidak Ditemukan', 'lunar' ); ?></h1>

	<p>
		<?php esc_html_e( 'Maaf, halaman yang Anda cari tidak ada atau sudah dipindahkan.', 'lunar' ); ?>
	</p>

	<p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Kembali ke Beranda', 'lunar' ); ?>
		</a>
		&mdash;
		<?php
		/*
		 * Deliberately not get_search_link(): called with no search term
		 * outside of a search context, it resolves to a pretty-permalink
		 * URL with an empty %search% segment (e.g. "/search/"), which does
		 * not match WordPress's search rewrite rule and 404s. Building the
		 * plain query-string URL avoids that entirely.
		 */
		?>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>">
			<?php esc_html_e( 'Cari artikel', 'lunar' ); ?>
		</a>
	</p>
</main>

<?php
get_footer();
