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
