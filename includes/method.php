<?php
/*
* Woo Rapido
*
* INCLUDES/METHOD.PHP
* Integrates the shipping method with Woo Commerce
*/


if (!defined('ABSPATH')) die;

class Wrapido_Method extends WC_Shipping_Method {
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct($instance_id = 0 ) {

        $this->id                 = 'rapido'; 
        $this->instance_id 		  = absint( $instance_id );
        $this->method_title       = __( 'Rapido Courier', 'wrapido' );  
        $this->method_description = __( 'Rapido Shipping Method', 'wrapido' ); 
		
		// $this->availability = null;
		// $this->countries = [];
		
		$this->supports = array( 'shipping-zones', 'settings', 'instance-settings', 'instance-settings-modal');
		
        $this->enabled = $this->get_option('enabled','yes');
        
        $this->test = $this->get_option('test','yes');
        $this->api_user = $this->get_option('api_user','');
        $this->api_pass = $this->get_option('api_pass','');
        
        $this->location = $this->get_option('location');
        $this->service1 = $this->get_option('service1');
        $this->service2 = $this->get_option('service2');
        
        $this->init();
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    public function init() {
    
        // Load the settings API
        $this->init_form_fields(); 
        
        $this->init_settings(); 
        $this->init_instance_settings();
	
		$this->title = $this->get_option('title',__('Rapido','wrapido'));
        $this->refresh = $this->get_option('loadrapidodb');

        $this->freeshipping = intval($this->get_instance_option('free'));
        $this->flatrate = intval($this->get_instance_option('flatrate'));
        
        $this->office = $this->get_instance_option('office');
        $this->door = $this->get_instance_option('door');
        if ($this->enabled == 'yes') {
        	$checkout = Wrapido_Checkout::get_instance();
        	$checkout->setRules($this->office,$this->door);
        }
       
        add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, [ $this, 'refreshDatabase' ] );
		
        // Save settings in admin if you have any defined
        add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
        
        
    }

