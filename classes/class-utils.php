<?php
class peerFeedback_utils
{

	
	public static function sanitizeTextImport( $input )
	{
		$output = wp_kses_post( $input );
		return $output;		
	}
	
	public static function processDatabaseText($input)
	{

			$output = sanitize_textarea_field( $input );
			$output = wpautop( $output );
			$output = stripslashes($output);
			
			
			return $output;
		
	}
	
	
	public static function processDatabaseTextForTextarea($input)
	{

			$output = esc_textarea($input); 
			$output = sanitize_textarea_field( $output );
			$output = stripslashes($input);
			
			return $output;
		
	}		
	

	public static function generatePassword($length = 16) {
		$chars = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
		$count = mb_strlen($chars);
	
		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
	
		return $result;
	}	
	

	public static function securityCheckAdminPages($varName)
	{
		
		if ( ! defined( 'ABSPATH' ) ) 
		{
			die();	// Exit if accessed directly
		}
		
		// Only let them view if admin		
		if(!current_user_can('manage_options'))
		{
			die();
		}		
		
		
		// Only Load page if the GET is a valid number
		if(isset($_GET[$varName]))
		{
			$$varName = $_GET[$varName];
			
			if(!is_numeric($$varName))
			{
				die();
			}
			else
			{
				
				return $$varName;
			}
		}
		else
		{
			die();
		}

	}
	
	public static function validateInputDate($input)
	{
		$d = DateTime::createFromFormat('Y-m-d', $input);
		return $d && $d->format('Y-m-d') === $input;
	}
	
	public static function validateInputCheckbox($input)
	{
		$output="";
		if($input=="on")
		{
			$output = $input;
		}
		return $output;
	}	
	
	public static function validateInputNumber($input)
	{
		$output="";
		if(is_numeric($input)){$output = $input;}
		return $output;
	}
	
	
	
