<?php
/*
* Woo Rapido
*
* INCLUDES/ADMIN/FULFILMENT.PHP
* Integrates the admin order fulfilment
*/


if (!defined('ABSPATH')) die;

class Wrapido_Admin_Fulfilment {

	
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
    	
    	add_action( 'add_meta_boxes', [ $this, 'metaBox' ] );
    	add_action( 'save_post', [ $this, 'saveMeta' ] );

    }
    
    /**
     * Add the order meta box
     */
    public function metaBox() {
    	
    	add_meta_box( 'rapido', __( 'Rapido Shipping', 'wrapido' ), [ $this, 'shippingBox' ], 'shop_order', 'normal', 'default' );
    	
   	}

    /**
     * Prints the order meta box for Shipping
     */
    public function shippingBox($post) {

    	?>
    	<h3><?php _e('Manage shipment with Rapido','wrapido'); ?></h3>
    	
    	<div id="rapido_shipment">
    		
			<?php 
			$_billing_first_name = get_post_meta( $post->ID, '_billing_first_name', true );
			$_billing_last_name = get_post_meta( $post->ID, '_billing_last_name', true );
			$_billing_company = get_post_meta( $post->ID, '_billing_company', true );
			$_billing_city = get_post_meta( $post->ID, '_billing_city', true );
			$_billing_postcode = get_post_meta( $post->ID, '_billing_postcode', true );
			$_billing_address_1 = get_post_meta( $post->ID, '_billing_address_1', true );
			$_billing_address_2 = get_post_meta( $post->ID, '_billing_address_2', true );
			$_billing_phone = get_post_meta( $post->ID, '_billing_phone', true );
			$_billing_country = get_post_meta( $post->ID, '_billing_country', true );    			
				$db = Wrapido_DB::get_instance();
				$country_id = $db->searchCountryID($_billing_country);
			    			
    		$rapido_city_id = get_post_meta( $post->ID, 'rapido_city_id', true ); 
    		$rapido_office = get_post_meta( $post->ID, 'rapido_office', true );
    			
    		$rapido_kvartal_id = get_post_meta( $post->ID, 'rapido_kvartal_id', true );
    		$rapido_street_id = get_post_meta( $post->ID, 'rapido_street_id', true );
    		$rapido_ent = get_post_meta( $post->ID, 'rapido_ent', true );
    		$rapido_fl = get_post_meta( $post->ID, 'rapido_fl', true );
    		$rapido_ap = get_post_meta( $post->ID, 'rapido_ap', true );
    		$rapido_to_office = ( !empty($rapido_office) ? 1 : 0 );
    		$rapido_tracking = get_post_meta( $post->ID, 'rapido_tracking', true );
    		
    		$payment_method = get_post_meta( $post->ID, '_payment_method', true );
    		if ($payment_method=='cod') {
    			$order = wc_get_order( $post->ID );
    			$cod_total = $order->get_total();
    		} else {
    			$cod_total = 0;
    		}
    		$weight = get_post_meta( $post->ID, '_shipping_weight', true );
    		?>    			
    		<table>
    		<thead><th> </th><th> </th><th> </th><th> </th></thead>
    		<tbody>
    			<tr>
    				<td colspan="2">
    					<label for="_billing_first_name"><?php _e('First Name','wrapido'); ?> *</label><br>
    					<input type="text" name="_billing_first_name" id="_billing_first_name" class="rapido100" value="<?php echo esc_attr($_billing_first_name); ?>">
    				</td>
    				<td colspan="2">
    					<label for="_billing_last_name"><?php _e('Last Name','wrapido'); ?> *</label><br>
    					<input type="text" name="_billing_last_name" id="_billing_last_name" class="rapido100" value="<?php echo esc_attr($_billing_last_name); ?>">
    				</td>	    				
    			</tr>
    			<tr>
    				<td colspan="4">
    					<label for="_billing_company"><?php _e('Company','wrapido'); ?></label><br>
    					<input type="text" name="_billing_company" id="_billing_company" class="rapido100" value="<?php echo stripslashes(esc_attr($_billing_company)); ?>">
    				</td>
    			</tr>
    			<tr>
    				<td>
    					<label  for="rapidoauto-city-id"><?php _e('City ID','wrapido'); ?> *</label><br>
    					<input type="text" name="rapido_city_id" id="rapidoauto-city-id" class="rapido100" maxlength="8" value="<?php echo esc_attr($rapido_city_id); ?>" readonly>
    				</td>
    				<td colspan="2">
    					<label  for="billing_city"><?php _e('City','wrapido'); ?></label> *<br>
    					<span class="rapidoauto-city"><input type="text" name="_billing_city" id="billing_city" class="rapido100" value="<?php echo stripslashes(esc_attr($_billing_city)); ?>" placeholder="<?php _e('Search for city','wrapido'); ?>"></span>
    				</td>
    				<td>
    					<label for="_billing_postcode"><?php _e('Postcode','wrapido'); ?> *</label><br>
    					<input type="text" name="_billing_postcode" id="billing_postcode" class="rapido100" value="<?php echo esc_attr($_billing_postcode); ?>">
    				</td>
    			</tr>
    			<tr>
    				<td>
    					<label for="rapido_office_id"><?php _e('Office ID','wrapido'); ?></label><br>
			    		<input type="text" name="rapido_office" id="rapido_office_id" class="rapido100" value="<?php echo esc_attr($rapido_office); ?>" readonly>
    				</td>
    				<td colspan="3">
    					<label for="rapido_office"><?php _e('Deliver to office','wrapido'); ?></label><br>
    					<select class="rapido100" name="rapido_office_select" id="rapido_office">
    						<option value="0"></option>
    					</select>
    				</td>
    			</tr>
    			<tr>
    				<td colspan="2">
    					<label for="rapidoauto-kvartal-id"><?php _e('Quarter ID','wrapido'); ?></label><br>
    					<input type="text" name="rapido_kvartal_id" id="rapidoauto-kvartal-id" class="rapido100" value="<?php echo esc_attr($rapido_kvartal_id); ?>" readonly>
    				</td>
    				<td colspan="2">
    					<label for="rapidoauto-street-id"><?php _e('Street ID','wrapido'); ?></label><br>
    					<input type="text" name="rapido_street_id" id="rapidoauto-street-id" class="rapido100" value="<?php echo esc_attr($rapido_street_id); ?>" readonly>
    				</td>
    			</tr>
    			<tr>
    				<td colspan="3">
    					<label for="rapido_street"><?php _e('Street / Quarter','wrapido'); ?></label><br>
    					<span class="rapidoauto-street"><input type="text" name="_billing_address_1" id="rapido_street" class="rapido100" value="<?php echo stripslashes(esc_attr($_billing_address_1)); ?>" placeholder="<?php _e('Search for street','wrapido'); ?>"></span>
    				</td>
    				<td>
    					<label for=""><?php _e('Street or Building Number','wrapido'); ?></label><br>
    					<input type="text" name="_billing_address_2" class="rapido100" value="<?php echo stripslashes(esc_attr($_billing_address_2)); ?>" maxlength="5">
    				</td>
    			</tr>
    			<tr>
    				<td>
    					<label for="rapido_ent"><?php _e('Entrance','wrapido'); ?></label><br>
    					<input type="text" name="rapido_ent" id="rapido_ent" class="rapido100" value="<?php echo esc_attr($rapido_ent); ?>" maxlength="6">
    				</td>
    				<td colspan="2">
    					<label for="rapido_fl"><?php _e('Floor','wrapido'); ?></label><br>
    					<input type="text" name="rapido_fl" id="rapido_fl" class="rapido100" value="<?php echo esc_attr($rapido_fl); ?>" maxlength="10">
    				</td>
    				<td>
    					<label for="rapido_ap"><?php _e('Apartment','wrapido'); ?></label><br>
    					<input type="text" name="rapido_ap" id="rapido_ap" class="rapido100" value="<?php echo esc_attr($rapido_ap); ?>" maxlength="10"
    				</td>
    			</tr>
    			<tr>
    				<td colspan="2">
    					<label for="rapido_cod"><?php _e('Cash on delivery value','wrapido'); ?></label><br>
		    			<input type="number" name="rapido_cod" id="rapido_cod" class="rapido100" value="<?php echo $cod_total; ?>" min="0" max="999999" step="0.01" />
    				</td>
    				<td>
    					<label for="rapido_insurance"><br><input type="checkbox" name="rapido_insurance" id="rapido_insurance" value="1" /> <?php _e('Insurance','wrapido'); ?></label>
    				</td>
    				<td>
    					<label for="rapido_fragile"><br><input type="checkbox" name="rapido_fragile" id="rapido_fragile" value="1" /> <?php _e('Fragile','wrapido'); ?></label>
    				</td>
    			</tr>
    			<tr>
    				<td colspan="2">
    					<label for="rapido_tracking"><?php _e('Tracking number','wrapido'); ?></label><br>
		    			<input type="text" name="rapido_tracking" id="rapido_tracking" class="rapido100" value="<?php echo $rapido_tracking; ?>" readonly>
    				</td>
    				<td colspan="2">
    					<label><?php _e('Contents of package','wrapido'); ?> *</label><br>
    					<input type="text" name="rapido_descr" class="rapido100" value="" placeholder="<?php _e('Describe order contents','wrapido'); ?>" >
    				</td>
    			</tr>
    		</tbody>
    		</table>
			<p>
    		<?php wp_nonce_field( 'wrapido-nonce','_wrapido_nonce', false ); ?>
    		<input type="hidden" name="rapido_order" value="<?php echo $post->ID; ?>">
    		<input type="hidden" name="_billing_phone" value="<?php echo $_billing_phone; ?>">
    		<input type="hidden" name="country_id" id="country_id" value="<?php echo $country_id; ?>">  
    		<input type="hidden" name="rapido_weight" value="<?php echo $weight; ?>">      		 		    		
    		<?php 
    		if ($rapido_tracking) {
    		?>
    			<button id="rapidoPrintShipment" class="button-primary"><?php _e('Print shipment','wrapido'); ?></button>
			<?php 
			} else {
			?>
	    		<button id="rapidoCreateShipment" class="button-primary"><?php _e('Create shipment','wrapido');?></button>
	    	<?php 
	    	}
	    	?>
	    	<span id="wrapido_messages"></span></p>    		    
	    	<p><small><?php _e('If City ID and Postcode are blank, you must enter the city name again and choose it from the dropdown list. For international shipments please use Rapido website only.','wrapido'); ?></small></p>
    	
    	</div>
    	<script>
    	
    	</script>
    	<?php
    }
    
    /**
     * Saves the order meta box
     */
    public function saveMeta($order_id) {
    	
    	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	      return;
		}
	
		// verify nonce
		if (!isset($_POST['_wrapido_nonce']) OR !wp_verify_nonce( $_POST['_wrapido_nonce'], 'wrapido-nonce' ) ) {  
	      return;
	    }
    	
    	if ( isset( $_POST['rapido_city_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_city_id', sanitize_text_field( $_POST['rapido_city_id'] ) );
	    }
	    if ( isset( $_POST['rapido_kvartal_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_kvartal_id', sanitize_text_field( $_POST['rapido_kvartal_id'] ) );
	    }
	    if ( isset( $_POST['rapido_street_id'] ) ) {
	        update_post_meta( $order_id, 'rapido_street_id', sanitize_text_field( $_POST['rapido_street_id'] ) );
	    }
	    if ( isset( $_POST['rapido_office'] ) ) {
	        update_post_meta( $order_id, 'rapido_office', sanitize_text_field( $_POST['rapido_office'] ) );
	    }
	    if ( isset( $_POST['rapido_ent'] ) ) {
	        update_post_meta( $order_id, 'rapido_ent', sanitize_text_field( $_POST['rapido_ent'] ) );
	    }
	    if ( isset( $_POST['rapido_fl'] ) ) {
	        update_post_meta( $order_id, 'rapido_fl', sanitize_text_field( $_POST['rapido_fl'] ) );
	    }
	    if ( isset( $_POST['rapido_ap'] ) ) {
	        update_post_meta( $order_id, 'rapido_ap', sanitize_text_field( $_POST['rapido_ap'] ) );
	    }
	    if ( isset( $_POST['rapido_ap'] ) ) {
	        update_post_meta( $order_id, 'rapido_ap', sanitize_text_field( $_POST['rapido_ap'] ) );
	    }	    
    	
   	}
}