jQuery(document).ready(function() {	

	jQuery("#add-new-rating-item-btn").click(function() {
		jQuery("#form-submitted").val("true");
	});

	jQuery("#clear-database-btn").live('click',function(e) {
		jQuery("#clear-database").val("true");
	});
	
	jQuery("#export-btn").click(function(event) {
		jQuery("#export-rating-results").val("true");
	});
	
	var rowActions = jQuery("#rating-item-table-form .row-actions > a");
	jQuery.each(rowActions, function(index, element) {
		jQuery(element).click(function(event) { 
			var btnId = this.id;
			var parts = btnId.split("-"); 
			var action = parts[0];
			var column = parts[1];
			var rowId = parts[2]; 
			if (action === "edit") {
				// change state
				jQuery("#view-section-" + column + "-" + rowId).css("display", "none");
				jQuery("#edit-section-" + column + "-" + rowId).css("display", "block");
			} else if (action === "save") {
			
				var field_id = "#field-" + column + "-" + rowId;
				var value = null;
				if (jQuery(field_id).is(":checkbox")) {
					value = jQuery(field_id).is(':checked');
				} else {
					value = jQuery(field_id).val();
				}
				
				var data =  { 
						
						action : "save_rating_item_table_column",
						nonce : mr_admin_data.ajax_nonce,
						column : column,
						ratingItemId : rowId,
						value : value
					};
				jQuery.post(mr_admin_data.ajax_url, data, function(response) {
					var jsonResponse = jQuery.parseJSON(response);
					if (jsonResponse.error_message && jsonResponse.error_message.length > 0) {
						alert(jsonResponse.error_message);
					} else {
						jQuery("#text-" + column + "-" + rowId).html(jsonResponse.value);
						jQuery("#view-section-" + column + "-" + rowId).css("display", "block");
						jQuery("#edit-section-" +  column + "-" + rowId).css("display", "none");
					}
				});
			}
			
			// stop event
			event.preventDefault();
		});
	});
	
	jQuery(document).ready(function() {
		
		jQuery('.color-picker').wpColorPicker({
		    defaultColor: false,
		    change: function(event, ui){},
		    clear: function() {},
		    hide: true,
		    palettes: true
		});
	    
	    jQuery('.date-picker').datepicker({
	        dateFormat : 'yy/mm/dd'
	    });
	    
	});
	
});