	// This is the main function for generating an array of marks for all students in a group
	static function generateGroupMarksArray($args)
	{
		
		$tableStr = ''; // Define the table string
		$masterGroupArray = array();
		$groupID = $args['groupID'];
		
		$args = array (
			"groupID"	=> $groupID,
		);
		$groupInfo = peerFeedback_Queries::getGroupInfo ($args);
		$groupMark = $groupInfo->groupMark;
		
		$projectID = $groupInfo->projectID;
		
		// GEt the feedback type
		$feedbackType = get_post_meta($projectID,'feedbackType',true);
	
		// Get the Project weighting and any penalties
		$nonCompletionPenalty = get_post_meta($projectID,'nonCompletionPenalty',true);
		$feedbackWeighting = get_post_meta($projectID,'feedbackWeighting',true);
		$allow_self_review = get_post_meta( $projectID, 'allow_self_review', true );
		$distributionPoints = get_post_meta( $projectID, 'distributionPoints', true );
		

		if($feedbackWeighting==""){$feedbackWeighting=100;}
		if($nonCompletionPenalty==""){$nonCompletionPenalty=10;}
		
		// Get the students in the group
		$studentsInGroup = peerFeedback_Queries::getUsersInGroup($args);
		$studentsInGroupLookup = array();
		
		
		if(is_array($studentsInGroup) )
		{

			// Generate lookup array based on user ID
			foreach ($studentsInGroup as $studentMeta)
			{
				$userID = $studentMeta['userID'];
				$firstName = $studentMeta['firstName'];
				$lastName = $studentMeta['lastName'];
				$studentsInGroupLookup[$userID] = $firstName.' '.$lastName;
				
			}
		}
		
		// Create blank array to store the totals with the userID as key
		$tempDataCountArray = array();
		$tempDataMarkArray = array();
		
		$studentCount = count($studentsInGroup);	

		// GEt all group feedback		
		$allGroupFeedback = peerFeedback_Queries::getAllGroupFeedback($groupID);
		
	
		// Check how many students gave marks to get the fudge factor
		$givenMarksArray = array();
		
		
		$normalisedMarksArray = array();
		if(is_array($allGroupFeedback) )
		{
			
			foreach ($allGroupFeedback as $feedbackMeta)
			{
				$userID = $feedbackMeta->userID;
				$targetUserID = $feedbackMeta->targetUserID;
				
				if($feedbackType=="distribution")
				{			
					$feedbackValue = $feedbackMeta->feedbackText;

			
					$normalisedScore = $feedbackValue/$distributionPoints;	
					$normalisedMarksArray[$userID][$targetUserID] = $normalisedScore;
				}
					
				if($feedbackType=="likert")
				{					
					
					$feedbackValue = $feedbackMeta->feedbackValue;
					
					// Create temp array which we can then use to calculate the normalised score
					$normalisedMarksArray[$userID][$targetUserID] = $feedbackValue;	
					
				}					
			}
						
			
			// If its likert calculate the total points allocated and also if they havehave marked all students or not

			
			if($feedbackType=="likert")
			{
				foreach ($normalisedMarksArray as $markerID => $receiverIDArray)
				{
										
					$markedCount = count($receiverIDArray);
					
					$expectedCount = $studentCount-1;
					if($allow_self_review=="on")
					{
						$expectedCount = $studentCount;
					}
					
					$addMarksToFinalArray=true;
					if($markedCount<$expectedCount)
					{
						// Remove these rankings as they do not count
						$addMarksToFinalArray=false;
						
						// unser this user marks
						unset ($normalisedMarksArray[$markerID]);
						
					}

					
					if($addMarksToFinalArray==true)
					{
						$tempTotalMarksGiven = 0;
						foreach ($receiverIDArray as $thisTargetUserID => $thisMarksGiven)
						{
							$tempTotalMarksGiven = $tempTotalMarksGiven+$thisMarksGiven;
						}						
						
						
						// Go through the array again and multiply by the factor
						foreach ($receiverIDArray as $thisTargetUserID => $thisMarksGiven)
						{
							$thisNormalisedScore = round($thisMarksGiven/$tempTotalMarksGiven, 2); // This is because they are all given 100 to distrobut	
							
							// Repopulate the array with this new normalised value
							$normalisedMarksArray[$markerID][$thisTargetUserID] = $thisNormalisedScore;
								
						}	
					}
				}
			}		
		}

		
		$studentsWhoGaveFeedback = count($normalisedMarksArray);
		
		$fudgeFactor = 1; // Set to 1 be default
		
		if($studentsWhoGaveFeedback>0)
		{
			//Calculate Fudge Factor
			$fudgeFactor = $studentCount / $studentsWhoGaveFeedback;
		}
		
		
		// Mark amount to adjust
		$percentWeighting = "0.".$feedbackWeighting;
		
		// Adjust for 100%
		if($feedbackWeighting==100)
		{
			$percentWeighting= 1;
		}
		
		
		// Also assign how much the PF will affect final score
		$nonPercentWeighting = 1-$percentWeighting;
		$markToAdjust = $groupMark * $percentWeighting;
		$markNotToAdjust = $groupMark * $nonPercentWeighting;		

		$tableStr.='<br/><table>';
		$tableStr.= '<tr><td width="150px">Group Mark</td><td>'.$groupMark.'%</td></tr>';
		$tableStr.= '<tr><td>Percent Weighting</td><td>'.$feedbackWeighting.'% of final mark</td></tr>';
		$tableStr.= '<tr><td>Mark to Adjust</td><td>'.$markToAdjust.'% of final mark</td></tr>';
		$tableStr.= '<tr><td>PA Fudge Factor</td><td>'.$fudgeFactor.'</td></tr>';
		$tableStr.='</table>';
		
	
		// Go through the master array creating usable lookup mini arrays		
		$marksGivenByEachUserArray = array(); // Create array for overview of marks given
		
		if(is_array($studentsInGroup) )
		{
			

			// Create the Table Header
			foreach($studentsInGroup as $tempUserInfo)
			{
				$thisUserID = $tempUserInfo['userID'];
				$firstName= $tempUserInfo['firstName'];	
				$lastName= $tempUserInfo['lastName'];
				
			}
			
			
			
			foreach($studentsInGroup as $tempUserInfo)
			{
				$thisUserID = $tempUserInfo['userID'];
				$firstName= $tempUserInfo['firstName'];	
				$lastName= $tempUserInfo['lastName'];			
				

							
				// Get all marks for this student and add them up				
				$totalScore = 0;
				foreach ($normalisedMarksArray as $markingUserID => $givenMarks)
				{
					
					$thisGrade = '';
					// Go through the given makrs looking for an array with key of that user ID
					if(array_key_exists($thisUserID, $givenMarks) )
					{
						$thisGrade = $givenMarks[$thisUserID];
						$totalScore = $totalScore+$thisGrade;
					}	
					
					$marksGivenByEachUserArray[$markingUserID][$thisUserID] = $thisGrade;
					
					
				}

				
				// Now see if this student SUBMITTED any marks
				$applyNonSubmissionPenalty=0; // By default do not apply penalty
				if(!array_key_exists($thisUserID, $normalisedMarksArray) )
				{
					// They didn't submit so apply non submission penalty
					$applyNonSubmissionPenalty = $nonCompletionPenalty;
				}
				
				
				// Get the final webPA score by multiplying by fudge factor
				$finalWebPA_score = round($totalScore * $fudgeFactor, 2);
				
				// Calculate thte final score adjusted for the project weghting
				
				

				
				$finalActualScore = ($markToAdjust*$finalWebPA_score) + $markNotToAdjust;				
				
				if($finalActualScore>100){$finalActualScore=100;} // Cant get above 100%
				if($finalActualScore<0){$finalActualScore=0;} // Cant get less that 0%
				// Round the score
				$finalActualScore = round($finalActualScore, 0);
				
				$prePenaltyScore = $finalActualScore;
				
				// Remove any penalties
				$finalActualScore = $finalActualScore-$applyNonSubmissionPenalty;
				
				$masterGroupArray[$thisUserID] = array(
				"prePenaltyScore"	=> $prePenaltyScore,
				"finalScore" => $finalActualScore,
				"totalWebPA_Score" => $totalScore,
				"adjustedFinalWebPA_score" => $finalWebPA_score,
				"fudgeFactor" => $fudgeFactor,
				"groupMark"	=> $groupMark,
				"nonSubmissionPenalty"	=> $applyNonSubmissionPenalty,
				"firstName"	=> $firstName,
				"lastName"	=> $lastName,
				);
				
			}
		}


		
		$tableStr.= '<table class="peerFeedbackAdminTable"><tr><td></td>';
		
		
		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{
			
			$tableStr.='<td><strong>'.$thisName.'</strong></td>';
			
		}
		$tableStr.= '<td>Total</td></tr>';
		
		

		foreach ($studentsInGroupLookup as $markingUserID => $thisName)
		{
			
			$totalGiven = '0';
	
			$tableStr.='<tr><td>'.$thisName.'</td>';
			
			foreach ($studentsInGroupLookup as $thisUserID => $thisName)
			{
				$thisGrade = '';
				
				if(isset($marksGivenByEachUserArray[$markingUserID][$thisUserID]) )
				{
					$thisGrade = $marksGivenByEachUserArray[$markingUserID][$thisUserID];
				}

				
				if($thisGrade)
				{
					$totalGiven = $totalGiven + $thisGrade;
				}
				else
				{
					$thisGrade = '-';
				}
				$tableStr.='<td>'.$thisGrade.'</td>';
				

			}			
			$tableStr.='<td>'.$totalGiven.'</td>';
			

			$tableStr.='</tr>';

						
		}
		
		
		// NOw add the web PA scores
		$tableStr.= '<tr><td><i>Web PA Total</i></td>';

		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{

			$totalWebPA_Score = $masterGroupArray[$thisUserID]['totalWebPA_Score'];
			
			$tableStr.= '<td><i><strong>'.$totalWebPA_Score.'<strong></i></td>';

		}
		$tableStr.='<td>-</td></tr>';	
		
		// Adjusted Score
		$tableStr.= '<tr><td><i>Adjusted Score Forumla</i></td>';

		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{
			$adjustedFinalWebPA_score = $masterGroupArray[$thisUserID]['adjustedFinalWebPA_score'];
			$prePenaltyScore = $masterGroupArray[$thisUserID]['prePenaltyScore'];			
			$tableStr.= '<td><i>';
			$tableStr.='('.$markToAdjust.'% * '.$adjustedFinalWebPA_score.' ) = '.round($markToAdjust * $adjustedFinalWebPA_score, 0).'%<br/>'; 
			$tableStr.=' + '.$markNotToAdjust.'%<br/>';
			$tableStr.=' = '.$prePenaltyScore.'%';	

			$tableStr.='</i></td>';

		}
		$tableStr.='<td>-</td></tr>';			


	

	
	
		

		// Pre penalty
		$tableStr.= '<tr><td><i>Pre Penalty Score</i></td>';
		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{

			$prePenaltyScore = $masterGroupArray[$thisUserID]['prePenaltyScore'];			
			$tableStr.= '<td><i>'.$prePenaltyScore.'%</i></td>';

		}
		$tableStr.='<td>-</td></tr>';

		// Penalty
		$tableStr.= '<tr><td><i>Penalty Score</i></td>';
		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{

			$nonSubmissionPenalty = $masterGroupArray[$thisUserID]['nonSubmissionPenalty'];
			
			$tableStr.= '<td><i>- '.$nonSubmissionPenalty.'%</i></td>';

		}
		$tableStr.='<td>-</td></tr>';		
		
		
		

		
		// NOw add the final scores
		$tableStr.= '<tr><td><strong>Final Score</strong></td>';

		foreach ($studentsInGroupLookup as $thisUserID => $thisName)
		{

			$finalScore = $masterGroupArray[$thisUserID]['finalScore'];
			
			$tableStr.= '<td><strong>'.$finalScore.'%</strong></td>';

		}		
		$tableStr.='<td>-</td></tr>';		
				
				
				
		

		$tableStr.= '</table>';

				
			

			
		$outputArray = array
		(
			"string"	=> $tableStr,
			"array"	=> $masterGroupArray,
		);
		
		return $outputArray;

		
		
	}




	
	
} //Close class
?>