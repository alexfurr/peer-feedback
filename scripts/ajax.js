function ajaxFeedbackTextUpdate(elementID, currentUserID, projectID)
{
	var userResponse = '';
	
	// GE tthe targetUserID
	var tempArray = elementID.split("_");
	var targetUserID = tempArray[1];

	
	//if is refection question with text inout, save it to the userResponse string for update
	userResponse = document.getElementById(elementID).value;
	
	
	// We need question ID AND the logged in user AND the value passed to the beneath query	
	jQuery.ajax({
		type: 'POST',
		url: frontEndAjax.ajaxurl,
		data: {			
			"action": "addPeerFeedback",
			"userResponse": userResponse,
			"currentUserID": currentUserID,
			"projectID": projectID,
			"targetUserID": targetUserID,
			"security": frontEndAjax.ajax_nonce
		},
		success: function(data){
			//console.log(data);
			
			}
	});
	
	
	return false;		
	
}


function ajaxFeedbackDistributionUpdate(currentUserID, projectID)
{
	var userResponse = '';
	
	

	jQuery('input[name="pf_distributionValue"]').each(function () {
		
		thisElementID = this.id;
		thisElementValue = this.value;
		//alert(thisElementID+"="+thisElementValue);
		if(!thisElementValue){thisElementValue=0;}
		

			
		// GE tthe targetUserID
		var tempArray = thisElementID.split("_");
		var targetUserID = tempArray[1];	

		// Check for text feedback as well
		var writtenFeedbackID = "writtenFeedback_"+targetUserID+"_"+currentUserID;
		var writtenFeedback = '';
		if(jQuery("#" + writtenFeedbackID).length == 0) {
		  
			writtenFeedback = '';
		}	
		else
		{
			writtenFeedback = document.getElementById(writtenFeedbackID).value;			
		}		



		
	
		
		// Add all these values
		jQuery.ajax({
			type: 'POST',
			url: frontEndAjax.ajaxurl,
			data: {			
				"action": "addPeerFeedback",
				"userResponse": thisElementValue,
				"currentUserID": currentUserID,
				"projectID": projectID,
				"targetUserID": targetUserID,
				"writtenFeedback": writtenFeedback,
				"security": frontEndAjax.ajax_nonce				
			},
			success: function(data){}
		});
			
		

	});



	return false;		
	
}


function ajaxFeedbackRubricUpdate(targetUserID, currentUserID, projectID)
{
	var tableID = '#rubricTable_'+targetUserID;
	var formCheck  = true; // Set to true by default. If there are any empoty values the set it to false.
	
	
	jQuery(tableID+" :radio").each(function()
	{
		
		var thisRadioName = jQuery(this).attr('name');
		var myValue = jQuery('input[name='+thisRadioName+']:checked').val();
		// get the value of the radio button
		
		if(!myValue) 
		{
			formCheck=false;			
		}

	});	
	
	
	var writtenFeedbackID = "writtenFeedback_"+targetUserID+"_"+currentUserID;
	var writtenFeedback = '';
	if(jQuery("#" + writtenFeedbackID).length == 0) {
	  
		writtenFeedback = '';
	}	
	else
	{
		writtenFeedback = document.getElementById(writtenFeedbackID).value;
		
	}
	

	
	
	if(formCheck==false)
	{
		// Show the problem div
		jQuery( "#notCompleteMessage_"+targetUserID ).show( "fast");
		
	}
	else
	{


		jQuery( "#feedbackResponse_"+targetUserID ).show( "fast");
		jQuery( "#notCompleteMessage_"+targetUserID ).hide( "fast");		
		//jQuery( "#submitTableDiv_"+targetUserID ).hide( "fast");				
		

		//console.log("test");
		jQuery(tableID+" :checked").each(function() {
		
			var checkedID = (this.id);
			var tempDataArray = checkedID.split("_");
			var thisCriteriaID = tempDataArray[2];
			var thisResponseID = tempDataArray[3];
			
			
			jQuery("#rubricTable_"+targetUserID+" .td-highlight").toggleClass('td-highlight td-green-highlight');			
			
			
			
			console.log('thisCriteriaID='+thisCriteriaID+' and thisResponseID='+thisResponseID);
			
			jQuery.ajax({
				type: 'POST',
				url: frontEndAjax.ajaxurl,
				data: {			
					"action": "addPeerFeedback",
					"userResponse": thisResponseID,
					"criteriaID": thisCriteriaID,				
					"currentUserID": currentUserID,
					"projectID": projectID,
					"targetUserID": targetUserID,
					"feedbackType": "rubric",
					"writtenFeedback" : writtenFeedback,
					"security": frontEndAjax.ajax_nonce					
				},
				success: function(data){					
					console.log("OK"+data);
					
				}
			});		
		
		});	
		

		// Set the hdden input value to be true for this
		jQuery("#checkFinished"+targetUserID).val("True");		
		
		var totalSubmittedCount = 0;
		
		// Now get all the elements and check for the checkFinished if they have values. If they all have vbalues they're done
		jQuery('*[id*=checkFinished]').each(function(i, el)
		{
			// Get the value of the element
			var thisID = jQuery(el).attr('id');
			
			// Get the value
			var thisValue = jQuery('#'+thisID).val();
			if(thisValue)
			{
				totalSubmittedCount++;
			}

		});		
		
		
		if(totalSubmittedCount==expectedResponses)
		{
			console.log('COMPLETE');

			jQuery("#peerFeedbackCompletePopup").show();

		}


	}	
	
	
	
	
	



	
	return false;		
	
}