<?php
class peerFeedback_Queries
{
	
	
	public static function getGroupsInProject ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_GROUPS." WHERE projectID=".$projectID.' ORDER BY groupName ASC';
	
		$results = $wpdb->get_results( $sql );
		$groupsCount = $wpdb->num_rows;
		
		if($groupsCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}
	
	public static function getGroupInfo ($args="")
	{
		global $wpdb;
		$groupID= $args['groupID'];
		
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_GROUPS." WHERE groupID=".$groupID;
		$results = $wpdb->get_row( $sql );
		$groupsCount = $wpdb->num_rows;
		
		if($groupsCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}	
	
	
	public static function getUsersInGroup($args="")
	{
		global $wpdb;
		$groupID = $args['groupID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_USERS." WHERE groupID=".$groupID;
	
		$groupUsers = $wpdb->get_results( $sql );
		$userCount= $wpdb->num_rows;
		
		if($userCount>=1)
		{
			$sortedUserArray = array();			
			foreach($groupUsers as $userInfo)
			{
				
				// Create temp array of users so we can order by surname
				$firstName = $userInfo->firstName;
				$lastName = $userInfo->lastName;
				$email = $userInfo->email;
				$userID = $userInfo->ID;
				$password= $userInfo->password;				
				
				
				$sortedUserArray[$userID] = array
				(
					'lastName'	=> $lastName,
					'firstName'	=> $firstName,
					'email'		=> $email,					
					'userID'	=> $userID,
					'password'	=> $password
				);
			}
			
			// Now sort the array by surname
			usort($sortedUserArray, function ($a, $b) { return strcmp($a['lastName'], $b['lastName']);});
			
			return $sortedUserArray;

		}
		else
		{
			return false;
		}
			
	}
	

	public static function getAllProjectStudents($args)
	{
		$projectID = $args['projectID'];
		$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);
		$masterStudentGroupArray = array();
		
		if(!$projectGroups)
		{
			return;
		}
		
		foreach($projectGroups as $groupInfo)
		{
			
			$groupName = $groupInfo->groupName;
			$groupID= $groupInfo->groupID;
			$args = array
			(
				'groupID' => $groupID
			);
			
			$groupUsers = peerFeedback_Queries::getUsersInGroup($args);
			if($groupUsers<>false)
			{
				foreach($groupUsers as $userInfo)
				{
					$firstName = $userInfo['firstName'];
					$lastName = $userInfo['lastName'];
					$userID = $userInfo['userID'];
					$email= $userInfo['email'];
					$password= $userInfo['password'];
					
					$masterStudentGroupArray[] = array
					(
						'firstName'	=> $userInfo['firstName'],
						'lastName'	=> $userInfo['lastName'],
						'userID'	=> $userInfo['userID'],
						'email'		=> $userInfo['email'],
						'password'	=> $userInfo['password']
					);
				}
			}
		}
		
		return $masterStudentGroupArray;
		
	}
	
	public static function getMyGroupStudents ($args)
	{
		global $wpdb;
		$projectID= $args['projectID'];
		$currentUserID= $args['userID'];
		
		$args = array
		(
			'projectID' => $projectID
		);
		$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);
		
		
		if($projectGroups==false)
		{
			return;
		}
		else
		{
			foreach($projectGroups as $groupInfo)
			{
				
				$groupName = $groupInfo->groupName;
				$groupID= $groupInfo->groupID;
				
				$args = array
				(
					'groupID' => $groupID
				);
				
				$groupUsers = peerFeedback_Queries::getUsersInGroup($args);
				if($groupUsers)
				{
					// Go through the ordeded array and spit out results
					foreach($groupUsers as $userInfo)
					{
						$firstName = $userInfo['firstName'];
						$lastName = $userInfo['lastName'];
						$userID = $userInfo['userID'];
						$email= $userInfo['email'];		
						
						if($userID==$currentUserID)
						{
							return 	$groupUsers;
						}
					}
				}
			}
		}
	}

	
	
	
	public static function getProjectCriteria ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_CRITERIA." WHERE projectID=".$projectID.' ORDER BY criteriaOrder ASC';
	
		$results = $wpdb->get_results( $sql );
		$groupsCount = $wpdb->num_rows;
		
