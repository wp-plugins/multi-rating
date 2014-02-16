jQuery(".ratingForm :button").click(function(e) {
	// Submit new rating item entry with criteria rating values
	
	// get selected rate item values
	var ratingItems = [];
	var postId = e.currentTarget.id;

	jQuery( '.ratingForm input[type="hidden"].ratingForm' + postId + 'Item').each(function( index ) {
		var rateItemId = jQuery(this).val();
		// get selected maximum rating value
		var selectedValue = jQuery('select#ratingForm' + postId + 'ItemValue' + rateItemId + ' :selected').val();
		var ratingItem = { 
					'id' : rateItemId, 
					'value' : selectedValue
				};
		ratingItems[index] = ratingItem;
	});
	
	var data = {
			action : "submit_rating",
			nonce : multiRatingLocalData.ajaxNonce,
			ratingItems : ratingItems,
			postId : postId
		};

		jQuery.post(multiRatingLocalData.ajaxUrl, data, function(response) {
			alert(response);
		});
});
