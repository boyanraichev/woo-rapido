<?php
/*
* Woo Rapido
*
* INCLUDES/CHECKOUT.PHP
* Handles the checkout options for this shipping method
*/


if (!defined('ABSPATH')) die;

class Wrapido_Checkout {

	/** @var The single instance of the class */
	private static $_instance = null;	
	
	// Don't load more than one instance of the class
	public static function get_instance() {
		if ( null == self::$_instance ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
	/**
     * Constructor
     */
    public function __construct() {

		// hide some fields & add custom fields
        add_filter('woocommerce_checkout_fields', [ $this, 'shipping_fields' ], 20, 1 );
        add_filter( 'woocommerce_default_address_fields' , [ $this, 'address_fields' ], 20, 1  );
        // add hidden fields here 
        add_action('woocommerce_after_checkout_billing_form', [ $this, 'hidden_fields' ]);
		// validate custom fields
        add_action('woocommerce_checkout_process', [ $this, 'process_fields' ]);
		// save fields
        add_action('woocommerce_checkout_update_order_meta', [ $this, 'save_fields' ]);       
        
        //add_action('woocommerce_after_checkout_validation', [$this,'forceReload']);     

	}
	
	public function forceReload() { 
		global $woocommerce;
        WC()->session->set( 'reload_checkout', true );
	}
	/**
     * Set some rules
     */
    public function setRules($office='yes',$door='yes') {
    	$this->office = ($office=='yes' ? true : false);
    	$this->door = ($door=='yes' ? true : false);
    }
	
	/**
     * Filter billing fields
     * @access public
     * @return array $address_fields     
     */
	public function shipping_fields($fields) {
		
		global $woocommerce;
        $customer_method = WC()->session->get( 'chosen_shipping_methods' );
        if(substr($customer_method[0], 0, 6) == 'rapido') {
        	//unset shipping fields
        		unset($fields['shipping']['shipping_first_name']);
	        	unset($fields['shipping']['shipping_last_name']);
	        	unset($fields['shipping']['shipping_company']);
	        	unset($fields['shipping']['shipping_country']);
				unset($fields['shipping']['shipping_city']);
	        	unset($fields['shipping']['shipping_state']);
	        	unset($fields['shipping']['shipping_postcode']);
	        	unset($fields['shipping']['shipping_address_1']);
	        	unset($fields['shipping']['shipping_address_2']);
	        
	        // BILLING FIELDS
	        	
	        $new_fields_billing = [];
	        $new_fields_billing['billing_first_name']	= $fields['billing']['billing_first_name'];
	        $new_fields_billing['billing_last_name']	= $fields['billing']['billing_last_name'];
	        $new_fields_billing['billing_company']	= $fields['billing']['billing_company'];
	        $new_fields_billing['billing_email']	= $fields['billing']['billing_email'];
	        $new_fields_billing['billing_phone']	= $fields['billing']['billing_phone'];
	        $new_fields_billing['billing_country']	= $fields['billing']['billing_country'];
	        $new_fields_billing['billing_city']	= $fields['billing']['billing_city'];
	        	$new_fields_billing['billing_city']['class'][] = 'rapidoauto-city';
	        	$new_fields_billing['billing_city']['description'] = __('Start typing and choose from the dropdown list. If your city does not show up in the list, enter it manually.','wrapido');
	        $new_fields_billing['billing_postcode']	= $fields['billing']['billing_postcode'];	        	
            	$new_fields_billing['billing_postcode']['class'][0] = 'form-row-wide';
            	$new_fields_billing['billing_postcode']['class'][] = 'rapidoauto-postcode';
            	$new_fields_billing['billing_postcode']['required'] = false;
        	$new_fields_billing['rapido_office'] = [
            		'type' => 'select',	
	            	'label' => __('Office','wrapido'),
	            	'description' => __('Do not select an office if you would like your order to be delivered to your address.','wrapido'),
	            	'required' => false,
	            	'class' => ['form-row-wide'],
	            	'options' => [ '0' => __('None found...','wrapido') ]
	            ];
	        $new_fields_billing['billing_address_1']	= $fields['billing']['billing_address_1'];
	        	$new_fields_billing['billing_address_1']['label'] = __('Street / Quarter','wrapido');
				$new_fields_billing['billing_address_1']['description'] = __('Start typing your street or quarter name here. If you do not find it in the dropdown list, enter it manually.','wrapido');
				$new_fields_billing['billing_address_1']['placeholder'] = __('Start typing and choose from the list','wrapido');
				$new_fields_billing['billing_address_1']['class'][] = 'rapidoauto-street';
	            $new_fields_billing['billing_address_1']['required'] = false;
				unset($new_fields_billing['billing_address_1']['validate']);
			$new_fields_billing['billing_address_2']	= $fields['billing']['billing_address_2'];
				$new_fields_billing['billing_address_2']['label'] = __('Number','wrapido');
	            $new_fields_billing['billing_address_2']['class'] = ['form-row-first'];
	            $new_fields_billing['billing_address_2']['placeholder'] = __('Street/Building #','wrapido');
	            $new_fields_billing['billing_address_2']['required'] = false;
	            $new_fields_billing['billing_address_2']['custom_attributes'] = [ 'maxlength' => '5' ];
			$new_fields_billing['rapido_ent'] = [
					'type' => 'text',
	            	'label' => __('Entrance','wrapido'),
	            	'required' => false,
	            	'class' => ['form-row-last'],
	            	'custom_attributes' => [ 'maxlength' => '6' ],
	            ];
            $new_fields_billing['rapido_fl'] = [
					'type' => 'text',            
	            	'label' => __('Floor','wrapido'),
	            	'required' => false,
	            	'class' => ['form-row-first'],
	            	'custom_attributes' => [ 'maxlength' => '10' ],
	            ];
           	$new_fields_billing['rapido_ap'] = [
					'type' => 'text',            
	            	'label' => __('Apartment','wrapido'),
	            	'required' => false,
	            	'class' => ['form-row-last'],
	            	'custom_attributes' => [ 'maxlength' => '10' ],
	            ];
			

            
            // add street/kvartal field
            
            
            $fields['billing'] = $new_fields_billing;
        }

        return $fields;

	}
	
	public function address_fields( $address_fields ) {
			
		global $woocommerce;
        $customer_method = WC()->session->get( 'chosen_shipping_methods' ); 
        if(substr($customer_method[0], 0, 6) == 'rapido') {
			
			unset($address_fields['state']);
			
			$address_fields['postcode']['required'] = false;
				unset($address_fields['postcode']['validate']);
			
			$address_fields['address_1']['label'] = __('Street / Quarter','wrapido');
			$address_fields['address_1']['placeholder'] = __('Start typing and choose from the list','wrapido');
						
			$address_fields['address_2']['label'] = __('Number','wrapido');
			$address_fields['address_2']['placeholder'] = __('Street or building number','wrapido');		
			$address_fields['address_2']['required'] = false;
		}		
		return $address_fields;
    }
	
	/**
     * Filter billing fields
     * @access public
     * @return array $address_fields     
     */
	public function hidden_fields($checkout) {
		echo '<input type="hidden" id="rapidoauto-city-id" name="rapido_city_id" value="" >';
		echo '<input type="hidden" id="rapidoauto-kvartal-id" name="rapido_kvartal_id" value="" >';
		echo '<input type="hidden" id="rapidoauto-street-id" name="rapido_street_id" value="" >';
		global $woocommerce;
        $customer_method = WC()->session->get( 'chosen_shipping_methods' ); 
        if(substr($customer_method[0], 0, 6) == 'rapido') {
			echo '<input type="hidden" name="rapido_test" value="1" >';
		}
		wp_nonce_field( 'wrapido-nonce','_wrapido_nonce', false );
	}
	
	/**
     * Process custom fields
     * @access public
     * @return void     
     */
	public function process_fields() {
		if ( !isset($_POST['rapido_test']) ) {
		    wc_add_notice( __( 'You need to fill in the special address fields for this delivery method.' ), 'error' );
		    WC()->session->set( 'reload_checkout', true );
		}
		if ( empty($_POST['rapido_office']) AND empty($_POST['billing_address_1'])) {
	        wc_add_notice( __( 'You need to choose an office or enter a street address.' ), 'error' );
		}
		if ( !empty($_POST['billing_address_1']) AND empty($_POST['billing_address_2'])) {
			wc_add_notice( __( 'You need to enter a street/building number.' ), 'error' );
		}
	}
	
	/**
     * Save custom fields
     * @access public
     * @return void     
     */
	public function save_fields($order_id) {
		if ( ! empty( $_POST['rapido_office'] ) ) {
	        update_post_meta( $order_id, 'rapido_office', sanitize_text_field( $_POST['rapido_office'] ) );
	    }
	    if ( ! empty( $_POST['rapido_ent'] ) ) {
	        update_post_meta( $order_id, 'rapido_ent', sanitize_text_field( $_POST['rapido_ent'] ) );
	    }
	    if ( ! empty( $_POST['rapido_fl'] ) ) {
	        update_post_meta( $order_id, 'rapido_fl', sanitize_text_field( $_POST['rapido_fl'] ) );
	    }
	    if ( ! empty( $_POST['rapido_ap'] ) ) {
	        update_post_meta( $order_id, 'rapido_ap', sanitize_text_field( $_POST['rapido_ap'] ) );
	    }
	    if ( ! empty( $_POST['rapido_city_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_city_id', sanitize_text_field( $_POST['rapido_city_id'] ) );
	    }
	    if ( ! empty( $_POST['rapido_kvartal_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_kvartal_id', sanitize_text_field( $_POST['rapido_kvartal_id'] ) );
	    }
	    if ( ! empty( $_POST['rapido_street_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_street_id', sanitize_text_field( $_POST['rapido_street_id'] ) );
	    }
	    global $woocommerce;
    	$weight = $woocommerce->cart->cart_contents_weight;
    	update_post_meta( $order_id, '_shipping_weight', $weight );
	}
}