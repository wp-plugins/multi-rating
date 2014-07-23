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
	var ratingItemStatus = {};
	
	// supporting different versions of Font Awesome icons
	var icon_classes = jQuery.parseJSON(mr_frontend_data.icon_classes);
	
	jQuery(".star-rating-select .mr-star-empty, .star-rating-select .mr-star-full").click(function(e) {
		
		updateRatingItemStatus(this, 'clicked');
		
		jQuery(this).not('.mr-minus').removeClass(icon_classes.star_empty + " mr-star-hover").addClass(icon_classes.star_full);
		jQuery(this).prevAll().not('.mr-minus').removeClass(icon_classes.star_empty + " mr-star-hover").addClass(icon_classes.star_full);
		jQuery(this).nextAll().not('.mr-minus').removeClass(icon_classes.star_full + " mr-star-hover").addClass(icon_classes.star_empty);
		
		updateSelectedHiddenValue(this);
	});
	
	jQuery(".star-rating-select .mr-minus").click(function(e) {
		
		updateRatingItemStatus(this, '');
		
		jQuery(this).not('.mr-minus').removeClass(icon_classes.star_empty + " mr-star-hover").addClass(icon_classes.star_full);
		jQuery(this).prevAll().not('.mr-minus').removeClass(icon_classes.star_empty + " mr-star-hover").addClass(icon_classes.star_full);
		jQuery(this).nextAll().not('.mr-minus').removeClass(icon_classes.star_full + " mr-star-hover").addClass(icon_classes.star_empty);
		
		updateSelectedHiddenValue(this);
	});
	
	jQuery(".star-rating-select .mr-minus, .star-rating-select .mr-star-empty, .star-rating-select .mr-star-full").hover(function(e) {

		var elementId = getRatingItemElementId(this);
		var ratingItemIdSequence = getRatingItemIdSequence(elementId);
		
		if (ratingItemStatus[ratingItemIdSequence] != 'clicked' && ratingItemStatus[ratingItemIdSequence] != undefined) {
			
			updateRatingItemStatus(this, 'hovered');
			
			jQuery(this).not('.mr-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full + " mr-star-hover");
			jQuery(this).prevAll().not('.mr-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full + " mr-star-hover");
			jQuery(this).nextAll().not('.mr-minus').removeClass(icon_classes.star_full + " mr-star-hover").addClass(icon_classes.star_empty);	
		}
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
	
	jQuery(".star-rating-select .mr-star-empty, .star-rating-select .mr-star-full, .star-rating-select .mr-minus").on("touchstart", function(e) {
		touchData.started = new Date().getTime();
		var touch = e.originalEvent.touches[0];
		touchData.previousXCoord = touch.pageX;
		touchData.previousYCoord = touch.pageY;
		touchData.touch = touch;
	});
	
	jQuery(".star-rating-select .mr-star-empty, .star-rating-select .mr-star-full, .star-rating-select .mr-minus").on(
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
						
						jQuery(this).removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
						jQuery(this).prevAll().removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
						jQuery(this).nextAll().removeClass(icon_classes.star_full).addClass(icon_classes.star_empty);
						
						updateSelectedHiddenValue(this);
					}
				}
				touchData.started = null;
				touchData.touch = null;
			});
	
	/**
	 * Updates the rating item status to either hovered or clicked
	 */
	function updateRatingItemStatus(element, status) {
		var elementId = getRatingItemElementId(element);
		var ratingItemIdSequence = getRatingItemIdSequence(elementId);
		if (ratingItemIdSequence != null) {
			ratingItemStatus[ratingItemIdSequence] = status;
		}
	}
	
	function getRatingItemIdSequence(elementId) {
		var parts = elementId.split("-"); 
		
		var ratingItemId = parts[4]; /// skipt 2: rating-item-
		var sequence = parts[5];
		
		var ratingItemIdSequence = 'rating-item-' + ratingItemId + '-' + sequence;
		return ratingItemIdSequence;
	}
	
	function getRatingItemElementId(element) {
		var clazz = jQuery(element).attr("class");
		
		if (clazz && clazz.length && clazz.split) {
			clazz = clazz.trim();
			clazz = clazz.replace(/\s+/g, ' ');
			var classes = clazz.split(' ');
			var index=0;
			for (index; index<classes.length; index++) {
				var currentClass = classes[index];
		        if (currentClass !== '' && currentClass.indexOf('index-') == 0) {
		        	
		        	// index-X-ratingItemId-sequence
		        	var parts = currentClass.split("-"); 
		    		var value = parts[1]; // this is the star index
		    		var ratingItemId = parts[4]; /// skipt 2: rating-item-
		    		var sequence = parts[5];
		    		
		    		var elementId = 'index-' + value + '-rating-item-' + ratingItemId + '-' + sequence;
		    		//index-1-rating-item-1-1
		    		return elementId;
		        }
			}
		}
		
		return null;
	}
	
	/**
	 * Updates the selected star rating value
	 */
	function updateSelectedHiddenValue(element) {
		var clazz = jQuery(element).attr("class");
		
		if (clazz && clazz.length && clazz.split) {
			clazz = clazz.trim();
			clazz = clazz.replace(/\s+/g, ' ');
			var classes = clazz.split(' ');
			var index=0;
			for (index; index<classes.length; index++) {
				var currentClass = classes[index];
		        if (currentClass !== '' && currentClass.indexOf('index-') == 0) {
		        	
		        	// FIXME this should use a unique element Id - not a class
		        	
		        	// index-X-ratingItemId-sequence
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