    /**
     * Define settings field for this shipping
     * @return void 
     */
    function init_form_fields() { 

        $this->form_fields = [		
        	'enabled' => [
            	'title' => __( 'Enable', 'wrapido' ),
              	'type' => 'checkbox',
              	'description' => __( 'Enable this shipping method.', 'wrapido' ),
              	'default' => 'yes',
            ],
			'title' => [
	            'title' => __( 'Name', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'The name of this shipping method for the customers', 'wrapido' ),
              	'default' => __( 'Rapido Courier', 'wrapido' ),
            ],
            'test' => [
            	'title' => __( 'Test', 'wrapido' ),
              	'type' => 'checkbox',
              	'description' => __( 'Run in test mode.', 'wrapido' ),
              	'default' => 'yes',
            ],
			'api_user' => [
	            'title' => __( 'Username', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Username for Rapido system', 'wrapido' ),
              	'default' => '',
            ],
			'api_pass' => [
	            'title' => __( 'Password', 'wrapido' ),
				'type' => 'password',
	            'description' => __( 'Password for Rapido system', 'wrapido' ),
              	'default' => '',
            ],
			'location' => [
	            'title' => __( 'Your city ID', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Your city ID from Rapido system', 'wrapido' ),
              	'default' => '68134',
            ],
			'service1' => [
	            'title' => __( 'Service 1 Subservice', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Subservice ID for same-city deliveries', 'wrapido' ),
              	'default' => '5',
            ],
			'service2' => [
	            'title' => __( 'Service 2 Subservice', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Subservice ID for deliveries to other cities', 'wrapido' ),
              	'default' => '9',
            ],
            'loadrapidodb2' => [
	            'title' => __( 'Load countries', 'wrapido' ),
            	'type' => 'checkbox',
	            'description' =>  __( 'Refresh Rapido countries database from file', 'wrapido' ),
            	'default' => 'no',
	        ],
            'loadrapidodb' => [
	            'title' => __( 'Load cities', 'wrapido' ),
            	'type' => 'checkbox',
	            'description' =>  __( 'Refresh Rapido cities database (takes a while)', 'wrapido' ),
            	'default' => 'no',
	        ],
	        'loadrapidodb3' => [
	            'title' => __( 'Load streets', 'wrapido' ),
            	'type' => 'checkbox',
	            'description' =>  __( 'Refresh Rapido streets database (takes a while)', 'wrapido' ),
            	'default' => 'no',
	        ],
	        'loadrapidodb4' => [
	            'title' => __( 'Load quarters', 'wrapido' ),
            	'type' => 'checkbox',
	            'description' =>  __( 'Refresh Rapido neighborhoods database', 'wrapido' ),
            	'default' => 'no',
	        ],
 		];
 		
 		$this->instance_form_fields = [		
			'flatrate' => [
	            'title' => __( 'Flat Rate', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Flat rate price. Leave blank for actual price calculation.', 'wrapido' ),
              	'default' => '',
            ],
			'free' => [
	            'title' => __( 'Free Shipping', 'wrapido' ),
				'type' => 'text',
	            'description' => __( 'Free shipping above this order value', 'wrapido' ),
              	'default' => '',
            ],
			'office' => [
	            'title' => __( 'Deliver to Office', 'wrapido' ),
				'type' => 'checkbox',
	            'description' => __( 'Offer option for pick-up from Rapido office', 'wrapido' ),
              	'default' => 'yes',
            ],
			'door' => [
	            'title' => __( 'Deliver to Door', 'wrapido' ),
				'type' => 'checkbox',
	            'description' => __( 'Offer option for delivery to customer address', 'wrapido' ),
              	'default' => 'yes',
            ],
 		];

    }

    
    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package = [] ) {
	
        $weight = 0;
		$cost = 0;
		$country = $package['destination']['country'];
		$value = ( !empty($package['contents_cost']) ? $package['contents_cost'] : 0 );
		
		$rapido = Wrapido_Rapido::get_instance();
		$rapido->setUpSoap($this->test,$this->api_user,$this->api_pass,$this->location,$this->service1,$this->service2);
				
		if (empty( $this->freeshipping ) OR $value < $this->freeshipping ) {
			
			if (!empty ( $this->flatrate )) {
			
				$cost = $this->flatrate;
			
			} else {
			
				foreach ( $package['contents'] as $item_id => $values ) { 
				   $_product = $values['data']; 
				   $weight += $_product->get_weight() * $values['quantity']; 
				}
				$weight = wc_get_weight( $weight, 'kg' );
		        
		        if (empty($package['destination']['rapido_city'])) {
		        	// get city id for postcode entered
					if ($package['destination']['state']=='BG-22' OR $package['destination']['postcode'] == '1000' ) {
						$cost = $rapido->calculatePrice($value,$weight,1);	
					} else {
						$cost = $rapido->calculatePrice($value,$weight,2);
					}
				} else {
					if ($package['destination']['rapido_city']==$this->location) {
						$cost = $rapido->calculatePrice($value,$weight,1);	
					} else {
						$cost = $rapido->calculatePrice($value,$weight,2);
					}
				}
				
			} 	
			
		}
		        
		$rate = [
			'id' => $this->get_rate_id(),
			'label' => $this->title,
			'cost' => $cost,
		];
			
		$this->add_rate( $rate );
		
    }
    
    /**
     * Refreshes the database from Rapido.
     *
     * @access public
     * @param none
     * @return void
     */
    public function refreshDatabase( $settings ) {
    	if ($this->instance_id==0) {
    		if ($settings['loadrapidodb'] == 'yes') {
	        	$settings['loadrapidodb'] = 'no';
	        	$rapido = Wrapido_Rapido::get_instance();
	        	$rapido->setUpSoap($this->test,$this->api_user,$this->api_pass,$this->location,$this->service1,$this->service2);
	        	$db = Wrapido_DB::get_instance();
	        	$cities = $rapido->getCities('');
	        	$db->importCities($cities);
	        }
	        if ($settings['loadrapidodb2'] == 'yes') {
	        	$settings['loadrapidodb2'] = 'no';
	        	$db = Wrapido_DB::get_instance();
	        	$db->importCountries();
	        }
	        if ($settings['loadrapidodb3'] == 'yes') {
	        	$settings['loadrapidodb3'] = 'no';
	        	$db = Wrapido_DB::get_instance();
	        	$db->importStreets();
	        }
	        if ($settings['loadrapidodb4'] == 'yes') {
	        	$settings['loadrapidodb4'] = 'no';
	        	$db = Wrapido_DB::get_instance();
	        	$db->importKvartal();
	        }
    	}
        return $settings;
    }
}