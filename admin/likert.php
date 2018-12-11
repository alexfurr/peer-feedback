<?php

// Get criteriaID from GET after security check
$projectID = peerFeedback_utils::securityCheckAdminPages('projectID');

global $wpdb;

// GEt the project Status
$project_status = get_post_meta( $projectID, 'project_status', true );
$project_title = get_the_title($projectID);

				

echo '<h1>'.$project_title.' : Likert Criteria</h1>';
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
    <form name="csvUploadForm" action="options.php?page=as-pfeedack-project-likert&projectID=<?php echo $projectID ;?>&action=criteriaUpload"  method="post" enctype="multipart/form-data">
    Upload your criteria as a CSV file with the following columns:<br/>
    <table class="csvUploadDemoTable">
    <tr><td>Student's Presentation</td><td>Very Poor</td><td>Polished</td></tr>
    <tr><td>Student's Effort</td><td>No effort was made</td><td>They did everything</td></tr>
    <tr><td>Student's Contribution</td><td>Nothing Produced</td><td>A powerhouse</td></tr>
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
				
					
						
					$criteria = $data[0];
					$likertLeft = $data[1];
					$likertRight = $data[2];
					
									

					// Add the response Option
					$msg = $wpdb->insert( 
						$criteriaTable,
						array( 
							'projectID'	=> $projectID,
							'criteria'	=> peerFeedback_utils::sanitizeTextImport($criteria),
							'criteriaOrder' => $currentRow,
							'likertLeft'	=> $likertLeft,
							'likertRight'	=> $likertRight,
						),
						array( '%d', '%s', '%d', '%s', '%s'  )
					);	

					
					
					$thisCriteriaID = $wpdb->insert_id;		
					
					$currentRow++;			
												


				
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
	"userFeedbackID"=> "",
	"userID"		=> ""
);


echo ASPFdraw::drawLikertScale($args);




?>
