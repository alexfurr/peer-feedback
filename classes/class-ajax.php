<?php

class peerFeedback_AJAX
{
	
	
	
	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}	
	
	
	function addWPActions()
	{	
		// Add textual feedback for clicks
		add_action( 'wp_ajax_addPeerFeedback', array($this, 'addPeerFeedback' ));
		add_action( 'wp_ajax_nopriv_addPeerFeedback', array($this, 'addPeerFeedback' ) );	 // Allow ajax for non logged in users
		
	}



	function addPeerFeedback()
	{
		
		global $wpdb;
		
		// Check the AJAX nonce
		check_ajax_referer( 'pf_ajax_nonce', 'security' );
		
		$userResponse = $_POST['userResponse'];		
		$currentUserID = $_POST['currentUserID']; 
		$projectID = $_POST['projectID']; 	
		$targetUserID = $_POST['targetUserID'];
		$feedbackType= $_POST['feedbackType'];	
		$writtenFeedback = $_POST['writtenFeedback'];
		
		$date = date("Y-m-d H:i:s");
		
		$table_name = $wpdb->prefix . DBTABLE_PEER_FEEDBACK;
		
		// Validation - check that the IDs are integers
		if(!is_numeric($currentUserID )|| !is_numeric($projectID )|| !is_numeric($targetUserID ))
		{
			exit;	
		}		
		
		if($feedbackType=="rubric" || $feedbackType=="likert")
		{
			
			// Get the additional criteriaID
			$criteriaID = $_POST['criteriaID'];
			
			// Validation - check that both the criteriaID and the response is an int
			if(!is_numeric($userResponse) || !is_numeric($criteriaID ) )
			{
				exit;	
			}
			
			//Delete previous submitted data
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$table_name." WHERE userID=%d AND targetUserID=%d AND projectID=%d AND feedbackText = %d", $currentUserID, $targetUserID, $projectID, $criteriaID));
			
			
			//update the user response
			$myFields="INSERT into ".$table_name." (userID, targetUserID, projectID, feedbackText, feedbackValue, submitDate) ";
			$myFields.="VALUES (%d, %d, %d, %d, %d, '%s')";	
			
			$RunQry = $wpdb->query( $wpdb->prepare($myFields,
				$currentUserID,
				$targetUserID,
				$projectID,
				$criteriaID,
				$userResponse,
				$date
			));	
		}
		else
		{
			
			// Sanitise the text fiedl but keep line breaks
			//$userResponse = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $userResponse ) ) );
		
			$userResponse = peerFeedback_utils::sanitizeTextImport( $userResponse );
		
			//Delete previous	
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$table_name." WHERE userID=%d AND targetUserID=%d AND projectID=%d", $currentUserID, $targetUserID, $projectID));
		
			//update the user response
			$myFields="INSERT into ".$table_name." (userID, targetUserID, projectID, feedbackText, submitDate) ";
			$myFields.="VALUES (%d, %d, %d, '%s', '%s')";	
			
			$RunQry = $wpdb->query( $wpdb->prepare($myFields,
				$currentUserID,
				$targetUserID,
				$projectID,
				$userResponse,
				$date
			));	
		
		}
		
		// Add the written feedback if not blank
		
		
		if($writtenFeedback<>"")
		{
			
			$table_name = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_WRITTEN_FEEDBACK;
			
		
			// Sanitise the text fiedl but keep line breaks
			//$writtenFeedback = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $writtenFeedback ) ) );
			$writtenFeedback = peerFeedback_utils::sanitizeTextImport( $writtenFeedback );
		
			//check if user has answered this question before	
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$table_name." WHERE userID=%d AND targetUserID=%d AND projectID=%d", $currentUserID, $targetUserID, $projectID));
		
			//update the user response
			$myFields="INSERT into ".$table_name." (userID, targetUserID, projectID, feedbackText, submitDate) ";
			$myFields.="VALUES (%d, %d, %d, '%s', '%s')";	
			
			$RunQry = $wpdb->query( $wpdb->prepare($myFields,
				$currentUserID,
				$targetUserID,
				$projectID,
				$writtenFeedback,
				$date
			));		
		}		
		
		
		
	
		die();
	}

} // End Class




?>