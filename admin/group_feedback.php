<?php
// Get project ID from GET after security check
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');
$feedback='';

if(isset($_GET['action']) )
{
	$myAction = $_GET['action'];
	switch ($myAction)
	{
		
		case "addGrades":
		
			global $wpdb;
			

			$groupsTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_GROUPS;
			foreach ($_POST as $KEY => $VALUE)
			{
				
				
				//if ites the gorup mark add that if its an integer
				if (strpos($KEY, 'groupMark_') !== false) {
					
					if(peerFeedback_utils::validateInputNumber($VALUE) )
					{			
						
						// Get the group ID
						$groupID = explode('_', $KEY)[1];
											
						$groupMark = $VALUE;
											
					
						//update the groups table
						$wpdb->update( 
							$groupsTable, 
							array( 
								'groupMark' => $groupMark	// integer (number) 
							), 
							array( 'groupID' => $groupID ), 
							array( 
								'%d'	// value2
							), 
							array( '%d' ) 
						);
					}				
				}
				
				
				// Add the gorup feedback text
				if (strpos($KEY, 'groupFeedback_') !== false) {
					
					$groupFeedback = peerFeedback_utils::sanitizeTextImport($VALUE);
						
					// Get the group ID
					$groupID = explode('_', $KEY)[1];
										
					$groupMark = $VALUE;
					
					
				
					//update the groups table
					$wpdb->update( 
						$groupsTable, 
						array( 
							'groupFeedback' => $groupFeedback
						), 
						array( 'groupID' => $groupID ), 
						array( 
							'%s'	// value2
						), 
						array( '%d' ) 
					);
				}
				
				
				
				// if its a group feedback sanoitize and add that
				
			}
			
			
			
			$feedback= '<div class="updated notice"><p>Group feedback updated.</p></div>';
		break;
	}
	
}


echo '<h1>Add Group Marks and Feedback</h1>';

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
			
			echo '<table class="peerFeedbackAdminTable">';
			// Go through the ordeded array and spit out results
			foreach($groupUsers as $userInfo)
			{
				$firstName = $userInfo['firstName'];
				$lastName = $userInfo['lastName'];
				$userID = $userInfo['userID'];
				$email= $userInfo['email'];
				$password= $userInfo['password'];				
				
				
				echo '<tr>';

				echo '<td>'.$lastName.', '.$firstName.'</td>';

				
				echo '</tr>';
			}
			echo '</table>';
			
			echo '<div style="margin-top:20px">';
			echo '<input size="3" type="text" value="'.$groupMark.'" name="groupMark_'.$groupID.'" id="groupMark_'.$groupID.'">% ';
			echo 'Group Mark';
			
			echo '<h3>Group Feedback</h3>';
			echo 'This will be shown to all students in this group<br/>';
			echo '<textarea class="peerFeedbackAdminTextarea" id="groupFeedback_'.$groupID.'" name="groupFeedback_'.$groupID.'">';
			echo $groupFeedback;
			echo '</textarea>';
			echo '</div>';
			
			echo '<input type="submit" value="Submit">';
			
			
		}
		echo '</div>';
	}
	echo '</form>';
}




?>
