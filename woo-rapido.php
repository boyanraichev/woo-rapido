<?php
/*
Plugin Name: Woo Rapido
Plugin URI: http://talkingaboutthis.eu
Description: Rapido Courier integration for WooCommerce
Version: 1.2
Author: Boyan Raichev
Author URI: http://talkingaboutthis.eu
Text Domain: wrapido
Domain Path: /languages
License: No redistribution allowed. 
*/

/*  Copyright 2014  Boyan Raichev  (http://talkingaboutthis.eu) */


if (!defined('ABSPATH')) die;

define('WRAPIDO_DIR', plugin_dir_path( __FILE__ ));
define('WRAPIDO_DIR_URL', plugin_dir_url( __FILE__ ));
define('WRAPIDO_VERSION', '1.2');

// Set up for multilanguage; domain is 'wrapido'
function wrapido_load_textdomain() {
	load_plugin_textdomain('wrapido', false, dirname(plugin_basename(__FILE__)). '/languages/' );
}
add_action( 'plugins_loaded', 'wrapido_load_textdomain' );

/* INCLUDE FILES */

// Class Autoloader
function wrapido_autoloader( $class_name ) {
	
	// autoload only classes starting with Kosher
    if ( 0 !== strpos( $class_name, 'Wrapido' ) ) {
        return;
    }
	
	$class_name = str_replace(
        array( '_' ), // Remove prefix | Underscores 
        array( '/' ),
        strtolower( $class_name ) // lowercase
    );
    
    $class_name = str_replace(
        array( 'wrapido' ), // Remove prefix | Underscores 
        array( 'includes' ),
        strtolower( $class_name ) // lowercase
    );
	
    // Compile our path from the current location
    $file = WRAPIDO_DIR . $class_name .'.php';

    // If a file is found
    if ( file_exists( $file ) ) { 
    	require_once( $file );
    }	   
}
spl_autoload_register( 'wrapido_autoloader' );

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	$init = new Wrapido_Init();
	
	function wrapido_shipping_init() {

       //$rapido = new Wrapido_Method(); 

    }
 
    add_action( 'woocommerce_shipping_init', 'wrapido_shipping_init' );
 
    function wrapido_shipping_method( $methods ) {
        $methods['rapido'] = 'Wrapido_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'wrapido_shipping_method' );
    
    $checkout = Wrapido_Checkout::get_instance();
    
    if (is_admin()) {
    	$admin = Wrapido_Admin_Fulfilment::get_instance();
    }

	
}
	
// ACTIVATION / DEACTIVATION / UNINSTALL HOOKS

register_activation_hook( __FILE__, 'wrapido_activate' );
register_deactivation_hook( __FILE__, 'wrapido_deactivate' );
register_uninstall_hook( __FILE__, 'wrapido_uninstall' );
function wrapido_activate() {
	$install = Wrapido_Admin_Install::activate();
}
function wrapido_deactivate() {
	$install = Wrapido_Admin_Install::deactivate();
}
function wrapido_uninstall() {
	$install = Wrapido_Admin_Install::uninstall();
}