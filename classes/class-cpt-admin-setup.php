<?php
class peerFeedback_CPT
{
	
	
	
	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}	
	
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		//Admin Menu
		add_action( 'init',  array( $this, 'create_CPTs' ) );		
		add_action( 'admin_menu', array( $this, 'create_AdminPages' ));
		
		// Slides post type metaboxes
		add_action( 'add_meta_boxes_peer_projects', array( $this, 'addMetaBoxes_peer_projects' ));


		// Remove and add columns in the projects table
		add_filter( 'manage_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_pages_custom_column', array($this, 'uos_customColumnContent'), 10, 2);
		
		
		// Add 'Instructions' title to the text editor for projects
		add_action( 'edit_form_after_title', array($this, 'myprefix_edit_form_after_title') );		
		
		
		// Add Default order of DATE to the project list edit table
		add_filter('pre_get_posts', array($this, 'peer_projects_default_order'));
		
		// Save additional project meta for the custom post
		add_action( 'save_post', array($this, 'saveProjectMeta' ));
		


	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPTs ()
	{
		
	
		//Projects
		$labels = array(
			'name'               =>  'Projects',
			'singular_name'      =>  'Project',
			'menu_name'          =>  'Peer Feedback',
			'name_admin_bar'     =>  'Peer Feedback Projects',
			'add_new'            =>  'Add New Project',
			'add_new_item'       =>  'Add New Project',
			'new_item'           =>  'New Project',
			'edit_item'          =>  'Edit Project',
			'view_item'          => 'View Projects',
			'all_items'          => 'All Projects',
			'search_items'       => 'Search Projects',
			'parent_item_colon'  => '',
			'not_found'          => 'No Projects found.',
			'not_found_in_trash' => 'No Projects found in Trash.'
		);
	
		$args = array(
			'menu_icon' => 'dashicons-groups',		
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_nav_menus'	 => false,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 65 ,
			'supports'           => array( 'title', 'editor'  )
			
		);
		
		register_post_type( 'peer_projects', $args );	
	}
	
	function create_AdminPages()
	{
		
		/* Create Admin Pages */

		/* Groups CSV Edit Page */		
		$parentSlug = "no_parent";
		$page_title="Groups";
		$menu_title="";
		$menu_slug="as-pfeedack-project-groups";
		$function=  array( $this, 'draw_adminProjectGroups' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	

		/* Groups CSV Edit Page */		
		$parentSlug = "no_parent";
		$page_title="Feedback Criteria";
		$menu_title="";
		$menu_slug="as-pfeedack-project-criteria";
		$function=  array( $this, 'draw_adminProjectCriteria' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	
		
		/* LIkert Edit Page */		
		$parentSlug = "no_parent";
		$page_title="Feedback Criteria";
		$menu_title="";
		$menu_slug="as-pfeedack-project-likert";
		$function=  array( $this, 'draw_adminProjectLikert' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);	
		
		
		
		/* View Student Feedback as tutor page*/		
		$parentSlug = "no_parent";
		$page_title="Student Feedback";
		$menu_title="";
		$menu_slug="as-pfeedack-student-feedback";
		$function=  array( $this, 'draw_adminStudentFeedback' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);
	
		/* View Student Feedback as tutor page*/		
		$parentSlug = "no_parent";
		$page_title="Group Mark Upload";
		$menu_title="";
		$menu_slug="as-pfeedack-group-feedback";
		$function=  array( $this, 'draw_adminGroupFeedback' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);
	

		/* View and Edit written feedback*/		
		$parentSlug = "no_parent";
		$page_title="Written Feedback";
		$menu_title="";
		$menu_slug="as-pfeedack-written-feedback";
		$function=  array( $this, 'draw_adminWrittenFeedback' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);


	
		/* Help page*/		
		$parentSlug = "edit.php?post_type=peer_projects";
		$page_title="Student Feedback";
		$menu_title="Help";
		$menu_slug="as-pfeedack-help";
		$function=  array( $this, 'draw_adminHelp' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);			
		
	}
	


	//Adds an "instructions" title above the textarea
	function myprefix_edit_form_after_title()
	{
		$scr = get_current_screen();
		$post_type = $scr->post_type;
		
		if($post_type=="peer_projects")
		{
		//	echo '<div class="postbox"><div class="inside">Instructions</div></div>';
			
			echo '<h1>Instructions</h1>';
		}
	}	
			
	

	
	function draw_adminProjectGroups()
	{
		include_once( PFEEDBACK_PATH . '/admin/groups.php' );
	}
	
	
	function draw_adminProjectCriteria()
	{
		include_once( PFEEDBACK_PATH . '/admin/criteria.php' );
	}	
	
	
	function draw_adminStudentFeedback()
	{
		include_once( PFEEDBACK_PATH . '/admin/student_feedback.php' );
	}	
	
	function draw_adminHelp()
	{
		include_once( PFEEDBACK_PATH . '/admin/help.php' );
	}
	function draw_adminProjectLikert()
	{
		include_once( PFEEDBACK_PATH . '/admin/likert.php' );
	}
	
	
	function draw_adminGroupFeedback()
	{
		include_once( PFEEDBACK_PATH . '/admin/group_feedback.php' );
	}		
	
	function draw_adminWrittenFeedback()
	{
		include_once( PFEEDBACK_PATH . '/admin/written_feedback.php' );
	}	
	
	
	
	
	
		
	// Register the metaboxes on projects CPT
	function addMetaBoxes_peer_projects ()
	{
		
		
		
		//Project Settings Metabox
		$id 			= 'peer_projects_availability';
		$title 			= 'Submission Dates';
		$drawCallback 	= array( $this, 'drawMetaBox_projectDates' );
		$screen 		= 'peer_projects';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs 
		);
		
		//Project Settings Metabox
		$id 			= 'peer_projects_weighting';
		$title 			= 'Penalities and Weighting';
		$drawCallback 	= array( $this, 'drawMetaBox_projectWeighting' );
		$screen 		= 'peer_projects';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();
	
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs 
		);		
		
		
		
		//Project Settings Metabox
		$id 			= 'peer_projects_options';
		$title 			= 'Project Settings';
		$drawCallback 	= array( $this, 'drawMetaBox_projectOptions' );
		$screen 		= 'peer_projects';
		$context 		= 'normal';
		$priority 		= 'default';
		$callbackArgs 	= array();
		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs 
		);
		
		
		// Add the metabox for groups if its been saved
		global $post;
		$postStatus = $post->post_status;
		if($postStatus=="publish")
		{
		
			//Group Edit Link Metabox
			$id 			= 'peer_projects_groups';
			$title 			= 'Project Groups';
			$drawCallback 	= array( $this, 'drawMetaBox_projectGroups' );
			$screen 		= 'peer_projects';
			$context 		= 'side';
			$priority 		= 'default';
			$callbackArgs 	= array();
			
			add_meta_box( 
				$id, 
				$title, 
				$drawCallback, 
				$screen, 
				$context,
				$priority, 
				$callbackArgs 
			);		
		}
		
		$feedbackType = get_post_meta($post->ID,'feedbackType',true);
		if($feedbackType=="rubric")
		{
			//Group Edit Link Metabox
			$id 			= 'peer_projects_rubric';
			$title 			= 'Rubric Options';
			$drawCallback 	= array( $this, 'drawMetaBox_projectRubric' );
			$screen 		= 'peer_projects';
			$context 		= 'side';
			$priority 		= 'default';
			$callbackArgs 	= array();
			
			add_meta_box( 
				$id, 
				$title, 
				$drawCallback, 
				$screen, 
				$context,
				$priority, 
				$callbackArgs 
			);				
			
		}


		
	}	
	
	function drawMetaBox_projectRubric($post, $metabox)
	{
		$projectID = $post->ID;
		
		
		echo '<hr/><a href="options.php?page=as-pfeedack-project-criteria&projectID='.$projectID.'" class="button-secondary">Edit Rubric</a>';
		
	}
	
	
	function drawMetaBox_projectWeighting($post, $metabox)
	{
		echo '<span style="color:#666; font-style:italic;">This only applies to the "point distribution" and "likert" types of peer feedback</span><br/>';
		$nonCompletionPenalty = get_post_meta($post->ID,'nonCompletionPenalty',true);
		$feedbackWeighting = get_post_meta($post->ID,'feedbackWeighting',true);
		
		if($feedbackWeighting==""){$feedbackWeighting=100;}
		if($nonCompletionPenalty==""){$nonCompletionPenalty=10;}
		
		echo '<label for="nonCompletionPenalty">Non completion penalty<label><br/>';
		echo '<input size="2" maxlength="3" type="text" value="'.$nonCompletionPenalty.'" name="nonCompletionPenalty" id="nonCompletionPenalty">%<hr/>';

		echo '<label for="feedbackWeighting">Peer mark weighting<label><br/>';
		echo '<input size="2" maxlength="3" type="text" value="'.$feedbackWeighting.'" name="feedbackWeighting" id="feedbackWeighting">%<hr/>';

	}
	
	function drawMetaBox_projectGroups($post, $metabox)
	{
		$projectID = $post->ID;
		// Get the groups info for this project
		$args = array
		(
			"projectID" => $projectID
		);
		$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);
		
		if($projectGroups==false)
		{
			echo 'No';
		}
		else
		{
			echo '<b>'.count($projectGroups).'</b>';
		}
		
		echo ' groups found<hr/>';
		$projectStudents = peerFeedback_Queries::getAllProjectStudents($args);
		
		if($projectStudents==false)
		{
			echo 'No';
		}
		else
		{
		
			echo '<b>'.count($projectStudents).'</b>';
		}
		echo ' students found';
		
		echo '<hr/><a href="options.php?page=as-pfeedack-project-groups&projectID='.$projectID.'" class="button-secondary">Add / Edit Groups</a>';
	}
	
	function drawMetaBox_projectOptions($post, $metabox)
	{
		// Load scripts that check for select change for X points
		?>
        <script>
		jQuery(function() {    // Makes sure the code contained doesn't run until
                  //     all the DOM elements have loaded

			jQuery('#feedbackType').change(function(){
				

				var newValue = this.value;
					
					
				switch (newValue)
				{
					
					case "textOnly":
						jQuery("#allowWrittenFeedbackDiv").hide("fast");
						jQuery("#distributionPointsInputDiv").hide("fast");
						jQuery("#likertPointsInputDiv").hide("fast");
						
					break;
					
					
					
					case "distribution":						
						jQuery("#distributionPointsInputDiv").show("fast");
						jQuery("#likertPointsInputDiv").hide("fast");
						jQuery("#allowWrittenFeedbackDiv").show("fast");
					break;			

					case "likert":						
						jQuery("#likertPointsInputDiv").show("fast");
						jQuery("#distributionPointsInputDiv").hide("fast");				
						jQuery("#allowWrittenFeedbackDiv").show("fast");						
						
					break;
					
					case "rubric":						
						jQuery("#likertPointsInputDiv").hide("fast");
						jQuery("#distributionPointsInputDiv").hide("fast");				
						jQuery("#allowWrittenFeedbackDiv").show("fast");						
						
					break;						
					case "distribution":
						jQuery("#allowWrittenFeedbackDiv").show("fast");											
					break;

					default:
						jQuery("#likertPointsInputDiv").hide("fast");				
						jQuery("#distributionPointsInputDiv").hide("fast");
						jQuery("#allowWrittenFeedbackDiv").hide("fast");
						
					break;						
				}
			});
			

		
		});
		</script>
        <?php
		
		//add wp nonce field
		wp_nonce_field( 'save_as_metabox_peerProjects', 'as_metabox_peerProjects' );
		
		
		// GEt the post meta
		$project_status = get_post_meta($post->ID,'project_status',true);
		$anon_feedback = get_post_meta($post->ID,'anon_feedback',true);
		$feedbackType = get_post_meta($post->ID,'feedbackType',true);
		$allow_self_review = get_post_meta($post->ID,'allow_self_review',true);	
		$distributionPoints = get_post_meta($post->ID,'distributionPoints',true);			
		$likertPoints = get_post_meta($post->ID,'likertPoints',true);	
		$allowWrittenFeedback = get_post_meta($post->ID,'allowWrittenFeedback',true);	


		
		echo '<h3>Anonymity</h3>';
		echo '<input type="checkbox" id="anon_feedback" name="anon_feedback" ';		
		if($anon_feedback=="on"){echo 'checked';}		
		echo ' />';
		echo '<label for="anon_feedback">Feedback is anonymous</label>';
		
		echo '<h3>Self Review</h3>';
		echo '<input type="checkbox" id="allow_self_review" name="allow_self_review" ';		
		if($allow_self_review=="on"){echo 'checked';}		
		echo ' />';
		echo '<label for="allow_self_review">Allow students to rate themselves</label>';
		
		echo '<h3>Feedback Type</h3>';
		echo '<select name="feedbackType" id="feedbackType">';
		echo '<option value="">Please Select</option>';		

		// Feedback textbox only
		echo '<option value="textOnly"';
		if($feedbackType=="textOnly"){echo 'selected';}
		echo '>Written feedback only</option>';
		
		// Rubric
		echo '<option value="rubric"';
		if($feedbackType=="rubric"){echo 'selected';}
		echo '>Criteria Grid (Rubric)</option>';
		
		// Distribute points
		echo '<option value="distribution"';
		if($feedbackType=="distribution"){echo 'selected';}
		echo '>Distribute X points</option>';
		
		// LIkert Scale
		echo '<option value="likert"';
		if($feedbackType=="likert"){echo 'selected';}
		echo '>Likert Scale</option>';
		echo '</select>';		
		
		echo '<div id="distributionPointsInputDiv"';
		if($feedbackType<>"distribution")
		{
			echo 'style="display:none;"';
		}
		echo '><label for="distributionPoints" class="pfAdmin_label">Total Distriubtion Points</label><br/><input type="text" name="distributionPoints" id="distributionPoints" size="3" value="'.$distributionPoints.'"> points</div>';
		
		
		// Likert points Div
		echo '<div id="likertPointsInputDiv"';
		if($feedbackType<>"likert")
		{
			echo 'style="display:none;"';
		}
		echo '><label for="likertPoints" class="pfAdmin_label">Scale Numbers</label><br/>';
		echo '<select name="likertPoints" id="likertPoints">';
		$i=3;
		while ($i<=10)
		{
			echo '<option value="'.$i.'"';
			if($likertPoints==$i){echo ' selected ';}
			echo '>'.$i.'</option>';	
			$i++;			
		}
		
		echo '</select>';
		echo '</div>';
		
		// Give written feedback option
		echo '<div id="allowWrittenFeedbackDiv"';
		if($feedbackType=="textOnly")
		{
			echo 'style="display:none;"';
		}
		echo '>';
		echo '<h3>Written Feedback</h3>';
		echo '<input type="checkbox" id="allowWrittenFeedback" name="allowWrittenFeedback" ';		
		if($allowWrittenFeedback=="on"){echo 'checked';}		
		echo ' />';
		echo '<label for="allowWrittenFeedback">Add a text feedback box for each student</label>';
		
		echo '</div>';	



	}
	
	// Save metabox data on edit slide
	function saveProjectMeta ( $postID )
	{
	
		// Check if nonce is set.
		if ( ! isset( $_POST['as_metabox_peerProjects'] ) ) {
			return;
		}
		
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['as_metabox_peerProjects'], 'save_as_metabox_peerProjects' ) ) {
			return;
		}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $postID ) ) {
			return;
		}
		
		$anon_feedback 	= isset( $_POST['anon_feedback'] ) 	?  		$_POST['anon_feedback']  		: '';	
		$feedbackType 	= isset( $_POST['feedbackType'] ) 	?  		$_POST['feedbackType']  		: '';
		$endDate 	= isset( $_POST['endDate'] ) 	?  		$_POST['endDate']  		: '';		
		$allow_self_review 	= isset( $_POST['allow_self_review'] ) 	?  		$_POST['allow_self_review']  		: '';	
		$distributionPoints 	= isset( $_POST['distributionPoints'] ) 	?  		$_POST['distributionPoints']  		: '';					
		$likertPoints 	= isset( $_POST['likertPoints'] ) 	?  		$_POST['likertPoints']  		: 3;					
		$allowWrittenFeedback 	= isset( $_POST['allowWrittenFeedback'] ) 	?  		$_POST['allowWrittenFeedback']  		: '';	
		$nonCompletionPenalty 	= isset( $_POST['nonCompletionPenalty'] ) 	?  		$_POST['nonCompletionPenalty']  		: '';					
		$feedbackWeighting 	= isset( $_POST['feedbackWeighting'] ) 	?  		$_POST['feedbackWeighting']  		: '';					

		// Validation
		if(!peerFeedback_utils::validateInputDate($endDate)){$endDate="";} // Validate the date
		$anon_feedback = peerFeedback_utils::validateInputCheckbox($anon_feedback);
		$allow_self_review = peerFeedback_utils::validateInputCheckbox($allow_self_review);
		$allowWrittenFeedback = peerFeedback_utils::validateInputCheckbox($allowWrittenFeedback);
		
		$distributionPoints = peerFeedback_utils::validateInputNumber($distributionPoints);		
		$likertPoints = peerFeedback_utils::validateInputNumber($likertPoints);	
		$nonCompletionPenalty = peerFeedback_utils::validateInputNumber($nonCompletionPenalty);		
		$feedbackWeighting = peerFeedback_utils::validateInputNumber($feedbackWeighting);		
		
		

		update_post_meta( $postID, 'allow_self_review', $allow_self_review );		
		update_post_meta( $postID, 'anon_feedback', $anon_feedback );
		update_post_meta( $postID, 'feedbackType', $feedbackType );		
		update_post_meta( $postID, 'distributionPoints', $distributionPoints );	
		update_post_meta( $postID, 'endDate', $endDate );			
		update_post_meta( $postID, 'likertPoints', $likertPoints );
		update_post_meta( $postID, 'allowWrittenFeedback', $allowWrittenFeedback );
		update_post_meta( $postID, 'nonCompletionPenalty', $nonCompletionPenalty );
		update_post_meta( $postID, 'feedbackWeighting', $feedbackWeighting );
		
	}	
	
	function drawMetaBox_projectDates($post, $metabox)
	{
		//echo '<label for="startDate">Opening Date</label><br/>';
		//echo '<input type="text" class="startDate" name="startDate" id="startDate" size="12" value="'.$startDate.'"/>';	
		//echo '<hr/>';		

		$endDate = get_post_meta($post->ID,'endDate',true);


		echo '<label for="endDate">Closing Date</label><br/>';
		echo '<input type="text" class="endDate" name="endDate" id="endDate" size="12" value="'.$endDate.'"/>';	
		echo '<hr/>';	
		
		
		// Enable Date Picker
		?>
		<script>
					jQuery( document ).ready( function ()
					{
					jQuery('.startDate').datepicker({
						dateFormat : 'yy-mm-dd'
					});

					jQuery('.endDate').datepicker({
						dateFormat : 'yy-mm-dd'
					});					
						
				});
		</script>
		<?php			
		
			
			
			
	}
	
	
	// Default order by date
	function peer_projects_default_order( $wp_query ) {
	  if (is_admin()) {
	
		// Get the post type from the query
		$post_type = $wp_query->query['post_type'];
	
		// If the orderby GET is set then its being ordered by something else so don't order
		if ( $post_type == 'peer_projects' && !isset($_GET['orderby']))
		{	
		  $wp_query->set('orderby', 'date');	
		  $wp_query->set('order', 'DESC');
		}
	  }
	}
	
	
	
	// Remove Date Columns on projects
	function my_custom_post_columns( $columns, $post_type )
	{
	  
	  switch ( $post_type )
	  {    
		
			case 'peer_projects':
			
			unset(
				$columns['date']
			);			

			$columns['feedbackType'] = 'Feedback Type';			
			$columns['groups'] = 'Groups and Feedback';
			$columns['project_status'] = 'Project Status';
			$columns['feedback_status'] = 'Feedback Status';
			$columns['date'] = 'Date';			

			break;
		}
		 
	  return $columns;
	}	
	

	
	// Content of the custom columns for Topics Page
	function uos_customColumnContent($column_name, $post_ID)
	{
		
		switch ($column_name)
		{
			
			case "feedbackType":
				$feedbackType = get_post_meta( $post_ID, 'feedbackType', true );
				
				
				switch ($feedbackType)
				{
					case "distribution":
						$distributionPoints = get_post_meta($post_ID, 'distributionPoints', true);
					
						echo 'Point Distribution ('.$distributionPoints.' points)';
					break;
					
					case "rubric":
						echo 'Criteria Grid (Rubric)<br/>';
						echo '<a href="options.php?page=as-pfeedack-project-criteria&projectID='.$post_ID.'">Edit Criteria</a>';
					break;
					
					case "likert":
						$likertPoints = get_post_meta($post_ID, 'likertPoints', true);
						echo 'Likert Scale ('.$likertPoints.' point scale)<br/>';
						echo '<a href="options.php?page=as-pfeedack-project-likert&projectID='.$post_ID.'">Edit Criteria</a>';
					break;

					case "textOnly":
						echo 'Written Feedback';
					break;
					
					default:
						echo '-';
					break;
					
					
				}
									
				
			break;	
					
			case "groups":
				
				$args = array
				(
					'projectID' => $post_ID
				);
				$projectGroups = peerFeedback_Queries::getGroupsInProject ($args);
				$groupCount = count($projectGroups);
				
				if($projectGroups==false)
				{
					echo 'No groups found<br/>';
				}
				else
				{
					echo '<b>'.$groupCount.'</b> group found<br/>';
				}
				echo '<a href="options.php?page=as-pfeedack-project-groups&projectID='.$post_ID.'">View / Edit Groups</a>';		
			break;
			
			case "project_status":
				// See if the project is Live Or not
				$project_status = get_post_meta( $post_ID, 'project_status', true );
				
				if($project_status==1)
				{
					echo 'Status : <span class="successText">Live</span><br/>';
					echo '<a href="#TB_inline?width=200&height=150&inlineId=disableLink'.$post_ID.'" class="thickbox button-secondary">Switch off</a>';
					
					// Launch Feedback popup
					echo '<div id="disableLink'.$post_ID.'" style="display:none;">';
					echo '<div style="text-align:center;">';
					echo '<h2>Are you sure you want to disable this project?</h2>';
					echo 'This will immediately prevent students from giving feedback<br/><br/>';
					
					echo '<a href="edit.php?post_type=peer_projects&myAction=disableProject&projectID='.$post_ID.'" class="button-primary">Yes DISABLE this project</a>';
					
					echo '<a href="" onclick="self.parent.tb_remove();return false" class="button-secondary">Cancel</a>';
					echo '</div>';
					echo '</div>';
					// End review popup					
				}
				else
				{
					echo 'Status : <span class="alertText">Awaiting Launch</span><br/>';
					
					// Check for students
					$args = array
					(
						'projectID' => $post_ID
					);
					$studentsArray = peerFeedback_Queries::getAllProjectStudents($args);					
					$studentCount = count($studentsArray);
					
					if($studentCount>=1)
					{						
						echo '<a href="#TB_inline?width=200&height=150&inlineId=launchLink'.$post_ID.'" class="thickbox button-primary">Launch Project</a>';
					}
					else
					{
						echo 'No Students Found';
					}
					
					// Launch Feedback popup
					echo '<div id="launchLink'.$post_ID.'" style="display:none;">';
					echo '<div style="text-align:center;">';
					echo '<h2>Are you sure you want to launch this project?</h2>';
					echo 'This will send an email to each student in each group and allow them to start the feedback process<br/><br/>';
					
					
					echo '<a href="edit.php?post_type=peer_projects&myAction=launchProject&projectID='.$post_ID.'" class="button-primary"> Yes LAUNCH this project</a>';
					
					echo '<a href="" onclick="self.parent.tb_remove();return false" class="button-secondary">Cancel</a>';
					echo '</div>';
					echo '</div>';
					// End review popup		
				}

				
				
				
				
				//echo '<a href="options.php?page=as-pfeedack-project-groups&projectID='.$post_ID.'">View / Edit Groups</a>';		
			break;	
			
			case "feedback_status":
			
				$project_status = get_post_meta( $post_ID, 'project_status', true );
			
				if($project_status<>1)
				{
					echo 'Status : <span class="alertText">Awaiting Launch</span><br/>';
				}
				else
				{

					// See if the project is Live Or not
					$feedback_status = get_post_meta( $post_ID, 'feedback_status', true );
					
					// Check for students
					$args = array
					(
						'projectID' => $post_ID
					);
					$studentsArray = peerFeedback_Queries::getAllProjectStudents($args);					
					$studentCount = count($studentsArray);		

					if($studentCount>=1)
					{

						if($feedback_status==1)
						{
							echo 'Status : <span class="successText">Enabled</span><br/>';
							echo '<a href="#TB_inline?width=200&height=150&inlineId=disableFeedbackLink'.$post_ID.'" class="thickbox button-secondary">Disable feedback</a>';
							
							// Launch Feedback popup
							echo '<div id="disableFeedbackLink'.$post_ID.'" style="display:none;">';
							echo '<div style="text-align:center;">';
							echo '<h2>Are you sure you want to make feedback unavailable?</h2>';
							
							echo '<a href="edit.php?post_type=peer_projects&myAction=disableFeedback&projectID='.$post_ID.'" class="button-primary"> Yes make feedback unavailable</a>';
							
							echo '<a href="" onclick="self.parent.tb_remove();return false" class="button-secondary">Cancel</a>';
							echo '</div>';
							echo '</div>';
							// End review popup					
						}
						else
						{
							echo 'Status : <span class="alertText">Not yet enabled</span><br/>';
							echo '<a href="#TB_inline?width=200&height=150&inlineId=enableFeedbackLink'.$post_ID.'" class="thickbox button-primary">Enable Feedback</a>';
							
							// Launch Feedback popup
							echo '<div id="enableFeedbackLink'.$post_ID.'" style="display:none;">';
							echo '<div style="text-align:center;">';
							echo '<h2>Are you sure you want to enable feedback results for this project?</h2>';
							echo 'This will send an email to each student in each group alerting them they can view their feedback<br/><br/>';
							
							echo '<a href="edit.php?post_type=peer_projects&myAction=enableFeedback&projectID='.$post_ID.'" class="button-primary"> Yes enable feedback for this project</a>';
							
							echo '<a href="" onclick="self.parent.tb_remove();return false" class="button-secondary">Cancel</a>';
							echo '</div>';
							echo '</div>';
							// End review popup		
						}
					}
					else
					{
						echo 'Status : <span class="alertText">Not yet enabled</span><br/>';
						echo 'No Students Found';

					}
				}

				
				
				
				
				//echo '<a href="options.php?page=as-pfeedack-project-groups&projectID='.$post_ID.'">View / Edit Groups</a>';		
			break;						
			
			
		}	
	}	
	
	
	

	
	
	
	
	
	
	
} //Close class
?>