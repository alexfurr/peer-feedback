<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get the data if it exists
	$args = array
	(
		"projectID" => $projectID,
		"userID" => $currentFeedbackUserID,
		"targetUserID" => $userID				
	);
	
	// Get the feedback value if it exsits
	$myData = peerFeedback_Queries::getUserFeedbackForTargetUser ($args); // Check against this for individual data entries	
	$thisValue="";
	if(is_array($myData))
	{
		$thisValue = peerFeedback_utils::parseTextFromDB($myData[0]->feedbackText);
	}	

	$textboxID = 'pFeedback_'.$userID;
	$theContent.='<textarea class="peerFeedbackTextarea" placeholder="Give Feedback Here" id="'.$textboxID.'">'.$thisValue.'</textarea>';
	
	echo '<script>';
	echo '
		jQuery(document).ready(
			function(){
				/*
				jQuery( "#submitButton_'.$textboxID.'" ).click(function() {
				  jQuery( "#submitButtonDiv_'.$textboxID.'" ).hide( "fast", function() {
					// Animation complete.
				  });
				});
				*/
				
				
				jQuery( "#submitButton_'.$textboxID.'" ).click(function() {
				  jQuery( "#feedbackResponse_'.$textboxID.'" ).show( "fast", function() {
					// Animation complete.
				  });
				});											
		
		
			});	
		';							
	
	echo '</script>';
	
	
	
	// Add the ajax call
	$clickAction='ajaxFeedbackTextUpdate(\''.$textboxID.'\', \''.$currentFeedbackUserID.'\', \''.$projectID.'\')';
	
	// Give the feedback
	
	
	$theContent.='<div id="submitButtonDiv_'.$textboxID.'" class="peerFeedbackSubmitButtonDiv">';
	$theContent.='<br/><a class="pure-button pure-button-primary"/ onclick="javascript:'.$clickAction.'" id="submitButton_'.$textboxID.'" >Submit Feedback</a>';
	$theContent.='</div>';
	
	$theContent.='<div id="feedbackResponse_'.$textboxID.'" class="feedbackSuccess" style="display:none">';
	$theContent.='Feedback Saved';
	$theContent.='</div>';
?>