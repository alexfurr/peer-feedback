<?php
class ASPFdraw {

	
	
	//This checks the page to see if its a feedback project and renders the grid etc if so
	public static function drawProjectPage( $theContent )
	{
		global $post;
		

		

		$projectID = get_the_ID();
		$thisPostType = get_post_type( $projectID );
		
		
		// Only modify if front end is a feedback project
		if($thisPostType=="peer_projects") 
		{
			$preview=false;
			if(isset($_GET['preview']))
			{
				$preview=true;
				
				// check the are editors or above

				if( !current_user_can( 'delete_others_pages'  ) )
				{
					return 'You don\'t have access to this page';	
				}
			}
			
			$currentFeedbackUserID = $_GET['userID'];	
			$currentPassword = $_GET['password'];
			
	
			// Get the user info and check against the password
			$thisUserInfo = peerFeedback_Queries::getUserFromID($currentFeedbackUserID);
			$checkPassword = $thisUserInfo['password'];
			$thisGroupID= $thisUserInfo['groupID'];		
			
			if($checkPassword<>$currentPassword)
			{
				return 'You don\'t have access to this page';	
			}			
			
			
			// First add the CSS regardlress of if they can view it
			// This prevents the 'next project' link being shown
			?>
			<style>
			.entry-meta, .post-navigation {
				display:none;
			}
			</style>                
			<?php

			
			$project_status = get_post_meta($projectID,'project_status',true);
			$feedback_status= get_post_meta($projectID,'feedback_status',true);				
			$allow_self_review= get_post_meta($projectID,'allow_self_review',true);				
			$feedbackType = get_post_meta($projectID,'feedbackType',true);
			$distributionPoints= get_post_meta($projectID,'distributionPoints',true);
			$endDate= get_post_meta($projectID,'endDate',true);
			$anon_feedback= get_post_meta($projectID,'anon_feedback',true);		
			$allowWrittenFeedback = get_post_meta($projectID,'allowWrittenFeedback',true);	
	
			
			$showPeerPage = false;
			
			if($project_status==1 || $feedback_status==1 || $preview==true)
			{
				$showPeerPage=true;
			}
			

			
			// Check if its available
			if($showPeerPage==false)
			{
				
				$theContent='<hr/>This feedback project is not available';
				return $theContent;
			}
			
			
			// Now check if they are a student attached to a group								
			// Get the group array of students for this user
			$args = array
			(
				'userID' =>$currentFeedbackUserID,
				'projectID' =>$projectID,					
				
			);
			$myStudents = peerFeedback_Queries::getMyGroupStudents ($args);
			
			// If they are not in the group then the this count will be zero.
			
			$studentCount = count($myStudents);
			
			// Add some javascript to the total number of expected students
			if($allow_self_review=="on")
			{
				$expectedSubmissions = $studentCount;
			}
			else
			{
				$expectedSubmissions = $studentCount-1;

			}
			$theContent.= '<script>
			var expectedResponses = '.$expectedSubmissions.';			
			</script>';			
			
			if($studentCount==0)
			{
				$theContent.= 'No students found';
			}
			else
			{
				
				// Check if they can view the feedback yet.					
				if($feedback_status==1)
				{
					
					$args=array
					(
						"projectID"		=>$projectID,
						"targetUserID"	=> $currentFeedbackUserID,
						"feedbackType"	=> $feedbackType,
						"anon_feedback"	=> $anon_feedback,
						"groupID"		=> $thisGroupID
					);
					return ASPFdraw::drawMyFeedback($args);	
				}
				
				
				if($endDate)
				{
					$endDate = new DateTime($endDate);
					$current_date = new DateTime();
					
					if ($endDate < $current_date)
					{
					  return 'Feedback is now closed';
					}
				}
				
				
				if ($feedbackType=="distribution")
				{
					$theContent.= 'Please distribute <b>'.$distributionPoints.'</b> across your group<br/>';
					$theContent.= '<div id="remainingPoints"></div>';
				}

				$currentStudent=1;
				// Go through the ordeded array and spit out results
				foreach($myStudents as $userInfo)
				{
					$firstName = $userInfo['firstName'];
					$lastName = $userInfo['lastName'];
					$userID = $userInfo['userID'];
					$email= $userInfo['email'];
					
					$showStudent=true;
					
					if($userID==$currentFeedbackUserID)
					{		
						$showStudent = false;					
						if($allow_self_review=="on")
						{
							$showStudent=true;	
						}
					}
					
					if($showStudent==true)
					{
						
						$theContent.='<div class="peerFeedbackStudentDiv"><h3>'.$currentStudent.'. '.$firstName.' '.$lastName.'</h3>';
						switch ($feedbackType)
						{
							case "textOnly":
								include( PFEEDBACK_PATH . 'front_forms/text-only.php' );
							break;
							
							case "distribution":
								include( PFEEDBACK_PATH . 'front_forms/distribution.php' );
							break;
							
							case "rubric":
								include( PFEEDBACK_PATH . 'front_forms/rubric.php' );
							break;	


							case "likert":
								include( PFEEDBACK_PATH . 'front_forms/likert.php' );
							break;
						}

						$currentStudent++;
						
						$theContent.='</div>'; // Close the peer feedback student div
					}
				} // End of students loop
				
				
				// Add Custom JS for type of feedback
				
				switch ($feedbackType)
				{
					case "distribution":
						// JS for showing the feedback of submission
						echo '<script>';
						echo '
							jQuery(document).ready(
							
								function(){
									jQuery( "#submitButtonDistribution" ).click(function() {
									  jQuery( "#distributeSubmitButtonDiv" ).hide( "fast", function() {
										// Animation complete.
									  });
									});
									
									jQuery( "#submitButtonDistribution" ).click(function() {
											  jQuery( "#distributeSubmitFeedbackDiv" ).show( "fast", function() {
												// Animation complete.
											  });
											});								
								});	
							';							
						
						echo '</script>';					
						
						// Single FEedback button for ALL users
						$clickAction='ajaxFeedbackDistributionUpdate(\''.$currentFeedbackUserID.'\', \''.$projectID.'\')';						
						$theContent.='<div id="distributeSubmitButtonDiv" ';
						if(!is_array($myData))
						{
							$theContent.='style="display:none"';

						}
						$theContent.=' class="peerFeedbackSubmitButtonDiv">';
						$theContent.='<a class="ek-button ek-button-primary" onclick="javascript:'.$clickAction.'" id="submitButtonDistribution" >Submit Feedback</a>';					
						$theContent.= '</div>';
						$theContent.='<div id="distributeSubmitFeedbackDiv" class="feedbackSuccess" style="display:none">';
						$theContent.='Feedback Saved';
						$theContent.='</div>';	


					break;		
					
					case "rubric":
					case "likert":
						
						
					break;		
				}// End of additional JS custom switch case
				
				
			}
			

		}
			
			
		// Finally add the complete poopup
		$theContent.=ASPFdraw::drawPeerFeedbackCompletePopup();
		
		return $theContent;
	}
	
	
	
	
	// Draws the main Rubric Table either as edit field, or read only
	public static function drawRubricTable($args)
	{
		$str=""; // Craete blank str to return
		
		$myData = ""; // Define blank var for data they have stored about other people
		
		// Create blank response option lookup array for allocating correct descriptors	
		$tempResponseOptionArray = array();
		
		// Create blank response Array
		$masterUserDataArray = array();
		
		// Interpret the args
		$projectID = $args['projectID'];
		$readOnly = $args['readOnly'];
		$userFeedbackID = $args['targetUserID'];
		$userID = $args['userID'];	
		$allowWrittenFeedback = $args['allowWrittenFeedback'];	
		
		

		if($readOnly==false)
		{
			// See if they've already stored data for this individual
			$args = array
			(
				"projectID" => $projectID,
				"userID" => $userID,
				"targetUserID" => $userFeedbackID				
			);
			$myData = peerFeedback_Queries::getUserFeedbackForTargetUser ($args); // Check against this for individual data entries
			
			// Create array using target user ID as the key and option IDs as second key
			$masterUserDataArray = array();
			
			if(is_array($myData))
			{
				foreach ($myData as $tempData)
				{
					$targetUserID =  $tempData->targetUserID;
					$criteriaID = $tempData->feedbackText;
					$userResponse= $tempData->feedbackValue;	
					$masterUserDataArray[$targetUserID][$criteriaID] = $userResponse;
				}
			}
		}
	
		
		// Get all project descriptors and put into an array
		$args = array('projectID'=> $projectID);
		$projectDescriptors = peerFeedback_Queries::getProjectCriteriaDescriptors ($args);	
		
		
		// Get the Response Options
		$args = array(
			'projectID' => $projectID
		);
		$myOptions = peerFeedback_Queries::getProjectResponseOptions ($args);
		
		
		// Get the Criteria
		$args = array(
			'projectID' => $projectID
		);
		$myCriteria = peerFeedback_Queries::getProjectCriteria ($args);
		
		if(!$myCriteria)
		{
			return 'No Criteria Found';	
		}
		
		
		
		$str.='<div id="submitTableDiv_'.$userFeedbackID.'">';	
		
			
		$str.= '<table class="rubricTable" id="rubricTable_'.$userFeedbackID.'">';
		
		if($myOptions<>false)
		{
		
			$responseOptionCount = count($myOptions);
			$str.= '<tr><th></th>';
			foreach($myOptions as $optionInfo)
			{
				$responseOption = $optionInfo->responseOption;
				$optionID= $optionInfo->optionID;
				$str.= '<th>'.$responseOption.'</th>';
				$tempResponseOptionArray[] = $optionID;
			}
			
			$str.= '</tr>';
		}
		
		if($myCriteria<>false)
		{
			foreach($myCriteria as $criteriaInfo)
			{
				$str.= '<tr>';		
				$criteria = $criteriaInfo->criteria;
				$criteriaID= $criteriaInfo->criteriaID;
				$str.= '<td>'.$criteria.'</td>';
				
				$currentRO=0; // Set the current Response Option ticker to 0 = use to lookup in the descriptors array
				
				// Get the current saved response if it exsits
				$checkResponse = "";
				if (array_key_exists($userFeedbackID, $masterUserDataArray))
				{				
					$checkResponse = $masterUserDataArray[$userFeedbackID][$criteriaID];
				}

				while ($currentRO<$responseOptionCount)
				{
					$tempOptionID = $tempResponseOptionArray[$currentRO];
					$str.='<td class="td-toggle rubricTableClickable';
					if(is_array($myData))
					{
						if($checkResponse==$tempOptionID){$str.= ' td-green-highlight ';} // Add a green highlight if its saved
					}	
					
					$str.='">';
					
					
					$str.= '<input type="radio" id="option_'.$userFeedbackID.'_'.$criteriaID.'_'.$tempOptionID.'" name="feedback_'.$criteriaID.'_'.$userFeedbackID.'" value="'.$criteriaID.'_'.$tempOptionID.'"';
					if($checkResponse==$tempOptionID){$str.= ' checked ';} // Check the radio button if its been saved
					$str.='><br/>';
		
					$descriptor = $projectDescriptors[$criteriaID][$tempOptionID];
					$str.= '<label for="option_'.$userFeedbackID.'_'.$criteriaID.'_'.$tempOptionID.'"  class="descriptor">'.$descriptor.'</label>';
					$str.= '</td>';
					$currentRO++;
				}
				$str.= '</tr>';		
			}
		}
		$str.= '</table>';
		
		// Add hidden input value for checking if all responses have ben given		
		$str.= '<input type="hidden" value="" id="checkFinished'.$userFeedbackID.'" name="check'.$userFeedbackID.'">';
		
		if($allowWrittenFeedback=="on")
		{
			
			
			$args = array(
				"projectID" => $projectID,
				"targetUserID" => $userFeedbackID,
				"userID" => $userID,
		
			);
			
			
			$str.=ASPFdraw::drawWrittenFeedbackTextarea($args);
		}	
		
		if($readOnly==false)
		{
		
			// Add the ajax call		
			$clickAction='ajaxFeedbackRubricUpdate(\''.$userFeedbackID.'\', \''.$userID.'\', \''.$projectID.'\')';

			$str.='<div class="ek-hidden" id="ek_peer_submitButton_'.$userFeedbackID.'">';
			$str.='<br/><a class="ek-button ek-button-primary" onclick="javascript:'.$clickAction.'">Submit Feedback</a>';
			$str.='</div>';
			$str.='</div>'; // End of div for entire table form wrap
			
			$str.='<div id="notCompleteMessage_'.$userFeedbackID.'" class="feedbackFail" style="display:none">You have not rated all criteria for this student</div>';
			
			$str.='<div id="feedbackResponse_'.$userFeedbackID.'" class="feedbackSuccess" ';
			
			// If data has already been saved show the feedback notice by default
			if(!is_array($myData))
			{	
				$str.='style="display:none"';
			}

			
			$str.='>Feedback Saved!</div>';
			
			
			$str.='<br/><hr/>';		
		}
		
		
		
		return $str;
		
	}	
	
	
	
