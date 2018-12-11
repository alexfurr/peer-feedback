<?php
class peerfeedbackActions {

	
	
	//This checks the page to see if its a feedback project and renders the grid etc if so
	static function projectLaunch( $projectID)
	{
		
		// They need to be admin to do this
		if(!current_user_can('manage_options')){exit;}
		
		
		update_post_meta( $projectID, 'project_status', 1 ); // Turn the project on
		
		// Get all students in group
		$args = array
		(
			'projectID' => $projectID
		);
		$studentsArray = peerFeedback_Queries::getAllProjectStudents($args);
		
		$project_title = get_the_title($projectID);		
		
		
		// Current Project Permalink
		$thisPermalink = get_permalink( $projectID );	

		
		
		foreach($studentsArray as $studentInfo)
		{
			
			$uniqueURL = $thisPermalink.'&password='.$studentInfo['password'].'&userID='.$studentInfo['userID'];
			$to = $studentInfo['email'];
			$subject = 'Peer feedback for the project '.$project_title;
			$message = "Dear ".$studentInfo['firstName']." ".$studentInfo['lastName'].",\r\n\r\n";
			$message.= "You have been requested to give feedback on the people in your group for the project '".$project_title."'\r\n";
			$message.= "Please click the link below to begin the feedback process\r\n\r\n";			
			
			$message.= $uniqueURL."\r\n\r\n";
			$message.= "Do not reply to this message";
			 
			wp_mail( $to, $subject, $message );
		}
				
		echo '<div class="updated notice"><p>Project Launched</p></div>';		
		
	
	}
	
	
	static function enableFeedback($projectID)
	{
		
		// They need to be admin to do this
		if(!current_user_can('manage_options')){exit;}
		
		
		update_post_meta( $projectID, 'feedback_status', 1 ); // Enable Feedback	
		
		// Get PRoject Info
		$project_title = get_the_title($projectID);		
		
		
		// Get all students in group
		$args = array
		(
			'projectID' => $projectID
		);
		$studentsArray = peerFeedback_Queries::getAllProjectStudents($args);
		
		// Current Project Permalink
		$thisPermalink = get_permalink( $projectID );	

		foreach($studentsArray as $studentInfo)
		{
			
			$uniqueURL = $thisPermalink.'&password='.$studentInfo['password'].'&userID='.$studentInfo['userID'];
			$to = $studentInfo['email'];
			$subject = 'Feedback now available for the project '.$project_title;
			$message = "Dear ".$studentInfo['firstName']." ".$studentInfo['lastName'].",\r\n\r\n";
			$message.= "Feedback is now available for the group project ".$project_title.". Click the link below to view your feedback\r\n";
			$message.= $uniqueURL."\r\n\r\n";
			$message.= "Do not reply to this message";
			 
			wp_mail( $to, $subject, $message );
		}		
		
		
		echo '<div class="updated notice"><p>Feedback Enabled</p></div>';
		
		
	}
	
	
	
	
}
?>