<?php
/**
 * Homepage (static front page). front-page.php hamesha front page ke liye use hota hai, is liye
 * Elementor wala "Home v4" (page 1158) ab nahi dikhta; wapas chahiye to ye file hata do.
 * Sections: hero, 4 category tiles, Best Selling, New Arrivals, phir Home v4 ke purane Elementor sections.
 * Upar ka Halloween ticker functions.php (RC_HOME_PAGE['ticker_*']) se aata hai (sirf homepage par).
 * Images Media Library IDs se: RC_HOME_PAGE constant (functions.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg = RC_HOME_PAGE;

const RC_HOME_ELEMENTOR_PAGE = 1158;      // purana Elementor homepage
// Home v4 ke upar wale containers (rev slider, 4 cards, Best Selling, category, New arrivals): inki jagah hamare sections hain
const RC_HOME_ELEMENTOR_SKIP = array( 'f975fdb', '8586ff5', 'e4b3448', '13d3d5b', 'd51c6f8' );

/**
 * Elementor HTML se diye gaye data-id wale <div> (poora, andar ke saath) hata do.
 * Containers hamesha <div> hote hain, is liye <div ... </div> ki gehrai gin kar band hone ki jagah dhoondte hain.
 */
function rc_home_strip_containers( $html, $ids ) {
	foreach ( $ids as $id ) {
		if ( ! preg_match( '/<div\b[^>]*\bdata-id="' . preg_quote( $id, '/' ) . '"[^>]*>/', $html, $m, PREG_OFFSET_CAPTURE ) ) {
			continue;
		}
		$start = $m[0][1];
		$pos   = $start + strlen( $m[0][0] );
		$depth = 1;
		while ( $depth > 0 && preg_match( '#<div\b|</div\s*>#i', $html, $t, PREG_OFFSET_CAPTURE, $pos ) ) {
			$depth += ( '/' === $t[0][0][1] ) ? -1 : 1;
			$pos    = $t[0][1] + strlen( $t[0][0] );
		}
		if ( 0 === $depth ) {
			$html = substr( $html, 0, $start ) . substr( $html, $pos );
		}
	}
	return $html;
}

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

	<?php
	// New Arrivals ke baad purane Elementor homepage (Home v4, page 1158) ke sections waise hi:
	// 2 collection cards, category slider, reviews, Discover banner, community aur features.
	// Upar wale sections (slider, tiles, Best Selling, New Arrivals) hamare apne hain, is liye
	// unke Elementor containers (RC_HOME_ELEMENTOR_SKIP) bane hue HTML se kaat diye jaate hain.
	// (should_render / builder_content_data filters kaam nahi karte: Elementor page ka HTML cache se deta hai.)
	if ( did_action( 'elementor/loaded' ) ) {
		// get_builder_content_for_display() yahan khaali deta hai: homepage khud page 1158 hai ("recursion" check).
		$rc_html = \Elementor\Plugin::instance()->frontend->get_builder_content( RC_HOME_ELEMENTOR_PAGE, true );
		echo rc_home_strip_containers( $rc_html, RC_HOME_ELEMENTOR_SKIP ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>

</main>
<?php
get_footer();
