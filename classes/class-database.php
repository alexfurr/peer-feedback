<?php
class aspf_database {
	
	
	var $DBversion 		= '0.6';
	
	
	//~~~~~
	function __construct ()
	{
		add_action( 'plugins_loaded', array($this, 'myplugin_update_db_check' ) );
					
	}              
	
	// Function to check latest version and then update DB if needed
	function myplugin_update_db_check()
	{
		
		$DBversion = $this->DBversion;		
		$savedVersion = get_option( 'pf_DBversion' );
		

		
		if($savedVersion=="")
		{
			add_option( 'pf_DBversion', $DBversion );
			$this->installDB();			
			
		}
		elseif ( get_option( 'pf_DBversion' )< $DBversion )
		{
			// Update version op
			update_option( 'pf_DBversion', $DBversion );
			$this->installDB();
						
		}
		

		// Overrider for testing
		//$this->installDB();
	
	}	
	
/*	--------------------------------------------
	PLUGIN COMPATIBILITY AND UPDATE FUNCTIONS 
	-------------------------------------------- */	
	//~~~~~
	function getCharsetCollate () 
	{
		global $wpdb;
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) )
		{
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) 
		{
			$charset_collate .= " COLLATE $wpdb->collate";
		}
		return $charset_collate;
	}
	

	

	//~~~~~
	function installDB ()
	{


		
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$WPversion = substr( get_bloginfo('version'), 0, 3);
		$charset_collate = ( $WPversion >= 3.5 ) ? $wpdb->get_charset_collate() : $this->getCharsetCollate();
		
		$feedbackTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK;
		//feedback table
		$sql = "CREATE TABLE $feedbackTable (
			feedbackID mediumint(9) NOT NULL AUTO_INCREMENT,
			userID mediumint(9),
			projectID mediumint(9),
			targetUserID mediumint(9),
			submitDate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			feedbackText longtext,
			feedbackValue mediumint(9),
			PRIMARY KEY  (feedbackID)
			) $charset_collate;";
		dbDelta( $sql );
		//$this->dbug .= '#delta tables.';
		
		$groupsTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_GROUPS;
		//groups table
		$sql = "CREATE TABLE $groupsTable (
			groupID mediumint(9) NOT NULL AUTO_INCREMENT,
			groupName varchar(255),
			projectID mediumint(9),
			groupFeedback longtext,
			groupMark mediumint(9),
			PRIMARY KEY  (groupID)
			) $charset_collate;";
			
		dbDelta( $sql );
		//$this->dbug .= '#delta tables.';	
		
		
		$usersTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_USERS;
		//groups table
		$sql = "CREATE TABLE $usersTable (
			ID mediumint(9) NOT NULL AUTO_INCREMENT,
			groupID mediumint(9),
			firstName varchar(255),
			lastName varchar(255),
			email varchar(255),
			password varchar(255),
			PRIMARY KEY  (ID)			
			) $charset_collate;";
			
			dbDelta( $sql );
//			print_r($msg);
//		echo 'called';
		//die();			
			
			
		
		

		$criteriaTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_CRITERIA;
		//groups table
		$sql = "CREATE TABLE $criteriaTable (
			criteriaID mediumint(9) NOT NULL AUTO_INCREMENT,
			projectID mediumint(9),
			criteria longtext,
			criteriaOrder mediumint(9),
			likertLeft longtext,
			likertRight longtext,
			PRIMARY KEY  (criteriaID)
			) $charset_collate;";
			
		dbDelta( $sql );
		
		



		$responseOptionsTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_RESPONSE_OPTIONS;
		//groups table
		$sql = "CREATE TABLE $responseOptionsTable (
			optionID mediumint(9) NOT NULL AUTO_INCREMENT,
			projectID mediumint(9),
			responseOption longtext,
			optionOrder mediumint(9),
			PRIMARY KEY  (optionID)
			) $charset_collate;";
			
		dbDelta( $sql );
		
		
		$descriptorsTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_CITERIA_DESCRIPTORS;
		//groups table
		$sql = "CREATE TABLE $descriptorsTable (
			criteriaID mediumint(9),
			optionID mediumint(9),
			projectID mediumint(9),
			descriptor longtext
			) $charset_collate;";
			
		dbDelta( $sql );	

		// Written feedback in addition to the standard ratings
		
		$writtenFeedbackTable = $wpdb->prefix . DBTABLE_PEER_FEEDBACK_WRITTEN_FEEDBACK;
		
		$sql = "CREATE TABLE $writtenFeedbackTable (
			ID mediumint(9) NOT NULL AUTO_INCREMENT,		
			projectID mediumint(9),
			userID mediumint(9),
			targetUserID mediumint(9),
			feedbackText longtext,
			submitDate datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (ID)	
			) $charset_collate;";

		dbDelta( $sql );
		
	}
	
}
?>