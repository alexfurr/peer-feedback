<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Give the feedback
	$args=array
	(
		"projectID"=> $projectID,
		"readOnly"=> false,
		"targetUserID"=> $userID,
		"userID" => $currentFeedbackUserID,
		"allowWrittenFeedback" => $allowWrittenFeedback,
	);
	
	$theContent.= ASPFdraw::drawRubricTable($args);
	
	
	

	
	
	
?>