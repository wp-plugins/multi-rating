jQuery(document).ready(function() {	
	
	jQuery(".rating-form :button").click(function(e) {
	
		var ratingItems = [];
		var btnId = e.currentTarget.id;
		var parts = btnId.split("-"); 
		var postId = parts[0];
		var sequence = parts[1];
		
		// each rating item has a hidden id field using the ratig form id, post id and sequence
		jQuery( '.rating-form input[type="hidden"].rating-form-' + postId + '-' + sequence + '-item').each(function( index ) {			
			var ratingItemId = jQuery(this).val();
			
			// get values for 3 types of rating items: select, radio and star rating
			var element = jQuery('[name=rating-item-' +ratingItemId + '-' + sequence + ']');
			var selectedValue = null;
			if (jQuery(element).is(':radio')) {
				selectedValue = jQuery('input[type="radio"][name=rating-item-' +ratingItemId + '-' + sequence + ']:checked').val(); 
			} else if (jQuery(element).is('select')) {
				selectedValue = jQuery('select[name=rating-item-' +ratingItemId + '-' + sequence + '] :selected').val(); 
			} else {
				selectedValue = jQuery('input[type=hidden][name=rating-item-' +ratingItemId + '-' + sequence + ']').val();
			}
			
			var ratingItem = { 
						'id' : ratingItemId, 
						'value' : selectedValue
					};
			ratingItems[index] = ratingItem;
		});

		
		var data = {
				action : "save_rating",
				nonce : mr_frontend_data.ajax_nonce,
				ratingItems : ratingItems,
				postId : postId
			};
	
			jQuery.post(mr_frontend_data.ajax_url, data, function(response) {
				alert(response);
			});
	});
	
	/**
	 * Selected star rating changes on hover and click
	 */
	jQuery(".star-rating-select .fa-star-o, .star-rating-select .fa-star, .star-rating-select .fa-minus-circle").click(function(e) {
		jQuery(this).removeClass("fa-star-o").addClass("fa-star");
		jQuery(this).prevAll().removeClass("fa-star-o").addClass("fa-star");
		jQuery(this).nextAll().removeClass("fa-star").addClass("fa-star-o");
		updateStarRatingValue(this);
	});
	// now cater for touch screen devices
	var touchData = {
		started : null, // detect if a touch event is sarted
		currrentX : 0,
		yCoord : 0,
		previousXCoord : 0,
		previousYCoord : 0,
		touch : null
	};
	jQuery(".star-rating-select .fa-star-o, .star-rating-select .fa-star, .star-rating-select .fa-minus-circle").on("touchstart", function(e) {
		touchData.started = new Date().getTime();
		var touch = e.originalEvent.touches[0];
		touchData.previousXCoord = touch.pageX;
		touchData.previousYCoord = touch.pageY;
		touchData.touch = touch;
	});
	jQuery(".star-rating-select .fa-star-o, .star-rating-select .fa-star, .star-rating-select .fa-minus-circle").on(
			"touchend touchcancel",
			function(e) {
				var now = new Date().getTime();
				// Detecting if after 200ms if in the same position.
				if ((touchData.started !== null)
						&& ((now - touchData.started) < 200)
						&& (touchData.touch !== null)) {
					var touch = touchData.touch;
					var xCoord = touch.pageX;
					var yCoord = touch.pageY;
					if ((touchData.previousXCoord === xCoord)
							&& (touchData.previousYCoord === yCoord)) {
						
						jQuery(this).removeClass("fa-star-o").addClass("fa-star");
						jQuery(this).prevAll().removeClass("fa-star-o").addClass("fa-star");
						jQuery(this).nextAll().removeClass("fa-star").addClass("fa-star-o");
						updateStarRatingValue(this);
					}
				}
				touchData.started = null;
				touchData.touch = null;
			});
	
	/**
	 * Updates the selected star rating value
	 */
	function updateStarRatingValue(element) {
		var clazz = jQuery(element).attr("class");
		
		if (clazz && clazz.length && clazz.split) {
			clazz = clazz.trim();
			clazz = clazz.replace(/\s+/g, ' ');
			var classes = clazz.split(' ');
			var index=0;
			for (index; index<classes.length; index++) {
				var currentClass = classes[index];
		        if (currentClass !== '' && currentClass.indexOf('starIndex-') == 0) {
		        	
		        	// starIndex-X-ratingItemId-sequence
		        	var parts = currentClass.split("-"); 
		    		var value = parts[1]; // this is the star index
		    		var ratingItemId = parts[4]; /// skipt 2: rating-item-
		    		var sequence = parts[5];
		    		
		    		var elementId = '#rating-item-'+ ratingItemId + '-' + sequence;

		        	jQuery(elementId).val(value);
		        	return;
		        }
			}
			
		}
	}
	
});