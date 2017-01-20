var rapidoadmin = {
	
	// ajax url
	ajaxurl: ajax_wrapido_object.ajaxurl,
	
	shipmentCreated: false,
	
    initialize: function() {
        this.bindEvents();
        this.nonce = jQuery('#_wrapido_nonce').val();
        
    },
    
    bindEvents: function() {
    	jQuery(document).ready(this.onReady);
            
    },
    
    onReady: function() {
    	var createS = document.getElementById('rapidoCreateShipment');
    	if (createS) { createS.addEventListener('click', rapidoadmin.createShipment, false); }
		
		var printS = document.getElementById('rapidoPrintShipment');
    	if (printS) { printS.addEventListener('click', rapidoadmin.printShipment, false); }
		
		var offices = document.getElementById('rapido_office');
		if (offices) { offices.addEventListener('change',function(){ jQuery('#rapido_office_id').val(offices.options[offices.selectedIndex].value); }); }
		
    },
    
    createShipment: function(event) {
		event.preventDefault();
		var cityid = document.getElementById('rapidoauto-city-id').value; 
		var countryid = document.getElementById('country_id').value;
		if (!countryid) {
			alert(ajax_wrapido_object.missingcountryid);
		} else {
			if ( (countryid == 100) && ( !cityid || cityid.length < 1) ) {
				alert(ajax_wrapido_object.missingcityid);		
	 		} else {
				if (rapidoadmin.shipmentCreated==false) {
					rapidoadmin.shipmentCreated=true;
					jQuery('#wrapido_messages').html('<span class="spinner-inline"></span>'+ajax_wrapido_object.loadingmessage);
			    	jQuery.ajax({
			            url: rapidoajax.ajaxurl,
			            dataType: "json",
			            data: jQuery('#rapido_shipment input').serialize() + '&action=rapidocreate',
			            success: function(data) {
			                if (data.tracking=='error') {
			                	alert(ajax_wrapido_object.error); 
			                	rapidoadmin.shipmentCreated=false;
			                } else {
			                	jQuery('#rapido_tracking').val(data.tracking);
			                	var button = document.getElementById('rapidoCreateShipment');
			                	button.id = 'rapidoPrintShipment';
			                	button.innerHTML = ajax_wrapido_object.printshipment;
			                	button.addEventListener('click', rapidoadmin.printShipment, false);
			                	document.getElementById('rapidoPrintShipment').click();
			                }
			                jQuery('#wrapido_messages').html(' ');
			            },
			            error: function () {
			            	alert(ajax_wrapido_object.error);
			            	rapidoadmin.shipmentCreated=false; 
			            	jQuery('#wrapido_messages').html(' ');
			            }
			        });
				}
			}
		}
    },
    
    printShipment: function(event) {
		event.preventDefault();
		var tracking = jQuery('#rapido_tracking').val();
		window.location.search += '&rapidoshipment='+tracking;        
    },
    
};
rapidoadmin.initialize();