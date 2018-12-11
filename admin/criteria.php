<?php

// Get criteriaID from GET after security check
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');

global $wpdb;

// GEt the project Status
$project_status = get_post_meta( $projectID, 'project_status', true );
$project_title = get_the_title($projectID);

				

echo '<h1>'.$project_title.' : Project Criteria</h1>';
echo '<a href="edit.php?post_type=peer_projects">Back to Projects</a><hr/>';


if($project_status==1)
{
	echo '<div class="update-nag notice"><p>This feedback project is current live and so you cannot edit the criteria</p></div>';
}
else
{
	?>
    
    
	<button class="button-secondary" id="uploadOpenButton">Upload your criteria</button>
    <div id="uploadDiv" style="display:none">
    <h2>How to upload your criteria</h2>    
    <form name="csvUploadForm" action="options.php?page=as-pfeedack-project-criteria&projectID=<?php echo $projectID ;?>&action=criteriaUpload"  method="post" enctype="multipart/form-data">
    Upload your ctieria as a CSV file with the following columns:<br/>
    <table class="csvUploadDemoTable">
    <tr><td></td><td>Rating 1</td><td>Rating 2</td><td>Rating 3</td><td>Rating 4</td></tr>
    <tr><td>Student's Effort</td><td>No effort was made</td><td>Not a lot of effort was put into the project</td><td>You did everything expected and required</td><td>You worked exceptionally hard on the project</td></tr>
    <tr><td>Student's Contribution</td><td>you produced no work</td><td>you produced poor quality work</td><td>you produced good work</td><td>you produced outstanding work</td></tr>
    <tr><td>Student's Leadership</td><td>you were absent or unhelpful</td><td>you did not take on any responsibilities</td><td>you played your role in the team</td><td>you provided effective leadership</td></tr>
    </table>


	<?php
	// Add nonce
	wp_nonce_field('criteriaUploadNonce');
	
	?>
    <input type="file" name="csvFile" size="20"/><br/>
    <input type="submit" value="Upload" name="submit" class="button-primary" />
    </form>
    </div>

	<script>
    jQuery( "#uploadOpenButton" ).click(function() {
      jQuery( "#uploadDiv" ).toggle( "fast" )
    });
    </script>    
    
    


    <br>
    <hr>
    
    <?php
}


// If the settings.
if ( isset( $_GET['action'] ) ) {		
	
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	if (wp_verify_nonce($retrieved_nonce, 'criteriaUploadNonce' ) )
	{
	
		$myAction = $_GET['action'];
		switch ($myAction)
		{	
	
			case "criteriaUpload":
		
		
			$newFilename = dirname(__FILE__).'/tempImport.csv';
			
			if(isset($_FILES['csvFile']['tmp_name']))
			{
				$criteriaTable = $wpdb->prefix.DBTABLE_PEER_FEEDBACK_CRITERIA;
				$responseOptionsTable= $wpdb->prefix.DBTABLE_PEER_FEEDBACK_RESPONSE_OPTIONS;
				$criteriaDescriptorsTable= $wpdb->prefix.DBTABLE_PEER_FEEDBACK_CITERIA_DESCRIPTORS;				
				

				// Delete old criteria records
				$delete = $wpdb->query("DELETE FROM ".$criteriaTable." WHERE projectID = ".$projectID);
				
				// Delete old response options records
				$delete = $wpdb->query("DELETE FROM ".$responseOptionsTable." WHERE projectID = ".$projectID);
				
				// Delete old descriptors records
				$delete = $wpdb->query("DELETE FROM ".$criteriaDescriptorsTable." WHERE projectID = ".$projectID);				
				
				move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);
				



				// Go through the CSV stuff
				ini_set('auto_detect_line_endings',1);
				$handle = fopen($newFilename, 'r');
				
				// Create some default arrays
				$tempCriteriaArray = array();

				$currentRow=1;
				$currentCriteriaOrder=1; // Incrememnt one if its the first col after first row

				//echo '<h1>Data</h1>';
				while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
				{	
				
					$data = array_map("utf8_encode", $data); //added				
				
					if($currentRow==1) // Its the first Row so it must be the criteria
					{						
						$currentCol=1;
						foreach($data as $thisResponseOption)
						{
							if($currentCol>=2)
							{
								// Add the response Option
								$msg = $wpdb->insert( 
									$responseOptionsTable,
									array( 
										'projectID'	=> $projectID,
										'responseOption'	=> peerFeedback_utils::sanitizeTextImport($thisResponseOption),
										'optionOrder' => $currentCol-1
									),
									array( '%d', '%s', '%d' )
								);	
								
								$thisResponseOptionID = $wpdb->insert_id;		
								$tempResponseOptionArray[] = $thisResponseOptionID;
														
							}
							$currentCol++;

						}
						$currentRow++;						
						//$currentGroup= peerFeedback_utils::sanitizeGroupsImport($data[0]);						
					}					
					else // Its not the first row so must be criteria and descriptors
					{
						$currentCol=1;
						foreach($data as $thisCellInfo)
						{		
						
							if($currentCol==1)
							{
								// Add the repson
								$msg = $wpdb->insert( 
									$criteriaTable,
									array( 
										'projectID'	=> $projectID,
										'criteria'	=> peerFeedback_utils::sanitizeTextImport($thisCellInfo),
										'criteriaOrder' => $currentCriteriaOrder
									),
									array( '%d', '%s', '%d' )
								);	
								
								$currentCriteriaOrder++;								
								
								$thisCriteriaID = $wpdb->insert_id;		
							}
							else // Its a descriptor
							{
								
								// Get the Respose Option ID from the temp array
								$thisArrayCell = $currentCol-2;
								$thisOptionID = $tempResponseOptionArray[$thisArrayCell];
								// Add the descriptor
								$msg = $wpdb->insert( 
									$criteriaDescriptorsTable,
									array( 
										'projectID'	=> $projectID,
										'optionID'	=> $thisOptionID,
										'criteriaID' => $thisCriteriaID,
										'descriptor' => peerFeedback_utils::sanitizeTextImport($thisCellInfo)
									),
									array( '%d', '%d', '%d', '%s' )
								);									
								
								
								
							}
							$currentCol++;

						}						
					}
				}
				
				echo '<div class="updated notice"><p>Criteria Uploaded</p></div>';
				
				
			} // End if file type is CSV
			// Now delete the temp file
			unlink ($newFilename);	
			
			
		}// End if grouopsUpload case	
		
	}
} // End is action




echo '<h1>Current Criteria</h1>';

$args=array
(
	"projectID"		=> $projectID,
	"readOnly"		=> true,
	"targetUserID"=> "",
	"userID"		=> ""
);


echo ASPFdraw::drawRubricTable($args);




?>
