
function calculateRemainingDistributionPoints(totalPointsAllowed, thisElementID)
{
	
	var tempValue;
	var totalAllocated=0;
	var totalLeft;
	
	jQuery('input[name="pf_distributionValue"]').each(function () {
		tempValue = this.value;
		totalAllocated = Number(totalAllocated) + Number(tempValue);
	});
	
	totalLeft = totalPointsAllowed - totalAllocated;
	
	if(totalLeft<0)
	{
		// force the box 
		jQuery("#remainingPoints").html(
		'<div class="feedbackFail">Remaining Points : '+totalLeft+'</span></div>'
		);
		
		// Hide the Submit Button
		jQuery("#distributeSubmitButtonDiv").hide( "fast");	
		

	}
	else if(totalLeft>0)

	{
		jQuery("#remainingPoints").html(
		'<div class="feedbackAlert">Remaining Points : '+totalLeft+'</span></div>'
		);	
		
		// Hide the Submit Button
		jQuery("#distributeSubmitButtonDiv").hide( "fast");	
		
	}
	else if(totalLeft==0)
	{
		jQuery("#remainingPoints").html(
		'<div class="feedbackSuccess">Remaining Points : '+totalLeft+'</span></div>'
		);	
		
		// Show the Submit Button
		jQuery("#distributeSubmitButtonDiv").show( "fast");	
	}

}


// Listener for the popoup close stuff
jQuery( document ).ready( function () {
	
	
	jQuery('#pfPopupCloseButton').on( 'click', function ( e ) {
		jQuery('#peerFeedbackCompletePopup').fadeOut( 400 );
	});
	
	
	
	
	
	
	
	
	
	
	
	
			// Listen for textarea change as well							
		jQuery('.peerFeedbackTextarea').bind('input propertychange', function( ) {								
			
			var thisTextareaID = jQuery( this ).attr('id');								

			var tempArray = thisTextareaID.split('_');
			// The target User ID is the first element of the temp array
			var targetUserID = tempArray[1];
			jQuery( '#feedbackResponse_'+targetUserID ).hide( 'fast');	

		});							
		
		
		
		jQuery('.td-toggle').on( 'click', function ( e ) {
			
			jQuery( this ).find('input:radio').prop('checked', true); // Allow the whole TD to be clicked	
			
			var parent_tr	= jQuery( this ).parent();
			var tds 		= jQuery( parent_tr ).children();
			
			jQuery( tds ).removeClass( 'td-highlight' ); // Remove ALL highlights from tds in this row
			jQuery( tds ).removeClass( 'td-green-highlight' ); // Remove ALL green highlights from tds in this row
			jQuery( this ).addClass( 'td-highlight' ); // Add the highglight just to this cell

			// HIDE the 'feedback saved' message if they select something else after saving
			var thisElementID = jQuery( this ).find('input:radio').attr('id');
			// Get the targetUserID
			var tempArray = thisElementID.split('_');
			// The target User ID is the first element of the temp array
			var targetUserID = tempArray[1];
			

			
			jQuery( '#feedbackResponse_'+targetUserID ).hide( 'fast');		
			
			
			
		});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	// Listen for clicking the tbale and showing the submit button if required
	jQuery ( ".rubricTableClickable " ).click(function() {
  
		var parentDivID = jQuery(this).closest('div').attr('id')

		//console.log("Parent = "+parentDivID);

		var thisUserID = parentDivID.split("_");
		thisUserID = thisUserID[1];
		  
		  
		var radioArray = new Array();
		jQuery("#"+parentDivID+" input:radio").each(function (index) {
			radioArray[index] = jQuery(this).attr('name');
			
		});  
		
		radioArray = jQuery.unique( radioArray );
		
		var radioCount = radioArray.length;
		var completedCount=0;
		  
		  // Got through the array and check if all radio buttons are clicked	  
		radioArray.forEach(function(radioName) {
			
			console.log(radioName+" CHECK = "+jQuery('input[name='+ radioName +']:checked').length);
			if(jQuery('input[name='+ radioName +']:checked').length) {
				  
				completedCount++
				//console.log("radioCount="+radioCount);
				//	console.log("completedCount="+completedCount);
		
			}
		  
		})
		
		if(completedCount>=	radioCount) // They have completed it for this peson so show the feedback button
		{
			//console.log("SHOW BUTTON FOR "+thisUserID);
			jQuery("div #ek_peer_submitButton_"+thisUserID).removeClass( "ek-hidden" );

		}		
	
	
 	
	
	
	
	});
 
	
	
	
	
	
	
});

