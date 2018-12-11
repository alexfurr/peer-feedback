<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');
$groupID = $_GET['groupID'];


$userID= $_GET['userID'];

// Security Check on project ID and admin only
if(!is_numeric($projectID))
{
	die();
}

// Security Check on project ID and admin only
if(!is_numeric($userID))
{
	die();
}

if(!current_user_can('manage_options'))
{
	die();	
}


$project_title = get_the_title($projectID);
// GEt this user Info
$userInfo = peerFeedback_Queries::getUserFromID($userID);
$groupID =$userInfo['groupID']; 

// Get the group mark
$args = array(
"groupID"	=> $groupID,
);
$groupInfo = peerFeedback_Queries::getGroupInfo($args);

$groupMark = $groupInfo->groupMark;


$args = array(
"groupID"	=> $groupID,
"groupMark"	=> $groupMark,
);



echo '<h1>'.$project_title.'</h1>';
echo '<h2>'.$userInfo['firstName'].' '.$userInfo['lastName'].'  feedback</h2>';
echo '<a href="?page=as-pfeedack-project-groups&projectID='.$projectID.'" class="backLink">Back to Groups</a><hr/>';

$args = array(
"targetUserID" => $userID
);

echo ASPFdraw::drawMyFeedback($args);

?>
