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
		<a href="<?php echo esc_url( get_search_link() ); ?>">
			<?php esc_html_e( 'Cari artikel', 'lunar' ); ?>
		</a>
	</p>
</main>

<?php
get_footer();
