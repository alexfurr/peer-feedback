
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
});

