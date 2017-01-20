<?php
/*
* Woo Rapido
*
* INCLUDES/AJAX.PHP
* Handles the ajax calls
*/


if (!defined('ABSPATH')) die;

class Wrapido_Ajax {

	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function get_instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
    
    }
    
	
	/**
     * Searches in the Cities Table
     */
    public function searchCity() { 
    	check_ajax_referer( 'wrapido-nonce','nonce' );    
    	$results = [];
    	
    	$term = sanitize_text_field( $_GET['term'] );

    	$db = Wrapido_DB::get_instance();
    	$cities = $db->searchCity($term);
    	
    	foreach ($cities as $city) {
    		$results[] = [ 'id' => $city['id'], 'value' => $city['name'], 'postcode' => $city['postcode'] ];
    	}
    	
    	echo json_encode( $results );
    	die(); 
    }
    
    
	/**
     * Searches in the Cities Table by postcode
     */
    public function searchPostcode() {
    	check_ajax_referer( 'wrapido-nonce','nonce' );    
    	$results = [];
    	
    	$term = sanitize_text_field( $_GET['term'] );
    	
    	$db = Wrapido_DB::get_instance();
    	$cities = $db->searchPostcode($term);
  	
    	foreach ($cities as $city) {
    		$results[] = [ 'id' => $city['id'], 'value' => $city['postcode'], 'city' => $city['name'] ];
    	}
    	
    	echo json_encode( $results );
    	die(); 
    }
    
    /**
     * Searches in the Cities Table by postcode
     */
    public function searchOffice() {
    	check_ajax_referer( 'wrapido-nonce','nonce' );    
    	$results = '';
    	
    	$city = intval( $_GET['city'] );
    	$selected = sanitize_text_field( $_GET['selected'] );
    	if ($city) {
	    	$rapido = Wrapido_Rapido::get_instance();
	    	$rapido->setUpSoapFromDB();
	    	$offices = $rapido->getOffices($city);
	    	if ($offices) {
	    		foreach ($offices as $office) {
	    			$results .= '<option value="'.$office['DATA'].'" '.selected($selected,$office['DATA'],false).'>'.$office['LABEL'].'</option>';
	    		}
	    	} else {
	    		$results .= '<option value="0">'.__('None found...','wrapido').'</option>';
	    	}
    	}
    	echo json_encode( $results );
    	die(); 
    }

    
	/**
     * Searches in the Streets Table
     */
    public function searchStreet() {
    	check_ajax_referer( 'wrapido-nonce','nonce' );
    	
    	$results = [];
    	
    	$term = sanitize_text_field( $_GET['term'] );
    	$city_id = intval( $_GET['city'] );
    	
    	$db = Wrapido_DB::get_instance();
    	
    	$streets = $db->searchStreet($term,$city_id);
    	if ($streets) {
	    	foreach ($streets as $street) {
	    		$results[] = [ 'id' => $street['id'], 'value' => $street['fullname'], 'kvartal' => '' ];
	    	}
	    } 
	    
	    $kvartals = $db->searchKvartal($term,$city_id);
    	if ($kvartals) {
	    	foreach ($kvartals as $kvartal) {
	    		$results[] = [ 'id' => '', 'value' => $kvartal['name'], 'kvartal' => $kvartal['id'] ];
	    	}
	    } 
	    
	    if (empty($results)) { 
	    	$results[] = [ 'id' => '', 'value' => __('No streets found','wrapido'), 'kvartal' => '' ];
	    }
    	
    	echo json_encode( $results );
    	die();
    }
    
    /**
     * Creates a shipment
     */
    public function createShipment() {
    	
    	check_ajax_referer( 'wrapido-nonce','_wrapido_nonce' );
		
		if (!current_user_can('manage_woocommerce')) {
			die('not authorised');
		}
		
		$result = 'error';
		$message = '';
		
		$rapido = Wrapido_Rapido::get_instance();
		$rapido->setUpSoapFromDB();

		$address = array();
		$address['receiver'] = sanitize_text_field($_GET['_billing_first_name']).' '.sanitize_text_field($_GET['_billing_last_name']);
		$address['country_id'] = ( isset($_GET['country_id']) ? sanitize_text_field($_GET['country_id']) : 100);
		$address['city'] = ( isset($_GET['_billing_city']) ? sanitize_text_field($_GET['_billing_city']) : '');
		$address['city_id'] = ( isset($_GET['rapido_city_id']) ? intval($_GET['rapido_city_id']) : '');
		$address['zip'] = ( !empty($_GET['_billing_postcode']) ? sanitize_text_field($_GET['_billing_postcode']) : 1000);
		
		if ( !empty($_GET['rapido_office']) ) {
			$address['office'] = ( isset($_GET['rapido_office']) ? sanitize_text_field($_GET['rapido_office']) : '');
		} else {
			if ( !empty($_GET['rapido_street_id']) ) {
				$address['street'] = ( isset($_GET['_billing_address_1']) ? sanitize_text_field($_GET['_billing_address_1']) : '');
				$address['street_id'] = ( isset($_GET['rapido_street_id']) ? intval($_GET['rapido_street_id']) : '');
				$address['street_type'] = 1;
				$address['street_no'] = ( isset($_GET['_billing_address_2']) ? sanitize_text_field($_GET['_billing_address_2']) : '');
			} elseif ( !empty($_GET['rapido_kvartal_id']) ) {
				$address['street'] = ( isset($_GET['_billing_address_1']) ? sanitize_text_field($_GET['_billing_address_1']) : '');
				$address['street_id'] = ( isset($_GET['rapido_kvartal_id']) ? intval($_GET['rapido_kvartal_id']) : '');
				$address['street_type'] = 2;
				$address['building_no'] = ( isset($_GET['_billing_address_2']) ? sanitize_text_field($_GET['_billing_address_2']) : '');
			} else {
				$address['info'] = ( isset($_GET['_billing_address_1']) ? sanitize_text_field($_GET['_billing_address_1']) : '');
			}
			$address['building_en'] = ( isset($_GET['rapido_en']) ? sanitize_text_field($_GET['rapido_en']) : '');
			$address['building_fl'] = ( isset($_GET['rapido_fl']) ? sanitize_text_field($_GET['rapido_fl']) : '');		
			$address['building_ap'] = ( isset($_GET['rapido_ap']) ? sanitize_text_field($_GET['rapido_ap']) : '');					
		}
		
		$address['phone'] = ( isset($_GET['_billing_phone']) ? sanitize_text_field($_GET['_billing_phone']) : '');		
		$address['ref'] = ( isset($_GET['rapido_order']) ? intval($_GET['rapido_order']) : '');
		
		
		$weight = ( !empty($_GET['rapido_weight']) ? floatval($_GET['rapido_weight']) : 1);
		$due = ( !empty($_GET['rapido_cod']) ? number_format($_GET['rapido_cod'],2,'.','') : 0 ); 
		$fragile = ( !empty($_GET['rapido_fragile']) ? 1 : 0);
		$insurance = ( !empty($_GET['rapido_insurance']) ? $due : '');
		$content = ( !empty($_GET['rapido_descr']) ? sanitize_text_field($_GET['rapido_descr']) : 'Стоки');

		$result = $rapido->createShipment($address,$weight,$due,$insurance,$fragile,$content);

		if ( !empty($result) AND is_array($result) AND empty($result['ERROR']) AND isset($result['TOVARITELNICA']) ) {
			update_post_meta( $address['ref'], 'rapido_tracking', $result['TOVARITELNICA'] );
			$result = $result['TOVARITELNICA'];
		}
		
		echo json_encode( ['tracking' => $result] );
    	die();
    }
    
    
    /**
     * Get the shipment PDF docs
     */
    public function printShipment() {
    
    	check_ajax_referer( 'wrapido-nonce','nonce' );
		
		die();    
    }
}