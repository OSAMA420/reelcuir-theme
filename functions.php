<?php
/**
 * Mixtas Child theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Child theme ki CSS/JS parent theme ke baad load karo.
 */
add_action( 'wp_enqueue_scripts', function () {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	wp_enqueue_style(
		'mixtas-child',
		$uri . '/assets/css/custom.css',
		array( 'nova-mixtas-styles' ),
		filemtime( $dir . '/assets/css/custom.css' )
	);

	wp_enqueue_script(
		'mixtas-child',
		$uri . '/assets/js/custom.js',
		array( 'jquery' ),
		filemtime( $dir . '/assets/js/custom.js' ),
		true
	);
}, 20 );

/**
 * Header menu: khaali product categories (0 published products) ki list JS ko do,
 * taake custom.js header se un ke links chhupa de. Product aate hi link wapas aa jata hai.
 */
add_action( 'wp_enqueue_scripts', function () {
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'fields'     => 'all',
	) );
	if ( is_wp_error( $terms ) ) {
		return;
	}
	$empty = array();
	foreach ( $terms as $term ) {
		if ( 0 === (int) $term->count ) {
			$empty[] = $term->slug;
		}
	}
	wp_add_inline_script( 'mixtas-child', 'window.rcEmptyCats = ' . wp_json_encode( $empty ) . ';', 'before' );
}, 25 );

/**
 * Top promo banner: har page par header se upar (theme ka wp_body_open, site-wrapper se pehle).
 * Scroll par baaki header ke saath upar chala jata hai; Elementor ka menu (3565a34) sticky rehta hai.
 * Images Media Library mein hain: desktop 1920x220 (ID 3078), mobile 800x300 (ID 3079).
 * Banner badalna ho to sirf ye IDs aur link badlein.
 */
define( 'RC_TOP_BANNER', array(
	'desktop' => 3078,
	'mobile'  => 3079,
	'link'    => '/new-arrivals/',
	'alt'     => 'New season arrivals - authentic leather jackets for men and women',
) );

add_action( 'wp_body_open', function () {
	if ( is_admin() ) {
		return;
	}
	$desktop = wp_get_attachment_image_url( RC_TOP_BANNER['desktop'], 'full' );
	$mobile  = wp_get_attachment_image_url( RC_TOP_BANNER['mobile'], 'full' );
	if ( ! $desktop ) {
		return;
	}
	printf(
		'<a class="rc-top-banner" href="%1$s"><picture>%2$s<img src="%3$s" alt="%4$s" width="1920" height="220" fetchpriority="high"></picture></a>',
		esc_url( home_url( RC_TOP_BANNER['link'] ) ),
		$mobile ? '<source media="(max-width: 767px)" srcset="' . esc_url( $mobile ) . '" width="800" height="300">' : '',
		esc_url( $desktop ),
		esc_attr( RC_TOP_BANNER['alt'] )
	);
} );

/**
 * New Arrivals page: discount (sale) wale products yahan nahi, woh Sale page par hain.
 * Page ke shortcode mein class="rc-new-arrivals" laga hai.
 */
add_filter( 'woocommerce_shortcode_products_query', function ( $query_args, $attributes ) {
	if ( empty( $attributes['class'] ) || false === strpos( $attributes['class'], 'rc-new-arrivals' ) ) {
		return $query_args;
	}
	$on_sale = wc_get_product_ids_on_sale();
	if ( $on_sale ) {
		$exclude = isset( $query_args['post__not_in'] ) ? (array) $query_args['post__not_in'] : array();
		$query_args['post__not_in'] = array_merge( $exclude, $on_sale );
	}
	return $query_args;
}, 10, 2 );

/**
 * Cart badge: CDN cached page mein purana count hota hai. WooCommerce cart-fragments
 * har page par load karo taake header ka count (.js_count_bag_item) asli cart se update ho.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'WC' ) && wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}, 30 );

/**
 * Cart/Checkout speed: ye pages WooCommerce blocks se bante hain, un par slider,
 * Instagram, contact form, mega menu aur classic cart ki files ka koi kaam nahi.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_cart' ) || ! ( is_cart() || is_checkout() ) ) {
		return;
	}

	$styles = array(
		'sbi_styles',                   // Instagram Feed
		'sb-elementor-shared-style',    // Instagram Feed
		'contact-form-7',
		'sr7css',                       // Slider Revolution
		'megamenu',
	);
	$scripts = array(
		'tp-tools',                     // Slider Revolution
		'sr7',                          // Slider Revolution
		'swv',                          // Contact Form 7
		'contact-form-7',
		'megamenu',
		'wc-cart',                      // classic cart (block cart ko nahi chahiye)
		'wc-country-select',
		'wc-address-i18n',
		'selectWoo',
	);

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
	}
	foreach ( $scripts as $handle ) {
		wp_dequeue_script( $handle );
	}
}, 999 );
