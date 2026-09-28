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
