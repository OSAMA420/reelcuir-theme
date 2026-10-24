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
 * Images Media Library mein hain: patli patti desktop 1920x180 (ID 3082), mobile 780x200 (ID 3083).
 * Editable source: Figma file "Réel Cuir – Top Banner".
 * (Purane: bada designer banner 3080/3081, temporary 3078/3079.) Banner badalna ho to sirf ye
 * IDs aur link badlein; size attachment se khud aata hai. 'mobile' => 0 ho to desktop image har jagah.
 */
define( 'RC_TOP_BANNER', array(
	'desktop' => 3082,
	'mobile'  => 3083,
	'link'    => '/new-arrivals/',
	'alt'     => 'New season arrivals - authentic handcrafted leather jackets for men and women',
) );

/**
 * Homepage (front-page.php). Images Media Library IDs se.
 * Homepage par upar ki patti ki jagah Halloween ticker aata hai (sirf yahin).
 */
define( 'RC_HOME_PAGE', array(
	'ticker_desktop' => 3090,
	'ticker_mobile'  => 3091,
	'ticker_link'    => '/shop/',
	'ticker_alt'     => 'Halloween Sale',
	'hero_desktop'   => 3088,
	'hero_mobile'    => 3089,
	'hero_link'      => '/shop/',
	'hero_alt'       => 'Halloween Sale - leather jackets',
	'cats'           => array(
		array( 'id' => 3084, 'slug' => 'men-leather-jackets', 'fallback' => '/product-category/men/', 'alt' => "Men's Leather Jacket" ),
		array( 'id' => 3087, 'slug' => 'women-leather-jackets', 'fallback' => '/product-category/women/', 'alt' => "Women's Leather Jacket" ),
		array( 'id' => 3085, 'slug' => 'shearling-leather-jackets', 'fallback' => '/shop/', 'alt' => 'Shearling Leather' ),
		array( 'id' => 3086, 'slug' => 'varsity-jackets', 'fallback' => '/shop/', 'alt' => 'Varsity Jacket' ),
	),
) );

// Elementor/theme homepage par apna template zabardasti lagate hain (page 1158 "fullwidth"),
// is liye sab se aakhir mein front-page.php ko hi chuno.
add_filter( 'template_include', function ( $template ) {
	if ( is_front_page() && ! is_home() ) {
		$ours = get_stylesheet_directory() . '/front-page.php';
		if ( file_exists( $ours ) ) {
			return $ours;
		}
	}
	return $template;
}, PHP_INT_MAX );

function rc_home_img( $id, $class = '', $alt = '', $size = 'full' ) {
	$src = $id ? wp_get_attachment_image_src( $id, $size ) : false;
	if ( ! $src ) {
		return '';
	}
	return sprintf(
		'<img class="%s" src="%s" width="%d" height="%d" alt="%s" loading="lazy" decoding="async">',
		esc_attr( $class ),
		esc_url( $src[0] ),
		(int) $src[1],
		(int) $src[2],
		esc_attr( $alt )
	);
}

/**
 * Best Selling ke product IDs. Asli bikri (total_sales > 0) ho to wahi; warna (abhi koi
 * order nahi) New Arrivals ke 4 se alag products, roz badalte hue, taake dono sections
 * ek jaise na hon.
 */
function rc_home_best_seller_ids( $limit = 4 ) {
	$top  = wc_get_products( array(
		'status'   => 'publish',
		'limit'    => $limit,
		'orderby'  => 'meta_value_num',
		'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
		'order'    => 'DESC',
		'return'   => 'ids',
	) );
	$sold = array_values( array_filter( $top, function ( $id ) {
		return (int) get_post_meta( $id, 'total_sales', true ) > 0;
	} ) );
	if ( count( $sold ) >= $limit ) {
		return $sold;
	}
	$newest = wc_get_products( array(
		'status'  => 'publish',
		'limit'   => $limit,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'ids',
	) );
	$pool = wc_get_products( array(
		'status'  => 'publish',
		'limit'   => -1,
		'exclude' => array_merge( $sold, $newest ),
		'return'  => 'ids',
	) );
	sort( $pool );
	mt_srand( (int) gmdate( 'Ymd' ) ); // roz naya, lekin din bhar ek jaisa (cache ke saath theek)
	shuffle( $pool );
	mt_srand();
	return array_slice( array_merge( $sold, $pool ), 0, $limit );
}

// Khaali category par tile ko kisi aur jagah bhejo (jaise abhi Shearling mein product nahi).
function rc_home_cat_link( $slug, $fallback ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) && (int) $term->count > 0 ) {
		return get_term_link( $term );
	}
	return home_url( $fallback );
}

