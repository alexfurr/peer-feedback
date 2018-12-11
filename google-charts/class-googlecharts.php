<?php

class ASPF_GCHARTS {


	function __construct ()
	{
		//$this->enqueueScripts();
	}

	
	function enqueueScripts ()
	{
		$pluginFolder = plugins_url( '/google-charts/', dirname(__FILE__) );
		wp_enqueue_script( 'google-charts', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'gcharts-custom-js', $pluginFolder . 'googlecharts.js', array( 'jquery' ) );
	}
	
	
	
	function draw ( $args )
	{
		$chartType	= $args['chartType'];
		$data		= $args['data'];
		$keyName	= $args['keyName'];
		$valName	= $args['valName'];
		$title		= $args['title'];	
		$elementID	= $args['elementID'];
		$width		= $args['width'];
		$height		= $args['height'];
		$maxValue = $args['maxValue'];
		
		if ( ! is_array( $data ) ) {
			
			return;
		}
		
		$c = 1;
		$dataCount = count( $data );
		
		$jsArray = "[ '" .$keyName. "', '" .$valName. "' ],";
		
		foreach ( $data as $i => $values ) 
		{
			$jsArray .= "[ '" .$values[0]. "', " .$values[1]. " ]" . ( $c < $dataCount ? ", " : "" );
			$c++;
		}
		
	
		?>
		
		<script>		
		jQuery( document ).ready( function () {
			ASPF_G_CHARTS.charts.push({
				type:		'<?php echo $chartType; ?>',
				data: 		[ <?php echo $jsArray; ?> ],
				elementID:	'<?php echo $elementID; ?>',
				title:		'<?php echo $title; ?>',
				maxValue:	'<?php echo $maxValue; ?>'
			});
		});
		</script>		
		
		<?php
		
		// Draw the element
		$myStyle = 'width: '.$width.'; height: '.$height.';';
		$str= '<div style="'.$myStyle.'" id="'.$elementID.'"></div>';
		
		return $str;

		
		
	}
	

}
?>