		if($groupsCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}	
	
	
	public static function getProjectLikertScale($projectID)
	{
		global $wpdb;
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_CRITERIA." WHERE projectID=".$projectID.' ORDER BY criteriaOrder ASC';
	
		$results = $wpdb->get_results( $sql );
		$groupsCount = $wpdb->num_rows;
		
		if($groupsCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}		
	}
	
	
	public static function getProjectResponseOptions ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_RESPONSE_OPTIONS." WHERE projectID=".$projectID.' ORDER BY optionOrder ASC';
	
		$results = $wpdb->get_results( $sql );
		$groupsCount = $wpdb->num_rows;
		
		if($groupsCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}		
	
	public static function getProjectCriteriaDescriptors ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_CITERIA_DESCRIPTORS." WHERE projectID=".$projectID;
	
		$results = $wpdb->get_results( $sql );
		$descriptorsCount = $wpdb->num_rows;
		
		if($descriptorsCount>=1)
		{
			$descriptorsArray = array();
			foreach($results as $desriptorsInfo)
			{
				
				$criteriaID = $desriptorsInfo->criteriaID;
				$optionID= $desriptorsInfo->optionID;	
				$descriptor= $desriptorsInfo->descriptor;					
				
				$descriptorsArray[$criteriaID][$optionID] = $descriptor;
			}
			
			
			return $descriptorsArray;
		}
		else
		{
			return false;
		}
	}		
	
	
	// Gets feedback that a specific user has given about another user
	public static function getUserFeedbackForTargetUser ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		$userID = $args['userID'];
		$targetUserID = $args['targetUserID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK." WHERE projectID=".$projectID." AND userID=".$userID." AND targetUserID=".$targetUserID;
	
		$results = $wpdb->get_results( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}	
	
	// Gets feedback that a specific user has given about another user
	public static function getWrittenFeedbackForTargetUser ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		$userID = $args['userID'];
		$targetUserID = $args['targetUserID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_WRITTEN_FEEDBACK." WHERE projectID=".$projectID." AND userID=".$userID." AND targetUserID=".$targetUserID;
	
		$results = $wpdb->get_row( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}		
	
	// Gets feedback that a specific user has given about another user
	public static function getAllWrittenFeedbackForTargetUser ($args="")
	{
		global $wpdb;
		$projectID = $args['projectID'];
		$targetUserID = $args['targetUserID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK_WRITTEN_FEEDBACK." WHERE projectID=".$projectID." AND targetUserID=".$targetUserID;
	
		$results = $wpdb->get_results( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}		
	
	// Gets all feedback given by a specific user about everyone
	public static function getUserFeedbackFromUser ($args="")
	{
		global $wpdb;
		$userID = $args['userID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK." WHERE userID=".$userID;	
		$results = $wpdb->get_results( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}		
	
	public static function getAllGroupFeedback($groupID)
	{
		global $wpdb;
		$groupID;
		
		// Get all the users in that groupID
		$args = array(
			"groupID"	=> $groupID,
		);
		$groupUsers = peerFeedback_Queries::getUsersInGroup($args);
		
		
		$userID_sql = '';
		$i=1;
		
		if(is_array($groupUsers) )
		{
			foreach ($groupUsers as $userMeta)
			{
				if($i>1){$userID_sql.= ' OR ';}			
				$userID = $userMeta['userID'];
				$userID_sql.= ' userID = '.$userID;
				
				$i++;
			}
		}

		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK." WHERE ".$userID_sql;
		$results = $wpdb->get_results( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}
	
	// Gets ALL feedback about specific  user
	public static function getAllUserFeedback ($args="")
	{
		global $wpdb;
		$targetUserID = $args['targetUserID'];
		
		$sql = "SELECT * FROM " . $wpdb->prefix . DBTABLE_PEER_FEEDBACK." WHERE targetUserID=".$targetUserID;
	
		$results = $wpdb->get_results( $sql );
		$dataCount = $wpdb->num_rows;
		
		if($dataCount>=1)
		{
			return $results;
		}
		else
		{
			return false;
		}
	}
	
	
	public static function getUserFromID($userID)
	{
		
		global $wpdb;		
		
		$sql  = "SELECT * FROM ".$wpdb->prefix . DBTABLE_PEER_FEEDBACK_USERS." WHERE ID = ".$userID;
		$userInfo = $wpdb->get_row( $sql, ARRAY_A );		
		return $userInfo;
		
		
	}
	
	

	
	
} //Close class
?>
