jQuery(document).ready(function() {	
	
	jQuery(".rating-form :button").click(function(e) {
	
		var ratingItems = [];
		var btnId = e.currentTarget.id;
		var parts = btnId.split("-"); 
		var postId = parts[0];
		var sequence = parts[1];
		
		
		jQuery( '.rating-form input[type="hidden"].rating-form-' + postId + '-' + sequence + '-item').each(function( index ) {			
			var ratingItemId = jQuery(this).val();

			// get selected maximum rating value
			var selectedValue = jQuery('select#rating-form-' + postId + '-' + sequence + '-item-value-' + ratingItemId + ' :selected').val();
			var ratingItem = { 
						'id' : ratingItemId, 
						'value' : selectedValue
					};
			ratingItems[index] = ratingItem;
		});
		
		var data = {
				action : "submit_rating",
				nonce : mr_frontend_data.ajax_nonce,
				ratingItems : ratingItems,
				postId : postId
			};
	
			jQuery.post(mr_frontend_data.ajax_url, data, function(response) {
				alert(response);
			});
	});
	
	
});