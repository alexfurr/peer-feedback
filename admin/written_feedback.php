<?php
// Get project ID from GET after security check
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');
$feedback='';

if(isset($_GET['action']) )
{
	$myAction = $_GET['action'];
	switch ($myAction)
	{
		
		case "deleteFEedback":
		
			global $wpdb;
			

			
			
			
			$feedback= '<div class="updated notice"><p>Group feedback updated.</p></div>';
		break;
	}
	
}


echo '<h1>Written Feedback</h1>';

echo $feedback;


echo '<a href="?page=as-pfeedack-project-groups&projectID='.$projectID.'">Back to groups admin</a>';

// Get the groups

$args = array
(
	'projectID' => $projectID
);
$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);

if($projectGroups==false)
{
	echo 'No groups found';
}
else
{
	

	
	
	echo '<form method="post" action="?page=as-pfeedack-group-feedback&projectID='.$projectID.'&action=addGrades">';
	foreach($projectGroups as $groupInfo)
	{
		
		$groupName = $groupInfo->groupName;
		$groupID= $groupInfo->groupID;
		$groupMark= $groupInfo->groupMark;
		
		$groupFeedback= peerFeedback_utils::processDatabaseTextForTextarea($groupInfo->groupFeedback);
		echo '<div class="pf_contentBox">';
		echo '<h3>'.$groupName.'</h3>';
		
		
		$args = array
		(
			'groupID' => $groupID
		);
		
		
		
		$groupUsers = peerFeedback_Queries::getUsersInGroup($args);
		if($groupUsers==false)
		{
			echo 'No students found';
		}
		else
		{
			$thisGroupStudentCount = count($groupUsers);
			
			// Create temproray lookup array for this group
			$tempUserLookupArray = array();
			foreach($groupUsers as $userInfo)
			{
				$firstName = $userInfo['firstName'];
				$lastName = $userInfo['lastName'];
				$userID = $userInfo['userID'];
				
				$tempUserLookupArray[$userID] = $firstName.' '.$lastName;
								

				
				
			}
			
			
			
			// Go through the ordeded array and spit out results
			foreach($groupUsers as $userInfo)
			{
				$firstName = $userInfo['firstName'];
				$lastName = $userInfo['lastName'];
				$userID = $userInfo['userID'];
								
				$args = array(
				"projectID"=> $projectID,
				"targetUserID"	=> $userID,
				);
				$myWrittenFeedback = peerFeedback_Queries::getAllWrittenFeedbackForTargetUser ($args);
				

				
				if(is_array($myWrittenFeedback) )
				{
					
					echo '<h3>'.$firstName.' '.$lastName.'</h3>'; 
					
					foreach ($myWrittenFeedback as $myFeedback)
					{
						$feedbackText = peerFeedback_utils::processDatabaseText($myFeedback->feedbackText);
						$writtenBy = $myFeedback->userID;	
						$writtenByName = $tempUserLookupArray[$writtenBy];
					
						
						echo '<div class="feedbackTextboxResponse">';
						echo $feedbackText;
						echo '<span class="smallText fadeText">Written by : '.$writtenByName.'</span><hr/>';
						echo '</div>';
						
						
					}
				}
				
				
				
			}

		}
		echo '</div>';
	}
	echo '</form>';
}




?>
