<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Give the feedback
	$args=array
	(
		"projectID"=> $projectID,
		"readOnly"=> false,
		"userFeedbackID"=> $userID,
		"userID" => $currentFeedbackUserID
	);
	
	$theContent.= ASPFdraw::drawLikertScale($args);
	

	
	
	
?>