<?php

// Get project ID from GET after security check
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');

global $wpdb;


// GEt the project Status
$project_status = get_post_meta( $projectID, 'project_status', true );
$feedbackType = get_post_meta( $projectID, 'feedbackType', true );
$allow_self_review = get_post_meta( $projectID, 'allow_self_review', true );
$allowWrittenFeedback = get_post_meta( $projectID, 'allowWrittenFeedback', true );
$project_title = get_the_title($projectID);



echo '<h1>'.$project_title.' : Student Groups</h1>';
echo '<a href="edit.php?post_type=peer_projects" class="backLink">Back to Projects</a><hr/>';


if($project_status==1)
{
	echo '<div class="update-nag  notice"><p>This feedback project is current live and so groups cannot be modified or deleted</p></div>';
}
else
{
	
	

	?>


	<button class="button-secondary" id="uploadOpenButton">Upload your groups</button>
	<a class="button-secondary" href="?page=as-pfeedack-group-feedback&projectID=<?php echo $projectID;?>">Add group marks and feedback</a>
	<?php
	if ($allowWrittenFeedback=="on")
	{
	?>
	
	
		<a class="button-secondary" href="?page=as-pfeedack-written-feedback&projectID=<?php echo $projectID;?>">View / edit written feedback</a>
	<?php
	}
	?>
	
    <div id="uploadDiv" style="display:none">
    <h2>How to upload your feedback groups</h2>    
    <form name="csvUploadForm" action="options.php?page=as-pfeedack-project-groups&projectID=<?php echo $projectID ;?>&action=groupsUpload"  method="post" enctype="multipart/form-data">
    Upload your groups list as a CSV file with the following columns:<br/>
    Group Name, First Name, Last Name, Email address<br/>
    <input type="file" name="csvFile" size="20"/><br/>
    <input type="submit" value="Upload" name="submit" class="button-primary" />
    <?php
	// Add nonce
	wp_nonce_field('groupUploadNonce');    
	?>
    
    </form>
    <br>
    <hr>
    </div>

	<script>
    jQuery( "#uploadOpenButton" ).click(function() {
      jQuery( "#uploadDiv" ).toggle( "fast" )
    });
    </script>
    
    <?php
}



// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['action'] ) )
{		
	
	
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'groupUploadNonce' ) )
	{
	
	
	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
	
		case "groupsUpload":
			$errorString = '';
			$errorUserArray=array(); // Create an array so that we can add problem users to
		
			$uploadPath = peerFeedback_utils::getTempUploadDir();						
			$newFilename = $uploadPath.'/tempPeerImport.csv';
			
			echo 'upload ot '.$newFilename;


			if(isset($_FILES['csvFile']['tmp_name']))
			{
				$groupsTable = $wpdb->prefix.DBTABLE_PEER_FEEDBACK_GROUPS;
				$usersTable= $wpdb->prefix.DBTABLE_PEER_FEEDBACK_USERS;				
				
				
				$args = array
				(
					'projectID' => $projectID
				);
				$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);
				
				if($projectGroups)
				{
					foreach($projectGroups as $groupInfo)
					{
						$groupID= $groupInfo->groupID;
						// Delete old group records
						$delete = $wpdb->query("DELETE FROM ".$groupsTable." WHERE groupID = ".$groupID);
						
						// Delete old user records
						$delete = $wpdb->query("DELETE FROM ".$usersTable." WHERE groupID = ".$groupID);
					}
				}				
				
				move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
				
				// Go through the CSV stuff
				ini_set('auto_detect_line_endings',1);
				$handle = fopen($newFilename, 'r');
				$userCount = 0; // Counts the number of students
				$errorCount = 0;
				$masterGroupsArray = array(); // Create empty array for storing group info
				$userImportErrorArray = array(); // Create error message array for any problems
				$groupNotGivenArray = array(); // Create error message array for any problems			
				$uniqueGroupArray = array(); // Array that contains just the group names
			
				// Delete old records
				//$delete = $wpdb->query("DELETE FROM ".$wpdb->base_prefix . BUZZ_TABLE_NAME_MENTORS." WHERE mentorType='".$mentorType."'");
			

				// Create the initial default arrays				
				$previousGroup = "";
				$currentCSVline=1;
				
				//echo '<h1>Data</h1>';
				while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
				{
					$currentGroup= peerFeedback_utils::sanitizeTextImport($data[0]);
					$currentGroup = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $currentGroup); // Remove hidden crap
//					$currentUsername = strtolower(peerFeedback_utils::sanitizeTextImport($data[1]));
					$currentFirstName = ucfirst(peerFeedback_utils::sanitizeTextImport($data[1]));
					$currentLastName = ucfirst(peerFeedback_utils::sanitizeTextImport($data[2]));
					$currentEmail = $data[3];
					
					$uniqueGroupArray[] = $currentGroup; // Add the group name to the array = we'll make sure they are unique later

					
					// Check for blank entires and ignore
					if($currentFirstName<>"" || $currentLastName<>"" || $currentEmail<>"") // only process if there is some data
					{
						
						if(is_email($currentEmail)) // Fiorst check they ahve a valid email
						{
							if($currentGroup=="")
							{
								$currentGroup = $previousGroup;
							}
							
							
							$previousGroup = $currentGroup;
							
							if($currentGroup<>"")
							{
								if($currentFirstName=="" && $currentLastName=="")
								{
									// Missing both surname AND first name
									$userImportErrorArray['nameProblem'][] = $currentFirstName.' '.$currentLastName .'('.$currentEmail.') - Row '.$currentCSVline; 
									$errorCount++;
									
								}
								else
								{
									// The are GOOD so add them to the array for data population
									$masterGroupsArray[$currentGroup][] = array(
										"firstName" => $currentFirstName,
										"lastName" => $currentLastName,
										"email" => $currentEmail
									);
									
								}
								$userCount++;
								
								
							}
							else // No Group listed - would only happen if the first entry has no group listed
							{
								$userImportErrorArray['noGroup'][] = $currentFirstName.' '.$currentLastName .' ('.$currentEmail.')  - Row '.$currentCSVline; // Add the username to the NOT FOUND in WP database
								$errorCount++;
								$userCount++;
								
							}
						} // End if Current Email is not blank
						else // Current email is blank
						{
							$userImportErrorArray['noEmail'][] = $currentFirstName.' '.$currentLastName .' ('.$currentEmail.') - Row '.$currentCSVline; // Add the username to the NOT FOUND in WP database
							$errorCount++;
						}
					}
					
					$currentCSVline++; // Increment the line for error reporting

				}
				
				echo '<div class="updated notice"><p><b>'.$userCount.'</b> students processed.</p></div>';


				
				if($errorCount>=1)				
				{
					// open the error admin feedback div
					echo '<div class="error  notice"><p>';
					
					echo '<b>'.$errorCount.'</b> problems were ecountered<br/>';
	
					// Show errors for missing invlaid emails
					if(is_array($userImportErrorArray['noEmail']))
					{
						echo '<h3>The following people had an invalid email or no email address</h3>';
						echo '<ul>';
						foreach	($userImportErrorArray['noEmail'] as $errorInfo )
						{
							echo '<li>'.$errorInfo.'</li>';
						}
						echo '</ul>';
					}
					
					// Show errors for missing groups
					if(is_array($userImportErrorArray['noGroup']))
					{
						echo '<h3>The following people had no group assigned</h3>';
						echo '<ul>';
						foreach	($userImportErrorArray['noGroup'] as $errorInfo )
						{
							echo '<li>'.$errorInfo.'</li>';
						}
						echo '</ul>';						
						
					}
					
					// Show errors for missing names
					if(is_array($userImportErrorArray['nameProblem']))
					{
						echo '<h3>The following people had no name</h3>';
						echo '<ul>';
						foreach	($userImportErrorArray['nameProblem'] as $errorInfo )
						{
							echo '<li>'.$errorInfo.'</li>';
						}
						echo '</ul>';						
								
					}					
					
					echo '</div>'; // Close the feedback error div
				}

				
				// Go through the group array and add all unique gruop names to a new array
				$uniqueGroupArray = array_unique($uniqueGroupArray, SORT_REGULAR);

				
				
				foreach	($uniqueGroupArray as $originalGroupKey => $groupName )
				{
					
					if($groupName<>"")
					{
				
						// Add the unique group names to the groups table
						$msg = $wpdb->insert( 
							$groupsTable, 
							array( 
								'projectID'	=> $projectID,
								'groupName'	=> $groupName,
							),
							array( '%d', '%s' )
						);	
						
						$thisGroupID = $wpdb->insert_id;
						
						//echo 'Group '.$groupName.' Added to DB with KEY '.$thisGroupID.'<hr/>';
						
						// Set the key of the array now to tbe thenew insert value
						$uniqueGroupArray[$thisGroupID] = $groupName;
						unset($uniqueGroupArray[$originalGroupKey]);
					}
					
				}
				
				
								
				foreach	($masterGroupsArray as $groupName => $groupUsers )
				{
					//echo '<h3>'.$groupName.'</h3>';
					
					/// Get the database group ID frmo the uniqueGroupArray
					$groupID= array_search($groupName, $uniqueGroupArray); //
					
					// Get the insertID from the uniqueGroupArray
					foreach($groupUsers as $userInfo)
					{
						//echo 'DB ID = <b>'.$groupID.'</b><br/>';	
						//echo 'Add '.$userID.' to '.$groupID.'<hr/>';
						
						$firstName = $userInfo['firstName'];
						$email = $userInfo['email'];
						$lastName = $userInfo['lastName'];
						
						// Generate an alphanumeric password
						$password = peerFeedback_utils::generatePassword();
																	
						
						// Add the unique group names to the groups table
						$msg = $wpdb->insert( 
							$usersTable, 
							array( 
								'groupID'	=> $groupID,
								'firstName'	=> $firstName,
								'lastName'	=> $lastName,
								'email'	=> $email,
								'password' => $password
							),
							array( '%d', '%s', '%s', '%s', '%s' )
						);							
						
					}
				}
						
			} // End if file type is CSV
			// Now delete the temp file
			
			unlink ($newFilename);	
			
			
			
			
		} // End of nonce check
	}// End if grouopsUpload case	
} // End is action