add_action( 'wp_body_open', function () {
	if ( is_admin() ) {
		return;
	}
	$banner = RC_TOP_BANNER;
	// Halloween ticker ab har page par (user ka faisla); hatana ho to ticker_desktop 0 kar do, purani patti wapas aa jayegi.
	if ( RC_HOME_PAGE['ticker_desktop'] ) {
		$banner = array(
			'desktop' => RC_HOME_PAGE['ticker_desktop'],
			'mobile'  => RC_HOME_PAGE['ticker_mobile'],
			'link'    => RC_HOME_PAGE['ticker_link'],
			'alt'     => RC_HOME_PAGE['ticker_alt'],
		);
	}
	// Asal size attachment se lo (width/height layout shift rokte hain).
	$desktop = wp_get_attachment_image_src( $banner['desktop'], 'full' );
	$mobile  = $banner['mobile'] ? wp_get_attachment_image_src( $banner['mobile'], 'full' ) : false;
	if ( ! $desktop ) {
		return;
	}
	$source = '';
	if ( $mobile ) {
		$source = sprintf(
			'<source media="(max-width: 767px)" srcset="%s" width="%d" height="%d">',
			esc_url( $mobile[0] ),
			(int) $mobile[1],
			(int) $mobile[2]
		);
	}
	printf(
		'<a class="rc-top-banner" href="%1$s"><picture>%2$s<img src="%3$s" alt="%4$s" width="%5$d" height="%6$d" fetchpriority="high"></picture></a>',
		esc_url( home_url( $banner['link'] ) ),
		$source, // phpcs:ignore WordPress.Security.EscapeOutput -- upar escape hua
		esc_url( $desktop[0] ),
		esc_attr( $banner['alt'] ),
		(int) $desktop[1],
		(int) $desktop[2]
	);
} );

/**
 * /new-arrivals/ ko shop archive ka view banao, taake shop wala sidebar
 * (filters, sorting, pagination) yahan bhi chale. Shortcode pages par filters kaam nahi karte.
 *  - new:  sab se naye pehle, sale wale products nahi
 *  - sale: sirf sale wale products
 * Rewrite rules page slugs se pehle ('top') lagti hain. Purane pages (3071, 3072) fallback hain.
 * Is list mein tabdeeli ho to neeche rc_rewrite_version badlo taake rules dobara banein.
 */
define( 'RC_SHOP_VIEWS', array(
	'new-arrivals' => array( 'view' => 'new', 'title' => 'New Arrivals' ),
	'sale'         => array( 'view' => 'sale', 'title' => 'Sale' ),
) );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'rc_view';
	return $vars;
} );

add_action( 'init', function () {
	foreach ( RC_SHOP_VIEWS as $slug => $cfg ) {
		add_rewrite_rule( '^' . $slug . '/?$', 'index.php?post_type=product&rc_view=' . $cfg['view'], 'top' );
		add_rewrite_rule( '^' . $slug . '/page/([0-9]{1,})/?$', 'index.php?post_type=product&rc_view=' . $cfg['view'] . '&paged=$matches[1]', 'top' );
	}
	if ( get_option( 'rc_rewrite_version' ) !== '3' ) {
		flush_rewrite_rules( false );
		update_option( 'rc_rewrite_version', '3' );
	}
} );

function rc_shop_view() {
	$view = get_query_var( 'rc_view' );
	foreach ( RC_SHOP_VIEWS as $cfg ) {
		if ( $cfg['view'] === $view ) {
			return $cfg;
		}
	}
	return null;
}

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'product' ) ) {
		return;
	}
	$view = $q->get( 'rc_view' );
	if ( 'new' === $view ) {
		if ( empty( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
		}
		$on_sale = wc_get_product_ids_on_sale();
		if ( $on_sale ) {
			$q->set( 'post__not_in', array_merge( (array) $q->get( 'post__not_in' ), $on_sale ) );
		}
	} elseif ( 'sale' === $view ) {
		$on_sale = wc_get_product_ids_on_sale();
		$q->set( 'post__in', $on_sale ? $on_sale : array( 0 ) );
	}
}, 20 );

// Title, browser title aur breadcrumb mein "Shop" ki jagah view ka naam.
// Header ka title/breadcrumb/description Kitify widgets dete hain:
// get_the_archive_title(), post_type_archive_title filter, get_the_archive_description().
// Browser title All in One SEO deta hai (aioseo_title).
function rc_view_title( $title ) {
	$cfg = rc_shop_view();
	return $cfg ? $cfg['title'] : $title;
}
add_filter( 'woocommerce_page_title', 'rc_view_title' );
add_filter( 'get_the_archive_title', 'rc_view_title', 99 );
add_filter( 'post_type_archive_title', 'rc_view_title', 99 );
add_filter( 'get_the_archive_description', function ( $desc ) {
	return rc_shop_view() ? '' : $desc;
}, 99 );
add_filter( 'aioseo_title', function ( $title ) {
	$cfg = rc_shop_view();
	return $cfg ? $cfg['title'] . ' - ' . get_bloginfo( 'name' ) : $title;
} );
add_filter( 'document_title_parts', function ( $parts ) {
	$cfg = rc_shop_view();
	if ( $cfg ) {
		$parts['title'] = $cfg['title'];
	}
	return $parts;
} );
add_filter( 'woocommerce_get_breadcrumb', function ( $crumbs ) {
	$cfg = rc_shop_view();
	if ( $cfg && $crumbs ) {
		$crumbs[ count( $crumbs ) - 1 ] = array( $cfg['title'], '' );
	}
	return $crumbs;
} );
// Shop page ka description ("This is where you can browse...") in views par nahi.
add_action( 'woocommerce_archive_description', function () {
	if ( rc_shop_view() ) {
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	}
}, 1 );

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
 * WordPress " - " ko " – " aur "3-5" ko "3–5" bana deta hai (wptexturize), jo product naamon mein
 * AI jaisa lagta tha. Text jaisa likha hai waisa hi dikhao.
 */
add_filter( 'run_wptexturize', '__return_false' );

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
