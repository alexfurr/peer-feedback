<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get the data if it exists
	$args = array
	(
		"projectID" => $projectID,
		"userID" => $currentFeedbackUserID,
		"targetUserID" => $userID				
	);
	
	$myData = peerFeedback_Queries::getUserFeedbackForTargetUser ($args); // Check against this for individual data entries	
	
	$thisValue="";
	if(is_array($myData))
	{
		$thisValue = $myData[0]->feedbackText;
	}


	$textboxID = 'pFeedback_'.$userID;
	$theContent.='<input maxlength="3" type="text" class="peerFeedbackDistributeTextbox" placeholder="0" id="'.$textboxID.'" ';
	if($thisValue)
	{
		$theContent.=' value="'.$thisValue.'"';
	}
	$theContent.='name="pf_distributionValue" onkeyup="javascript:calculateRemainingDistributionPoints('.$distributionPoints.')"> points';

	
	if($allowWrittenFeedback=="on")
	{
		$theContent.=ASPFdraw::drawWrittenFeedbackTextarea($args);
	}
	
	?>