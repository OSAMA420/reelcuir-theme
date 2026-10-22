<?php
/**
 * Homepage (static front page). front-page.php hamesha front page ke liye use hota hai, is liye
 * Elementor wala "Home v4" (page 1158) ab nahi dikhta; wapas chahiye to ye file hata do.
 * Sections: hero, 4 category tiles, Best Selling, New Arrivals, features.
 * Upar ka Halloween ticker functions.php (RC_HOME_PAGE['ticker_*']) se aata hai (sirf homepage par).
 * Images Media Library IDs se: RC_HOME_PAGE constant (functions.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg = RC_HOME_PAGE;

get_header();
?>
<main id="rc-home" class="rc-home">

	<section class="rc-home-hero" aria-label="<?php esc_attr_e( 'Halloween sale', 'mixtas-child' ); ?>">
		<a href="<?php echo esc_url( home_url( $cfg['hero_link'] ) ); ?>">
			<?php
			$hd = wp_get_attachment_image_src( $cfg['hero_desktop'], 'full' );
			$hm = wp_get_attachment_image_src( $cfg['hero_mobile'], 'full' );
			if ( $hd ) :
				?>
				<picture>
					<?php if ( $hm ) : ?>
						<source media="(max-width: 767px)" srcset="<?php echo esc_url( $hm[0] ); ?>" width="<?php echo (int) $hm[1]; ?>" height="<?php echo (int) $hm[2]; ?>">
					<?php endif; ?>
					<img src="<?php echo esc_url( $hd[0] ); ?>" width="<?php echo (int) $hd[1]; ?>" height="<?php echo (int) $hd[2]; ?>" alt="<?php echo esc_attr( $cfg['hero_alt'] ); ?>" fetchpriority="high">
				</picture>
			<?php endif; ?>
		</a>
	</section>

	<section class="rc-home-cats rc-wrap" aria-label="<?php esc_attr_e( 'Shop by category', 'mixtas-child' ); ?>">
		<?php foreach ( $cfg['cats'] as $cat ) : ?>
			<a class="rc-home-cat" href="<?php echo esc_url( rc_home_cat_link( $cat['slug'], $cat['fallback'] ) ); ?>">
				<?php echo rc_home_img( $cat['id'], '', $cat['alt'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
		<?php endforeach; ?>
	</section>

	<section class="rc-home-products rc-wrap">
		<header class="rc-home-heading">
			<span><?php esc_html_e( 'Top Products', 'mixtas-child' ); ?></span>
			<h2><?php esc_html_e( 'Best Selling', 'mixtas-child' ); ?></h2>
		</header>
		<?php
		$best = rc_home_best_seller_ids( 4 );
		if ( $best ) {
			echo do_shortcode( '[products ids="' . esc_attr( implode( ',', $best ) ) . '" columns="4" orderby="post__in"]' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</section>

	<section class="rc-home-products rc-wrap">
		<header class="rc-home-heading">
			<span><?php esc_html_e( 'Latest Products', 'mixtas-child' ); ?></span>
			<h2><?php esc_html_e( 'New Arrivals', 'mixtas-child' ); ?></h2>
		</header>
		<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<p class="rc-home-more"><a href="<?php echo esc_url( home_url( '/new-arrivals/' ) ); ?>"><?php esc_html_e( 'View all new arrivals', 'mixtas-child' ); ?></a></p>
	</section>

	<section class="rc-home-features rc-wrap" aria-label="<?php esc_attr_e( 'Why shop with us', 'mixtas-child' ); ?>">
		<?php
		$features = array(
			array( 'truck', 'Free Shipping', 'Free worldwide shipping on orders over $150.' ),
			array( 'return', 'Easy Returns', '30-day returns and exchanges, no hassle.' ),
			array( 'chat', 'Online Support', 'Questions about sizing or orders? We are here to help.' ),
			array( 'shield', 'Genuine Leather', '100% genuine, handcrafted leather in every jacket.' ),
		);
		$icons = array(
			'truck'  => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="17" r="1.6"/><circle cx="17" cy="17" r="1.6"/>',
			'return' => '<path d="M4 12a8 8 0 1 0 2.4-5.7"/><polyline points="4 4 4 9 9 9"/>',
			'chat'   => '<path d="M4 5h16v11H8l-4 4z"/><path d="M8 10h8M8 13h5"/>',
			'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><polyline points="9 12 11 14 15 10"/>',
		);
		foreach ( $features as $f ) :
			?>
			<div class="rc-home-feature">
				<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?php echo $icons[ $f[0] ]; // phpcs:ignore WordPress.Security.EscapeOutput ?></svg>
				<h3><?php echo esc_html( $f[1] ); ?></h3>
				<p><?php echo esc_html( $f[2] ); ?></p>
			</div>
		<?php endforeach; ?>
	</section>

</main>
<?php
get_footer();