echo '<h2>Current Groups</h2>';

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
	foreach($projectGroups as $groupInfo)
	{
		
		$groupName = $groupInfo->groupName;
		$groupID= $groupInfo->groupID;
		$groupMark = $groupInfo->groupMark;
		
		
		
		$args = array("groupID" => $groupID);
		$masterGroupMarkReturnArray = peerFeedback_utils::generateGroupMarksArray($args);

		// get the array version
		$masterGroupMarkArray = $masterGroupMarkReturnArray['array'];
		$masterGroupMarkTableArray = $masterGroupMarkReturnArray['string'];

				
		$colspan = 7;
		if($feedbackType<>"textOnly")
		{
			$colspan=6;
		}		
		
		echo '<div class="pf_contentBox">';
		echo '<table class="peerFeedbackAdminTable">';
		echo '<tr>';
		echo '<td colspan="'.$colspan.'">';
		echo '<h3>'.$groupName.'</h3>';
		echo '</td>';		
		if($feedbackType<>"textOnly")
		{
			if($groupMark=="")
			{
				$groupMarkText = 'Not Given';				
			}
			else
			{
				$groupMarkText = $groupMark.'%';
			}
			echo '<td>Group Mark<br/><b> '.$groupMarkText.'</b></td>';
		}			
		
		echo '</tr>';
		
		
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
			// If they haven't allowed self review then subtract one from the totla student count check
			if($allow_self_review<>"on")
			{
				$thisGroupStudentCount = ($thisGroupStudentCount-1);
			}
			
			if($feedbackType=="rubric" || $feedbackType=="likert")
			{
				// if its a rubric then multiply the group count by this which is the expected numnber of dfeedback items
				// Get the number of criteria - dividwe by that count to get the number of people rated
				$args = array ( "projectID"=>$projectID);
				$myCriteria = peerFeedback_Queries::getProjectCriteria($args);
				$criteriaCount = count($myCriteria);
			}
			
			// Go through the ordeded array and spit out results
			foreach($groupUsers as $userInfo)
			{
				$firstName = $userInfo['firstName'];
				$lastName = $userInfo['lastName'];
				$userID = $userInfo['userID'];
				$email= $userInfo['email'];
				$password= $userInfo['password'];	

				$myMarksArray = $masterGroupMarkArray[$userID];
				$myFinalScore = $myMarksArray['finalScore'];
				
				
				// Current Project Permalink
				$thisPermalink = get_permalink( $projectID );
				
				echo '<tr>';
				echo '<td width="50">';
				$args = array
				(
					"size"=> 50
				);
				$avatar = get_avatar_url($email, $args);
				echo '<img src="'.$avatar.'">';	

				echo '</td>';
				echo '<td>'.$lastName.', '.$firstName.' ('.$userID.')</td><td width="200px"><a href="mailto:'.$email.'">'.$email.'</a></td>';
				
				
				// Check if Feedback given or not 
				echo '<td width="200px">';
				// Check to see if they have given feedback or not

					
				// Get any data and check against the student count in this group
				$args = array("userID" => $userID);
				$feedbackFromUser = peerFeedback_Queries::getUserFeedbackFromUser ($args);

				$feedbackCount=0;
				
				if(is_array($feedbackFromUser))
				{
					$feedbackCount = count($feedbackFromUser);
					
					
					switch ($feedbackType)
					{
						
						// Divide by the number of people for rubric
						case "rubric":
							$feedbackCount = $feedbackCount / $criteriaCount;
						break;
						
						case "likert":
							$feedbackCount = $feedbackCount / $criteriaCount;
						break;
						
						
						
					}
	
				}
				echo'<span class="';
				if($thisGroupStudentCount==$feedbackCount)
				{
					echo 'successText';
				}
				else
				{
					echo 'failText';
				}	
				echo '">';
				echo $feedbackCount.'/'.$thisGroupStudentCount;													
				echo ' feedback given</span>';		
				echo '</td>';
				
				
				echo '<td width="100"><a href="'.$thisPermalink.'&password='.$password.'&userID='.$userID.'&preview=true" target="blank" class="button-secondary">Preview</a></td>';
				echo '<td width="100"><a href="?page=as-pfeedack-student-feedback&projectID='.$projectID.'&userID='.$userID.'&groupID='.$groupID.'" class="button-secondary">Feedback Received</a></td>';
				if($feedbackType<>"textOnly")
				{
					
					if($groupMark=="")
					{
						$myFinalScore = '-';
					}
					
						echo '<td width="100">';
						echo $myFinalScore.'%';				
						echo '</td>';
				}
				
				
				echo '</tr>';
			}
			
		}
		echo '</table>';
		
		if($feedbackType<>"textOnly")
		{
		
			echo '<br/><button class="button-secondary" id="breakdown'.$groupID.'">View detailed Feedback Breakdown</button>';
			echo '<div style="display:none" id="detailedBreakdown'.$groupID.'">';
			echo $masterGroupMarkTableArray;
			echo '</div>';
			
			echo '<script>
			jQuery( "#breakdown'.$groupID.'" ).click(function() {
			  jQuery( "#detailedBreakdown'.$groupID.'" ).toggle("fast");
			});
			</script>';
		}
		
		echo '</div>';

	}
}




?>
