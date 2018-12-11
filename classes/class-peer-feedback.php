<?php
class peerFeedback
{
	var $version 		= '0.2';
	var $pluginFolder 	= '';
	var $opName 		= 'as_peer_feedback_ops';
	var $ops 			= false;
	var $dbug 			= '';
	
	
	//~~~~~
	function __construct ()
	{
		$this->pluginFolder = plugins_url('', __FILE__);
		$this->addWPActions();
		
	}
	
	
	//~~~~~
	function defaults ()
	{
		/*
		$defaults = array(
			'version' 			=> $this->version,
			'navButtonLocation'	=> 'both',
			'nextLinkText'		=> 'Next',
			'backLinkText'		=> 'Previous',
			'buttonIconID'		=> '1',
			'showQuickJumpList'	=> 'true',
			'unMarkedText'		=> 'Mark as read',
			'markedText'		=> 'Completed',
			'showStudentProgress' => 'bar',
			'readButtonLocation' => 'top',
			'startLinkText'		=> 'Click here to start',
			'miniMenuLocation'		=> 'Top',
			'subpageListStyle'	=> 'twoCol'
		);
		*/
		
		$defaults="";
		return $defaults;
	}
		
	
	
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
				
		//Frontend
		add_action( 'wp_footer', array( $this, 'frontendEnqueues' ) );
		//add_action( 'wp_footer', array( $this, 'frontendInlineScript' ), 100 ); //later than enqueues
		
		add_action( 'admin_enqueue_scripts', array( $this, 'adminSettingsEnqueues' ) );
		
		
		// Function that check sfor custom GET actions etc		
		add_action( 'load-edit.php', array( $this,'checkForActions'));	

		// Front end drawing of the content page for peer projects		
		add_action( 'the_content', array( $this, 'peer_project_front_draw' ), 100 );
	
	}
	
	//~~~~~
	function peer_project_front_draw( $theContent )
	{
		return ASPFdraw::drawProjectPage( $theContent );
	}	
	
	
	//~~~~~
	function frontendEnqueues ()
	{
		//Scripts
		wp_enqueue_script('jquery');
		
		//Styles
		wp_enqueue_style( 'peer-feedback-front-css', PFEEDBACK_PLUGIN_URL . '/css/frontend.css' );
		wp_enqueue_style( 'peer-feedback-shared-css', PFEEDBACK_PLUGIN_URL . '/css/shared.css' );
		//wp_enqueue_style('pure-style','https://cdnjs.cloudflare.com/ajax/libs/pure/0.6.0/buttons.css');		
		
		
		// Register Ajax script for front end
		wp_enqueue_script('js_custom_ajax', PFEEDBACK_PLUGIN_URL.'/scripts/ajax.js', array( 'jquery' ) ); #Custom JS functions
		wp_enqueue_script('js_custom', PFEEDBACK_PLUGIN_URL.'/scripts/js-functions.js'); #Custom JS functions	
		
		
		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('pf_ajax_nonce')
		);
		wp_localize_script( 'js_custom_ajax', 'frontEndAjax', $params );		
		
		
			
		
	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	
	//~~~~~
	function adminSettingsEnqueues ()
	{
		//WP includes
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-mouse');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-touch-punch');	
		wp_enqueue_script('jquery-ui-datepicker');
	
		
		//Plugin folder js
		//wp_enqueue_script( 'page_tracker_settings', $this->pluginFolder . '/scripts/settings.js' );
		
		//Plugin folder css
		wp_enqueue_style( 'peer_feedback_admin_css', PFEEDBACK_PLUGIN_URL . '/css/admin.css' );
		wp_enqueue_style( 'peer-feedback-shared-css', PFEEDBACK_PLUGIN_URL . '/css/shared.css' );
		//wp_enqueue_style( 'page_tracker_progressBars', $this->pluginFolder . '/css/progress-bar.css' );		
		
		
		//DataTables js
		wp_register_script( 'datatables', ( '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js' ), false, null, true );
		wp_enqueue_script( 'datatables' );
		
		//DataTables css
		wp_enqueue_style('datatables-style','//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css');
		
		
		
		//Load the jquery ui theme
		global $wp_scripts;	
		$queryui = $wp_scripts->query('jquery-ui-core');
		$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";	
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);	


		// Add Thickbox		
		add_thickbox(); 
	}
	
	

	

	
	
	// Check for custom actions on the admin screens	
	function checkForActions()
	{
		$screen = get_current_screen(); 
		
		
		// Only edit post screen:
		if( 'edit-peer_projects' === $screen->id )
		{
			// Before:
			add_action( 'all_admin_notices', array($this, 'applyActions'));
			//add_action( 'load-edit.php', array( $this,'checkForActions'));		
			
		}
	}
	
	function applyActions()
	{
		if(isset($_GET['myAction']))
		{
			
			$myAction = $_GET['myAction'];
			switch ($myAction)
			{
				
				case "launchProject":
					// Get the project ID and set it to live
					$projectID = $_GET['projectID'];					
					peerfeedbackActions::projectLaunch($projectID);
					
				break;
				
				
				case "disableProject":
					// Get the project ID and set it to live
					$projectID = $_GET['projectID'];
					update_post_meta( $projectID, 'project_status', 0 );
					update_post_meta( $projectID, 'feedback_status', 0 ); // Disable feedback in case its on fopr any reason
					echo '<div class="updated notice"><p>Project Disabled</p></div>';
					
				break;
				
				case "enableFeedback":
				
					$projectID = $_GET['projectID'];				
					peerfeedbackActions::enableFeedback($projectID);
					
				break;				
				
				case "disableFeedback":
					// Get the project ID and set it to live
					$projectID = $_GET['projectID'];
					update_post_meta( $projectID, 'feedback_status', 0 ); // Disable feedback in case its on fopr any reason
					echo '<div class="updated notice"><p>Feedback Disabled</p></div>';					
					
				break;				
				
				
			}
		}		
		
	}	
	


				
	
	
} //Close class
?>