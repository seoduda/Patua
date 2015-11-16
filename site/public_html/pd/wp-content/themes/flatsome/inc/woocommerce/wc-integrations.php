<?php
/**
 * Integration logic for WooCommerce extensions
 *
 * @package flatsome
 */


function flatsome_woocommerce_integrations_scripts() {
	if ( is_extension_activated( 'woocommerce_booking' ) ) {
	  wp_enqueue_style( 'flatsome-woocommerce-bookings-style', get_template_directory_uri() . '/inc/woocommerce/integrations/bookings.css', 'flatsome-woocommerce-style' );
	}
	if ( is_extension_activated( 'Fancy_Product_Designer' ) ) {
	  wp_enqueue_style( 'flatsome-fancy-product-designer', get_template_directory_uri() . '/inc/woocommerce/integrations/product-designer.css', 'flatsome-woocommerce-style' );
	}
}

add_action( 'wp_enqueue_scripts', 'flatsome_woocommerce_integrations_scripts' );



/* WooCommerce Ajax Navigation */
add_filter('_ajax_layered_nav_containers', 'ux_add_custom_container');
	function ux_add_custom_container($containers){
	$containers[] = '.woocommerce-pagination';
	$containers[] = '.woocommerce-result-count';
	return $containers;
}

/* Yith Ajax Navigation */
add_filter('sod_ajax_layered_nav_product_container', 'aln_product_container');
	function aln_product_container($product_container){
	//Enter either the class or id of the container that holds your products
	return '.products';
}

