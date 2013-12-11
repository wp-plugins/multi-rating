// Add a rating criteria form submit
jQuery("#createRatingItemBtn").click(function() {
	jQuery("#formSubmitted").val("true");
});


// Edit/save actions
var rowActions = jQuery(".row-actions > a");
jQuery.each(rowActions, function(index, element) {
	jQuery(element).click(function(event) { 
		var btnId = this.id;
		var parts = btnId.split("-"); 
		var action = parts[0];
		var column = parts[1];
		var rowId = parts[2]; 
		if (action === "edit") {
			// change state
			jQuery("#viewSection-" + column + "-" + rowId).css("display", "none");
			jQuery("#editSection-" + column + "-" + rowId).css("display", "block");
		} else if (action === "save") {
			// save
			var value = jQuery("#input-" + column + "-" + rowId).val();
			var data =  { action : "save_column", nonce : multiRatingLocalData.ajaxNonce, column : column, ratingItemId : rowId, value : value };
			jQuery.post(multiRatingLocalData.ajaxUrl, data, function(response) {
				jQuery("#text-" + column + "-" + rowId).html(value);
				jQuery("#viewSection-" + column + "-" + rowId).css("display", "block");
				jQuery("#editSection-" +  column + "-" + rowId).css("display", "none");
			});
		}
		
		// stop event
		event.preventDefault();
	});
});

//Clear database button submit
jQuery("#clear-database-btn").live('click',function(e) {
	jQuery("#clear-database-flag").val("true");
});