	public static function drawLikertScale($args)
	{
		$str=""; // Craete blank str to return
		
		$myData = ""; // Define blank var for data they have stored about other people
		
		// Create blank response option lookup array for allocating correct descriptors	
		$tempResponseOptionArray = array();
		
		// Create blank response Array
		$masterUserDataArray = array();
		
		
		// Interpret the args
		$projectID = $args['projectID'];
		$readOnly = $args['readOnly'];
		$userFeedbackID = $args['userFeedbackID'];
		$userID = $args['userID'];	
		
		// Do we ad a textarea box or not
		$allowWrittenFeedback = get_post_meta($projectID,'allowWrittenFeedback',true);	
		
		if($readOnly==false)
		{
			// See if they've already stored data for this individual
			$args = array
			(
				"projectID" => $projectID,
				"userID" => $userID,
				"targetUserID" => $userFeedbackID				
			);
			$myData = peerFeedback_Queries::getUserFeedbackForTargetUser ($args); // Check against this for individual data entries
			
			// Create array using target user ID as the key and option IDs as second key
			$masterUserDataArray = array();
			
			if(is_array($myData))
			{
				foreach ($myData as $tempData)
				{
					$targetUserID =  $tempData->targetUserID;
					$criteriaID = $tempData->feedbackText;
					$userResponse= $tempData->feedbackValue;	
					$masterUserDataArray[$targetUserID][$criteriaID] = $userResponse;
				}
			}
		}
				
		// Get all project descriptors and put into an array
		$likertCriteria = peerFeedback_Queries::getProjectLikertScale ($projectID);	
		
		$str.='<div id="submitTableDiv_'.$userFeedbackID.'">';	

		// Get the number of options
		$optionCount = get_post_meta($projectID, 'likertPoints', true);
		

		if($likertCriteria<>false)
		{
			foreach($likertCriteria as $likertCriteriaInfo)
			{
				$criteria = $likertCriteriaInfo->criteria;
				$criteriaID= $likertCriteriaInfo->criteriaID;
				$likertLeft= $likertCriteriaInfo->likertLeft;
				$likertRight= $likertCriteriaInfo->likertRight;
				
				$str.='<h4>'.$criteria.'</h4>';
				$str.= '<table class="rubricTable" id="rubricTable_'.$userFeedbackID.'">';
				$str.= '<tr><td class="aspf_likertLeft">'.$likertLeft.'</td>';
				

				$checkResponse = '';
				if (array_key_exists($userFeedbackID, $masterUserDataArray))
				{				
					$checkResponse = $masterUserDataArray[$userFeedbackID][$criteriaID];
				}				

				$i=1;
				while ($i<=$optionCount)
				{
					$str.='<td class="td-toggle rubricTableClickable ';
					if(is_array($myData))
					{
						if($checkResponse==$i){$str.= ' td-green-highlight ';} // Add a green highlight if its saved
					}						
					
					$str.='">';					
					$str.=$i.'<br/>';					
					$str.= '<input type="radio" id="option_'.$userFeedbackID.'_'.$criteriaID.'_'.$i.'" name="feedback_'.$criteriaID.'_'.$userFeedbackID.'" value="'.$i.'"';
					if($checkResponse==$i){$str.= ' checked ';} // Check the radio button if its been saved
					$str.='><br/>';				
					$str.='</td>';
					$i++;

				}
				$str.= '<td class="aspf_likertRight">'.$likertRight.'</td>';
				$str.='</tr>';
				$str.= '</table>';
				
								
				// Get the current saved response if it exsits
				$checkResponse = "";
				if (array_key_exists($userFeedbackID, $masterUserDataArray))
				{				
					$checkResponse = $masterUserDataArray[$userFeedbackID][$criteriaID];
				}	
				
			}		

			
			
		}
		
		// Add hidden input value for checking if all responses have ben given		
		$str.= '<input type="hidden" value="" id="checkFinished'.$userFeedbackID.'" name="check'.$userFeedbackID.'">';
		
		
		
		if($allowWrittenFeedback=="on")
		{
			// Get the written feedback for this person
			$args = array
			(
				"projectID" => $projectID,
				"userID" => $userID,
				"targetUserID" => $userFeedbackID,
			);
			
			
			$str.=ASPFdraw::drawWrittenFeedbackTextarea($args);
		}
		
		if($readOnly==false)
		{
			// Add the ajax call		
			$clickAction='ajaxFeedbackRubricUpdate(\''.$userFeedbackID.'\', \''.$userID.'\', \''.$projectID.'\')';
			
			
			$str.='<div class="ek-hidden" id="ek_peer_submitButton_'.$userFeedbackID.'">';

			$str.='<br/><a class="ek-button ek-button-primary" onclick="javascript:'.$clickAction.'">Submit Feedback</a>';
			$str.='</div>';
			
			$str.='</div>'; // End of div for entire table form wrap
			
			$str.='<div id="notCompleteMessage_'.$userFeedbackID.'" class="feedbackFail" style="display:none">You have not rated all criteria for this student</div>';	
			$str.='<div id="feedbackResponse_'.$userFeedbackID.'" class="feedbackSuccess" ';
			
			// If data has already been saved show the feedback notice by default
			if(!is_array($myData))
			{	
				$str.='style="display:none"';
			}

			
			$str.='>Feedback Saved!</div>';
			
			
			$str.='<br/><hr/>';		
		}
		
		
		
		return $str;
	}
	
	
	public static function drawMyFeedback($args)
	{
		$str="";
		$targetUserID	= $args['targetUserID'];
		
		// GEt the User Info
		$userInfo  = peerFeedback_Queries::getUserFromID($targetUserID);
		$groupID = $userInfo['groupID'];
		
		// Get the Group Info		
		$args = array("groupID" => $groupID);
		$groupInfo = peerFeedback_Queries::getGroupInfo($args);		
		$projectID = $groupInfo->projectID;
		
		// Get the Project Info
		$feedbackType = get_post_meta($projectID,'feedbackType',true);
		$anon_feedback = get_post_meta($projectID,'anon_feedback',true);		
		
				
		$dataArgs = array
		(	
			"targetUserID" => $targetUserID
		);
		$myFeedback = peerFeedback_Queries::getAllUserFeedback($dataArgs);

		
		if(!$myFeedback)
		{
			return 'Nobody has given you any feedback';
		}		

		
		switch ($feedbackType)
		{
			case "distribution":
			case "likert":
		
			
				$masterGroupMarkArray = peerFeedback_utils::generateGroupMarksArray($args);
				// get the array version
				$masterGroupArray = $masterGroupMarkArray['array'];
			

				$myMarksArray = $masterGroupArray[$targetUserID];
				$myFinalScore = $myMarksArray['finalScore'];
				$groupMark = $myMarksArray['groupMark'];
				$nonSubmissionPenalty = $myMarksArray['nonSubmissionPenalty'];

				
				
				$str.='<div class="peerFinalMarkWrap">';				
				$str.= '<div><div class="boxTitle">Group Mark</div>';
				$str.='<div class="myGrade">'.$groupMark.'%</div>';
				$str.='</div>';		

				$str.= '<div><div class="boxTitle">My Grade</div>';
				$str.='<div class="myGrade">'.$myFinalScore.'%</div>';
				
				
				if($nonSubmissionPenalty>=1)
				{
					$str.='<div class="penalty failText">Includes a -'.$nonSubmissionPenalty.'% non completion penalty</div>';
				}
				$str.='</div>';
				
				
				$str.='</div>';
				

			break;
			
			
			
			case "textOnly":
			
				if($anon_feedback=="on")
				{
					// Shuffle the array
					shuffle($myFeedback);
						
				}


				$currentFeedbackNo=1;
				foreach($myFeedback as $feedbackInfo)
				{
					$thisResponse = $feedbackInfo->feedbackText;
					$thisResponse = peerFeedback_Utils::processDatabaseText($thisResponse);
					
					
					$thisUserID = $feedbackInfo->userID;
					
					$str.='<div class="feedbackTextResponse">';
					
					if($anon_feedback<>"on")
					{
						// Get this user details
						$thisUserInfo = peerFeedback_Queries::getUserFromID($thisUserID);
						$firstName = $thisUserInfo['firstName'];
						$lastName = $thisUserInfo['lastName'];
						
						$str.= '<h3>Feedback from '.$firstName.' '.$lastName.'</h3>';
					}
					else
					{
						$str.='<h3>Feedback '.$currentFeedbackNo.'</h3>';
					}
					
					$str.='<blockquote><p>'.$thisResponse.'</p></blockquote>';
					
					$str.='</div>';
					$currentFeedbackNo++;					

				}
			
			break;
			
		}
		
		
		
		// Now draw written feedback
		
		$groupFeedback = $groupInfo->groupFeedback;
		if($groupFeedback)
		{
			$groupFeedback = peerFeedback_utils::processDatabaseText($groupFeedback);
			$str.='<h2>Group Feedback</h2>';
			$str.= '<div class="feedbackTextboxResponse">'.$groupFeedback.'</div>';
		}
		
		
		
		
		// Now check for written feedback
		$allowWrittenFeedback = get_post_meta($projectID, 'allowWrittenFeedback', true);

		if($allowWrittenFeedback=="on")
		{
			$str.='<h2>Individual Feedback</h2>';
			
			
			// Get the written feedback
			$args = array(
				"targetUserID" => $targetUserID,
				"projectID"		=> $projectID,
			);
			$myWrittenFeedback = peerFeedback_Queries::getAllWrittenFeedbackForTargetUser ($args);
			
			if(is_array($myWrittenFeedback) )
			{
				foreach ($myWrittenFeedback as $writtenFeedback)
				{
					$feedbackText = peerFeedback_utils::processDatabaseText($writtenFeedback->feedbackText);
					
					$str.= '<div class="feedbackTextboxResponse">'.$feedbackText.'</div>';
				}
			}
			else
			{
				$str.='No Written Feedback Given';
			}
		}
		
		
		
		// NOw draw graphs in appropriate
			
			
			
		switch ($feedbackType)
		{
			
			
			case "rubric":
			case "likert":
			
			
				$str.='<h2>My Mark Distribution</h2>';
				// Loadup the scripts for google charts
				global $ASPF_gCharts;
				$ASPF_gCharts->enqueueScripts();
				
				
				// Now go through all the feedback and tally up the totals		
				$feedbackTotalsArray = array();
				
				
				foreach($myFeedback as $feedbackInfo)
				{
					$thisCriteriaID = $feedbackInfo->feedbackText;
					$thisResponse = $feedbackInfo->feedbackValue;
					$thisUserID = $feedbackInfo->userID;			
					$feedbackTotalsArray[$thisCriteriaID][$thisResponse][]=$thisUserID;
				}
				
				// Get the response Options and add to array
				$args = array
				(
					"projectID" => $projectID
				);
				$responseOptions = 	peerFeedback_Queries::getProjectResponseOptions ($args);
				
				// Get the crtieria
				$args = array
				(
					"projectID" => $projectID
				);				
					
				$projectCriteria = 	peerFeedback_Queries::getProjectCriteria ($args);
				$maxValue=0; // This will determine how big the chart is. Just keep a record of the max response
				
				foreach($projectCriteria as $criteriaInfo)
				{
					$chartData = array(); // Create blank Array for the data
					
					$criteriaID = $criteriaInfo->criteriaID;
					$criteria = $criteriaInfo->criteria;
					
					$str.= '<h3>'.$criteria.'</h3>';
					
					
					
					if($feedbackType=="rubric")
					{
					
						foreach($responseOptions as $optionInfo)
						{
							$optionID = $optionInfo->optionID;
							$responseOption = $optionInfo->responseOption;
							$optionLookupArray[$optionID] = $responseOption;
							
							// Get the number of response options in the master feedback array
							
							
							$tempFeedbackCheckCount=0;
							if (array_key_exists($optionID, $feedbackTotalsArray[$criteriaID]))
							{
								$tempFeedbackCheckCount = count($feedbackTotalsArray[$criteriaID][$optionID]);						
							}
							
							
							if($tempFeedbackCheckCount>=$maxValue){$maxValue=$tempFeedbackCheckCount;}
	//						$str.= 	'['.$tempFeedbackCheckArray.'] '.$responseOption.'<br/>';
							$chartData[] = array($responseOption, $tempFeedbackCheckCount);						
						}
					}
					
					
					if($feedbackType=="likert")
					{
						
						// Get the number of options
						$likertPoints = get_post_meta($projectID, 'likertPoints', true);
					
					
						$i=1;
						
						while ($i<=$likertPoints)
						{
							
							$tempFeedbackCheckCount=0;
							if (array_key_exists($i, $feedbackTotalsArray[$criteriaID]))
							{
								$tempFeedbackCheckCount = count($feedbackTotalsArray[$criteriaID][$i]);						
							}
														
							if($tempFeedbackCheckCount>=$maxValue){$maxValue=$tempFeedbackCheckCount;}
							$chartData[] = array($i, $tempFeedbackCheckCount);	

							$i++;
						}
					}	
				

					$chartsArgs = array
					(
						"chartType" => 'bar',
						"data"		=> $chartData,
						"keyName"	=> 'Criteria',
						"valName"	=> 'Number of Votes',
						"title"		=> 'Peer Feedback',
						"elementID"	=> 'crtieriaChart'.$criteriaID,
						"width"		=> '80%',
						"height"	=> '200px',
						"maxValue"	=> $maxValue
					
					);
					
					$str.= $ASPF_gCharts->draw( $chartsArgs );
					
					
				}				
			
			break;
		}
		
		
		// Group Feedback
		
		
		/*
		$dataArgs = array
		(	
			"targetUserID" => $targetUserID
		);					
		
		$feedbackAboutMe = peerFeedback_Queries::getAllUserFeedback($dataArgs);
		// Turn this array into a simple lookup array
		
		$feedbackLookupArray = array();
		foreach ($feedbackAboutMe as $tempFeedbackMeta)
		{
			$tempUserID = $tempFeedbackMeta->userID;	
			$tempScore = $tempFeedbackMeta->feedbackText;
			
			$feedbackLookupArray[$tempUserID] = $tempScore;
		}	
*/		
		
		
		// Get the group feedback
		


		
		return $str;
		
	}
	
	
	//---
	public static function drawPeerFeedbackCompletePopup () 
	{
		echo '<!-- POPUP FOOTER -->';
		
		
		$html = '';
		
		
		$html .= '<div id="peerFeedbackCompletePopup" style="display:none;">';

		$html .= 	'<h3>Thank you!</h3>';
		$html .= 	'You have now completed this peer feedback task and can close this browser window';
		$html .= 	'<br><br><button class="ek-button" id="pfPopupCloseButton">Close</button>';
		$html .= '</div>';
		
		return $html;
		
		?>
		<script>

		
		</script>
		<?php
	}	
	
	
	public static function drawWrittenFeedbackTextarea($args)
	{
		$userID = $args['userID'];
		$targetUserID = $args['targetUserID'];
		
		$writtenFeedbackInfo = peerFeedback_Queries::getWrittenFeedbackForTargetUser ($args); // Check against this for individual data entries

		$writtenFeedback = '';
		if(isset($writtenFeedbackInfo->feedbackText) )
		{
			$writtenFeedback = $writtenFeedbackInfo->feedbackText;
			$writtenFeedback = peerFeedback_utils::processDatabaseTextForTextarea($writtenFeedback);
		}
		
		$str='<div class="writtenFeedbackDiv">';
		$str.='<label for="writtenFeedback_'.$targetUserID.'_'.$userID.'">Written Feedback</label>';
		$str.='<textarea placeholder="Leave optional text feedback for this student" class="peerFeedbackTextarea" id="writtenFeedback_'.$targetUserID.'_'.$userID.'" name="writtenFeedback_'.$targetUserID.'_'.$userID.'">';
		$str.=$writtenFeedback;
		$str.='</textarea>';
		$str.='</div>';
		
		return $str;
	}

	
	
	
	
}
?>