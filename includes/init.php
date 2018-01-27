<?php
/*
* Woo Rapido
*
* INCLUDES/INIT.PHP
* Initializes the plugin
*/


if (!defined('ABSPATH')) die;

class Wrapido_Init {

	/**
     * Constructor
     */
    public function __construct() {
    	
    	$this->registerTables();
    	
    	add_action( 'admin_init', [ $this, 'adminInit' ] );
    	add_action( 'init', [ $this, 'rapidoInit' ] );
    	
    }

 	/* 
 	* Admin Init functions
 	*/
 	public function adminInit() {
 		
 		// own js
		wp_register_script('wrapidoadmin', WRAPIDO_DIR_URL . 'assets/js/wrapidoadmin.js', [ 'jquery', 'jquery-ui-autocomplete' ], WRAPIDO_VERSION, true ); 
		wp_enqueue_script('wrapidoadmin');
		
		wp_register_style('rapido',  WRAPIDO_DIR_URL . 'assets/css/rapidoadmin.css' , WRAPIDO_VERSION );
		wp_enqueue_style( 'rapido' );
		
		if (current_user_can('manage_woocommerce')) {
		
			if (isset($_GET['rapidoshipment'])) {
				$tracking = intval($_GET['rapidoshipment']);
				$rapido = Wrapido_Rapido::get_instance();
				$rapido->setUpSoapFromDB();
				$rapido->printShipment($tracking);
			}
			
		}
		
 	}
 	
 	/* 
 	* Frontend Init functions
 	*/
 	public function rapidoInit() {
 		
 		wp_enqueue_script('jquery-ui-autocomplete');
 		
 		// own js
		wp_register_script('wrapido', WRAPIDO_DIR_URL . 'assets/js/wrapido.js', [ 'jquery', 'jquery-ui-autocomplete' ], WRAPIDO_VERSION, true ); 
		wp_enqueue_script('wrapido');
		
		wp_register_style( 'jquery-ui-styles','//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui-styles' );
		
		if (!is_admin()) {
			add_action('wp_head',[$this,'cssSpinner']);
		}
		// LOCALIZE SCRIPTS
		
	    wp_localize_script( 
	    	'wrapido',
	    	'ajax_wrapido_object', 
	    	[ 
	    		'ajaxurl' => admin_url( 'admin-ajax.php' ), 
	    		'loadingmessage' => __('Please, wait...','wrapido'),
	    		'error' => __('Could not create shipment. Check the fields and try again.','wrapido'),
	    		'missingcityid' => __('Missing CITY ID. Please, enter the city again and choose from the dropdown list!','wrapido'),
	    		'missingcountryid' => __('Missing Country ID. Please, check user country and update order!','wrapido'),
	    		'printshipment' => __('Print Shipment','wrapido'),
	    	]
		);
		
		// AJAX CALLS
		$this->ajaxActions();
		
 	}
 	
 	public function cssSpinner() {
 		echo '<style> input[type="text"].ui-autocomplete-loading { background: url(\''.admin_url('images/loading.gif').'\') no-repeat right center; } </style>';
 	}
 	
	/* 
 	* Register DB tables
 	*/
 	public function registerTables() {
	    global $wpdb;
	    $wpdb->rapido_countries = "{$wpdb->prefix}rapido_countries";
	    $wpdb->rapido_cities = "{$wpdb->prefix}rapido_cities";
	    $wpdb->rapido_kvartal = "{$wpdb->prefix}rapido_kvartal";
	    $wpdb->rapido_streets = "{$wpdb->prefix}rapido_streets"; 
	}
	
	/*
	* Registers Ajax Actions
	*/
	private function ajaxActions() {
		// City search
		add_action( 'wp_ajax_rapidocity', ['Wrapido_Ajax','searchCity'] );
		add_action( 'wp_ajax_nopriv_rapidocity', ['Wrapido_Ajax','searchCity'] );
		// Postcode search
		add_action( 'wp_ajax_rapidopost', ['Wrapido_Ajax','searchPostcode'] );
		add_action( 'wp_ajax_nopriv_rapidopost', ['Wrapido_Ajax','searchPostcode'] );
		// Street search
		add_action( 'wp_ajax_rapidostreet', ['Wrapido_Ajax','searchStreet'] );
		add_action( 'wp_ajax_nopriv_rapidostreet', ['Wrapido_Ajax','searchStreet'] );
		// Office search
		add_action( 'wp_ajax_rapidooffice', ['Wrapido_Ajax','searchOffice'] );
		add_action( 'wp_ajax_nopriv_rapidooffice', ['Wrapido_Ajax','searchOffice'] );		
		// create shipment
		add_action( 'wp_ajax_rapidocreate', ['Wrapido_Ajax','createShipment'] );
		// print shipment
		add_action( 'wp_ajax_rapidoprint', ['Wrapido_Ajax','printShipment'] );		
	}
}
