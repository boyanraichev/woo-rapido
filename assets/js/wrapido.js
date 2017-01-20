var rapidoajax = {
	
	// ajax url
	ajaxurl: ajax_wrapido_object.ajaxurl,
	
	nonce: '',
	
	self: this,
	
    initialize: function() {
        this.nonce = jQuery('#_wrapido_nonce').val();
        this.searchCities();
        this.searchPostcodes();
        this.searchStreets();
        this.bindEvents();
    },
    
    bindEvents: function() {
        jQuery(document).ready(this.onReady);    
    },
    
    onReady: function() {
    	var city_id = jQuery('#rapidoauto-city-id').val(); 
    	var office_id = jQuery('#rapido_office_id').val(); 
    	if (city_id && city_id.length > 0) {
    		rapidoajax.searchOffices(city_id,office_id);
    	}
    	jQuery('input[name^="shipping_method"]').on('change',function(){
    		jQuery('form.checkout').trigger('update');
		});
    },
    
    searchCities: function() {
    	jQuery('.rapidoauto-city #billing_city').autocomplete({
	        source: rapidoajax.ajaxurl + '?action=rapidocity&nonce=' + rapidoajax.nonce,
	        minLength: 3,
	        delay: 300,
	        select: function( event, ui ) {
	            jQuery('#rapidoauto-city-id').val(ui.item.id);
	            jQuery('#billing_postcode').val(ui.item.postcode);
	            rapidoajax.searchOffices(ui.item.id,0);
	            jQuery('form.checkout').trigger('update');
	        },
	        change: function(event, ui) {
	            jQuery('#rapidoauto-city-id').val(ui.item ? ui.item.id : '');
	        }
	    });
    },
    
    searchPostcodes: function() {
    	jQuery('.rapidoauto-postcode #billing_postcode').autocomplete({
	        source: rapidoajax.ajaxurl + '?action=rapidopost&nonce=' + rapidoajax.nonce,
	        minLength: 2,
	        delay: 200,
	        select: function( event, ui ) {
	            jQuery('#rapidoauto-city-id').val(ui.item.id);
	            jQuery('#billing_city').val(ui.item.city);
	            rapidoajax.searchOffices(ui.item.id);
	        },
	        change: function(event, ui) {

	        }
	    });
    },
    
	searchOffices: function(city,office_id) {
		jQuery.ajax({
            url: rapidoajax.ajaxurl,
            dataType: "json",
            data: {
            	action: 'rapidooffice',
            	nonce: rapidoajax.nonce,
            	selected: office_id,
                city : city,
            },
            success: function(data) {
                jQuery('#rapido_office').html(data);
            }
        });
	},

    searchStreets: function() {
    	jQuery('.rapidoauto-street input').autocomplete({
	        source: function(request, response) {
	            jQuery.ajax({
	                url: rapidoajax.ajaxurl,
	                dataType: "json",
	                data: {
	                	action: 'rapidostreet',
	                	nonce: rapidoajax.nonce,
	                    term : request.term,
	                    city : jQuery('#rapidoauto-city-id').val()
	                },
	                success: function(data) {
	                    response(data);
	                }
	            });
	        },
	        minLength: 3,
	        delay: 300,
	        select: function( event, ui ) {
	            jQuery('#rapidoauto-street-id').val(ui.item.id);
	            jQuery('#rapidoauto-kvartal-id').val(ui.item.kvartal);
	        },
	        change: function(event, ui) {
	            jQuery('#rapidoauto-street-id').val(ui.item ? ui.item.id : '');
	            jQuery('#rapidoauto-kvartal-id').val(ui.item ? ui.item.kvartal : '');
	        }
	    });
    },
    
};
rapidoajax.initialize();