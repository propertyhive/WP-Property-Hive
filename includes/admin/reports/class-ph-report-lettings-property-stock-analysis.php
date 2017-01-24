<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PH_Report_Lettings_Property_Stock_Analysis
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Reports
 * @version     1.0.0
 */
class PH_Report_Lettings_Property_Stock_Analysis extends PH_Admin_Report {

	private function get_metrics()
	{
		$return = array(
			'price' => array(
				'label' => 'Rent PCM',
				'taxonomy' => false,
			),
			'bedrooms' => array(
				'label' => 'Bedrooms',
				'taxonomy' => false,
			),
			'property_type' => array(
				'label' => 'Property Type',
				'taxonomy' => true,
			)
		);

		$args = array(
	        'hide_empty' => false,
	        'parent' => 0
	    );
	    $terms = get_terms( 'location', $args );
	    
	    if ( !empty( $terms ) && !is_wp_error( $terms ) )
	    {
	    	$return['location'] = array(
				'label' => 'Location',
				'taxonomy' => true,
			);
	    }

	    return $return;
	}

	private function get_price_ranges()
	{
		return array(
			array(
				'from' => 0,
				'to' => 99 
			),
			array(
				'from' => 100,
				'to' => 199 
			),
			array(
				'from' => 200,
				'to' => 299 
			),
			array(
				'from' => 300,
				'to' => 399 
			),
			array(
				'from' => 400,
				'to' => 499 
			),
			array(
				'from' => 500,
				'to' => 599 
			),
			array(
				'from' => 600,
				'to' => 699 
			),
			array(
				'from' => 700,
				'to' => 799 
			),
			array(
				'from' => 800,
				'to' => 899 
			),
			array(
				'from' => 900,
				'to' => 999 
			),
			array(
				'from' => 1000,
				'to' => 1249 
			),
			array(
				'from' => 1250,
				'to' => 1499 
			),
			array(
				'from' => 1500,
				'to' => 1999 
			),
			array(
				'from' => 2000,
				'to' => 2499 
			),
			array(
				'from' => 2500,
				'to' => 2999 
			),
			array(
				'from' => 3000,
				'to' => 9999 
			)
		);
	}

	private function get_property_data( $selected_metrics = array() )
	{
		global $post;

		/*$price_data = array();
		$price_range_data = array();
		$bedrooms_data = array();
		$property_type_data = array();
		$location_data = array();*/

		$return = array();

		$price_ranges = $this->get_price_ranges();

		$args = array(
			'post_type' => 'property',
			'nopaging' => true,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => '_on_market',
					'value' => 'yes'
				),
				array(
					'key' => '_department',
					'value' => 'residential-lettings'
				)
			)
		);

		$property_query = new WP_Query( $args );

		if ( $property_query->have_posts() )
		{
			while ( $property_query->have_posts() )
			{
				$property_query->the_post();

				$property_metric_data = array();

				// Prices
				if ( in_array('price', $selected_metrics) )
				{
					$price  = get_post_meta( get_the_ID(), '_price_actual', TRUE );
					if ( $price != '' && $price != '0' )
					{
						$price_data[] = $price;

						foreach ( $price_ranges as $price_range )
						{
							if ( $price >= $price_range['from'] && $price <= $price_range['to'] )
							{
								$property_metric_data['price'] = $price_range['from'];

								break;
							}
						}
					}
				}

				// Bedrooms
				if ( in_array('bedrooms', $selected_metrics) )
				{
					$bedrooms  = get_post_meta( get_the_ID(), '_bedrooms', TRUE );
					if ( $bedrooms != '' )
					{
						$property_metric_data['bedrooms'] = $bedrooms;
					}
				}

				// Property Types
				if ( in_array('property_type', $selected_metrics) )
				{
					$term_list = wp_get_post_terms( get_the_ID(), 'property_type', array("fields" => "all") );
	    
			        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
			        {
			            foreach ( $term_list as $term )
			            {
			            	$property_metric_data['property_type'] = $term->term_id;
			            }
			        }
			        else
			        {
			        	$property_metric_data['property_type'] = 0;
			        }
			    }

				// Locations
				if ( in_array('location', $selected_metrics) )
				{
					$term_list = wp_get_post_terms( get_the_ID(), 'location', array("fields" => "all") );
	    
			        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
			        {
			            foreach ( $term_list as $term )
			            {
			            	$property_metric_data['location'] = $term->term_id;
			            }
			        }
			        else
			        {
			        	$property_metric_data['location'] = 0;
			        }
			    }

			    if ( count($selected_metrics) == count($property_metric_data) )
			    {
			    	$key = implode("|", $property_metric_data);

			    	if ( !isset($return[$key]) ) { $return[$key] = 0; }

			    	++$return[$key];
			    }
			}
		}
		wp_reset_postdata();

		return $return;
	}

	private function get_average_property_data($metric_one, $metric_two)
	{
		global $post;

		$return = array();

		$args = array(
			'post_type' => 'property',
			'nopaging' => true,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => '_on_market',
					'value' => 'yes'
				),
				array(
					'key' => '_department',
					'value' => 'residential-lettings'
				)
			)
		);

		$price_ranges = $this->get_price_ranges();

		$property_query = new WP_Query( $args );

		if ( $property_query->have_posts() )
		{
			while ( $property_query->have_posts() )
			{
				$property_query->the_post();

				$metric_two_data = '';
				switch ( $metric_two )
				{
					case "price":
					{
						$metric_two_data  = get_post_meta( get_the_ID(), '_price_actual', TRUE );
						break;
					}
					case "bedrooms":
					{
						$metric_two_data  = get_post_meta( get_the_ID(), '_bedrooms', TRUE );
						break;
					}
				}

				if ( $metric_two_data != '' )
				{
					switch ( $metric_one )
					{
						case "property_type":
						case "location":
						{
							$term_list = wp_get_post_terms( get_the_ID(), $metric_one, array("fields" => "all") );
	    
					        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
					        {
					            foreach ( $term_list as $term )
					            {
					            	if ( !isset($return[$term->term_id]) ) { $return[$term->term_id] = array(); }

					            	$return[$term->term_id][] = $metric_two_data;

					            	if ( $term->parent != 0 )
					            	{
					            		$return[$term->parent][] = $metric_two_data;
					            	}
					            }
					        }
							break;
						}
						case "price":
						{
							$price = get_post_meta( get_the_ID(), '_price_actual', TRUE );
							if ( $price != '' && $price != '0' )
							{
								foreach ( $price_ranges as $price_range )
								{
									if ( $price >= $price_range['from'] && $price <= $price_range['to'] )
									{
										$return[$price_range['from']][] = $metric_two_data;
									}
								}
							}
							break;
						}
						case "bedrooms":
						{
							$bedrooms = get_post_meta( get_the_ID(), '_bedrooms', TRUE );
							if ( $bedrooms != '' )
							{
								$return[$bedrooms][] = $metric_two_data;
							}
							break;
						}
					}
				}
			}
		}

		wp_reset_postdata();

		return $return;
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$metrics = $this->get_metrics();

		$report_type = ( ( isset($_GET['report_type']) ) ? $_GET['report_type'] : 'averages' );
?>
<style type="text/css">

.chart-tabs {  }
.chart-tabs ul { list-style-type:none; margin:0; padding:0; }
.chart-tabs ul li  { display:inline-block; margin:0; padding:0; }
.chart-tabs ul li a { display:block; line-height:40px; text-decoration:none; color:#333; padding:0 30px; text-align:center; border:1px solid #DDD; border-bottom:0; }
.chart-tabs ul li.active a { background:#FFF; font-weight:700; }
.chart-tabs ul li a:hover { background:#FFF; }
.chart-with-sidebar { padding:12px 12px 12px 249px; background:#FFF; border:1px solid #DDD; }
.chart-sidebar { width:225px; margin-left:-237px; float:left; }

</style>

<br>

<div class="chart-tabs">
	<ul>
		<li<?php if ( $report_type == 'averages' ) { echo ' class="active"'; } ?>><a href="admin.php?page=ph-reports&tab=properties&report=lettings_property_stock_analysis">Averages</a></li>
		<li<?php if ( $report_type == 'totals' ) { echo ' class="active"'; } ?>><a href="admin.php?page=ph-reports&tab=properties&report=lettings_property_stock_analysis&report_type=totals">Totals</a></li>
	</ul>
</div>
<div class="chart-with-sidebar">

	<div class="chart-sidebar">

		
		<?php 
			if ( $report_type == 'averages' ) 
			{
				$metric_one = ( ( isset($_POST['metric_one']) ) ? $_POST['metric_one'] : 'property_type' );
				$metric_two = ( ( isset($_POST['metric_two']) ) ? $_POST['metric_two'] : 'price' );
		?>
		<form method="post" action="">

			<label for="metric_one">Metric One:</label>
			<select name="metric_one" id="metric_one" style="width:100%;">
				<?php 
					foreach ( $metrics as $metric => $metric_data ) 
					{
				?>
				<option value="<?php echo $metric; ?>"<?php if ( $metric == $metric_one ) { echo ' selected'; } ?>><?php echo $metric_data['label']; ?></option>
				<?php 
					} 
				?>
			</select>

			<br><br>

			<label for="metric_two">Metric Two:</label>
			<select name="metric_two" id="metric_two" style="width:100%;">
				<?php 
					foreach ( $metrics as $metric => $metric_data ) 
					{
						if ($metric_data['taxonomy']) { continue; }
				?>
				<option value="<?php echo $metric; ?>"<?php if ( $metric == $metric_two ) { echo ' selected'; } ?>><?php echo $metric_data['label']; ?></option>
				<?php 
					} 
				?>
			</select>

			<br><br>
			<input type="submit" value="Update" class="button button-primary">

		</form><br>

		<hr>

		<p>This report will look at all the properties currently on the market and display the average rent or number of bedrooms (metric two) for each metric one chosen.</p>
		<p>The averages can be found along the X axis. Alternatively hover over each bar to see the value.</p>
		<p>The thin blue bar shows the average overall of all properties.</p>

		<?php 
			}

			if ( $report_type == 'totals' ) 
			{
		?>
		<form method="post" action="">
		<?php
				$selected_metrics = ( ( isset($_POST['metrics']) ) ? $_POST['metrics'] : array('price') );

				foreach ( $metrics as $metric => $metric_data ) 
				{
		?>
		<label style="display:block; padding:5px 0"><input type="checkbox" name="metrics[]" value="<?php echo $metric; ?>"<?php
			if ( in_array($metric, $selected_metrics) || empty($selected_metrics) ) { echo ' checked'; }
		?>> <?php echo $metric_data['label']; ?></label>
		<?php
				}
		?>
			<br><br>
			<input type="submit" value="Update" class="button button-primary">

		</form><br>

		<hr>

		<p>This report displays the total number of currently on market properties that exist within each unique combination of chosen metrics.</p>
		<?php
			}
		?>
			
		
	</div>

	<div class="chart-main">
		<div class="chart-container" id="ph_chart" style="height:750px;"></div>
	</div>

</div>
<?php
		if ($report_type == 'totals')
		{
			$this->output_totals_chart();
		}
		else
		{
			$this->output_averages_chart();
		}

	}

	private function output_totals_chart()
	{
		$metrics = $this->get_metrics();

		$selected_metrics = ( ( isset($_POST['metrics']) ) ? $_POST['metrics'] : array('price') );

		$price_ranges = $this->get_price_ranges();

		$totals_data = $this->get_property_data( $selected_metrics );
?>
<script>

var totals_data = <?php echo json_encode($totals_data ); ?>;

var selected_metrics = <?php echo json_encode($selected_metrics); ?>;

<?php
	$price_labels = array();
	foreach ( $price_ranges as $price_range )
	{
		$price_labels[$price_range['from']] = '£' . number_format($price_range['from']) . ' - £' . number_format($price_range['to']) . ' PCM';
	}
?>
var price_labels = <?php echo json_encode($price_labels); ?>;

<?php
	$property_type_labels = array();
	$args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $terms = get_terms( 'property_type', $args );
    
    if ( !empty( $terms ) && !is_wp_error( $terms ) )
    {
        foreach ($terms as $term)
        {
            $property_type_labels[$term->term_id] = $term->name;
            
            $args = array(
                'hide_empty' => false,
                'parent' => $term->term_id
            );
            $subterms = get_terms( 'property_type', $args );
            
            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
            {
                foreach ($subterms as $subterm)
                {
                    $property_type_labels[$subterm->term_id] = $subterm->name;
                }
            }
        }
    }
?>
var property_type_labels = <?php echo json_encode($property_type_labels); ?>;
property_type_labels[0] = 'No Property Type Set';

<?php
	$location_labels = array();
	$args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $terms = get_terms( 'location', $args );
    
    if ( !empty( $terms ) && !is_wp_error( $terms ) )
    {
        foreach ($terms as $term)
        {
            $location_labels[$term->term_id] = $term->name;
            
            $args = array(
                'hide_empty' => false,
                'parent' => $term->term_id
            );
            $subterms = get_terms( 'location', $args );
            
            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
            {
                foreach ($subterms as $subterm)
                {
                    $location_labels[$subterm->term_id] = $subterm->name;
                }
            }
        }
    }
?>
var location_labels = <?php echo json_encode($location_labels); ?>;
location_labels[0] = 'No Location Set';

jQuery(document).ready(function($)
{
	var data = [];

	var tick_labels = [];
	var tick_labels_set = false;

	var j = 0;
	for ( var i in totals_data )
	{
		data.push([totals_data[i], j]);

		var exploded_data_key = i.split("|");
		var exploded_data_key_values = new Array();

		var tick_label = '';
		for ( var k in selected_metrics )
		{
			switch ( selected_metrics[k] )
			{
				case "price":
				{
					exploded_data_key_values[k] = price_labels[exploded_data_key[k]];
					break;
				}
				case "bedrooms":
				{
					exploded_data_key_values[k] = exploded_data_key[k] + " beds";
					break;
				}
				case "property_type":
				{
					exploded_data_key_values[k] = property_type_labels[exploded_data_key[k]];
					break;
				}
				case "location":
				{
					exploded_data_key_values[k] = location_labels[exploded_data_key[k]];
					break;
				}
			}
			tick_label = exploded_data_key_values.join(", ");
		}
		tick_labels.push([j, tick_label]);

		tick_labels_set = true;

		j++;
	}

	data = [
		{ 
			label: "Total",
			data: data,
			bars: {
	        	align: "center",
	            show: true,
	            horizontal: true,
	            barWidth: 0.8
	        }
	    }
    ];

	var options = { 
		grid: { show: true, borderWidth: 0, hoverable: true, },
		legend: { show:false },
		yaxis: {  
    		//axisLabel: "Property Type",
    		//axisLabelUseCanvas: true,
    		tickFormatter: function (v, axis) {
		        return v;
		    },
		},
		xaxis: {  
    		//axisLabel: "Average Rent (£ PCM)",
    		//axisLabelUseCanvas: true,
    		tickFormatter: function (v, axis) {
		        return v;
		    },
		}
	};

    if ( tick_labels_set )
    {
    	options.yaxis.ticks = tick_labels;
    }

	$.plot($("#ph_chart"), data, options);
	$("#ph_chart").useTooltip();

	// Tooltip
	$("<div id='tooltip'></div>").css({
		position: "absolute",
		display: "none",
		border: "1px solid #fdd",
		padding: "4px",
		backgroundColor: "#333",
		color: "#FFF",
		opacity: 0.80
	}).appendTo("body");

});

jQuery.fn.useTooltip = function () {
	jQuery(this).bind("plothover", function (event, pos, item) 
	{
		if (item) {
			var x = item.datapoint[0]

			jQuery("#tooltip").html( x + " Propert" + ( (x != 1) ? 'ies' : 'y' ) )
				.css({top: item.pageY+5, left: item.pageX})
				.fadeIn(200);
		} else {
			jQuery("#tooltip").hide();
		}
	});
};

</script>

<?php
	}

	private function output_averages_chart()
	{
		$metrics = $this->get_metrics();

		$metric_one = ( ( isset($_POST['metric_one']) ) ? $_POST['metric_one'] : 'property_type' );
		$metric_two = ( ( isset($_POST['metric_two']) ) ? $_POST['metric_two'] : 'price' );
		//

		$average_data = $this->get_average_property_data($metric_one, $metric_two);

		$price_ranges = $this->get_price_ranges();
?>

<script>
var average_data = <?php echo json_encode($average_data); ?>;

var metric_one = '<?php echo $metric_one; ?>';
var metric_two = '<?php echo $metric_two; ?>';

var metric_one_labels = false;
<?php
	$labels = array();

	$metric_one_is_taxonomy = false;
	if ( taxonomy_exists($metric_one) )
	{
		$metric_one_is_taxonomy = true;

	    $args = array(
	        'hide_empty' => false,
	        'parent' => 0
	    );
	    $terms = get_terms( $metric_one, $args );
	    
	    if ( !empty( $terms ) && !is_wp_error( $terms ) )
	    {
	        foreach ($terms as $term)
	        {
	            $labels[$term->term_id] = $term->name;
	            
	            $args = array(
	                'hide_empty' => false,
	                'parent' => $term->term_id
	            );
	            $subterms = get_terms( $metric_one, $args );
	            
	            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
	            {
	                foreach ($subterms as $subterm)
	                {
	                    $labels[$subterm->term_id] = $term->name .' - ' . $subterm->name;
	                }
	            }
	        }
	    }
	}
	else
	{
		if ( $metric_one == 'price' )
		{
			foreach ( $price_ranges as $price_range )
			{
				$labels[$price_range['from']] = '£' . number_format($price_range['from']) . ' - £' . number_format($price_range['to']) . ' PCM';
			}
		}
		if ( $metric_one == 'bedrooms' )
		{
			foreach ( $average_data as $bedrooms => $value )
			{
				$labels[$bedrooms] = $bedrooms . ' Beds';
			}
		}
	}

	echo 'metric_one_labels = ' . json_encode($labels) . ';';
?>
var metric_one_is_taxonomy = <?php echo ( $metric_one_is_taxonomy ) ? 'true' : 'false'; ?>;

	jQuery(document).ready(function($)
	{
		var data = [];

		var averages = new Array();
		var average_overall = 0;
		var num_total = 0;
		for ( var i in average_data )
		{
			averages[i] = 0;
			for ( var j in average_data[i] )
			{
				averages[i] += parseInt(average_data[i][j]);

				average_overall += parseInt(average_data[i][j]);

				num_total++;
			}
			averages[i] = averages[i] / average_data[i].length;
		}
		if ( num_total > 0 )
		{
			average_overall = average_overall / num_total;
		}

		var tick_labels = new Array();
		var tick_labels_set = false;
		var j = 0;
		for ( var i in averages )
		{
			if ( metric_one_is_taxonomy ) 
			{
				if ( typeof metric_one_labels[i] != 'undefined' )
				{
					data.push([averages[i], j]);

					tick_labels.push([j, metric_one_labels[i]]);

					tick_labels_set = true;

					j++;
				}
			}
			else
			{
				data.push([averages[i], j]);

				tick_labels.push([j, metric_one_labels[i]]);

				tick_labels_set = true;

				j++;
			}
		}

		data = [
			{ 
				label: "Average",
				data: data,
				bars: {
		        	align: "center",
		            show: true,
		            horizontal: true,
		            barWidth: 0.8
		        }
		    },
		    { 
				label: "Average Overall",
				data: [ [ average_overall, 0 ], [ average_overall, (tick_labels.length - 1) ] ],
				points: { show: false },
				lines: { show: true, lineWidth: 2, fill: false },
		    }
	    ];

		var options = { 
			grid: { show: true, borderWidth: 0, hoverable: true, },
			legend: { show:false },
			yaxis: {  
        		//axisLabel: "Property Type",
        		//axisLabelUseCanvas: true,
        		tickFormatter: function (v, axis) {
			        return <?php if ($metric_one == 'price') { echo '"£" + '; } ?>v<?php if ($metric_one == 'price') { echo ' + " PCM"'; } ?>;
			    },
    		},
    		xaxis: {  
        		//axisLabel: "Average Rent (£ PCM)",
        		//axisLabelUseCanvas: true,
        		tickFormatter: function (v, axis) {
			        return <?php if ($metric_two == 'price') { echo '"£" + '; } ?>v<?php if ($metric_two == 'price') { echo ' + " PCM"'; } ?>;
			    },
    		}
		};

	    if ( tick_labels_set )
	    {
	    	options.yaxis.ticks = tick_labels;
	    }

		$.plot($("#ph_chart"), data, options);
		$("#ph_chart").useTooltip();

		// Tooltip
		$("<div id='tooltip'></div>").css({
			position: "absolute",
			display: "none",
			border: "1px solid #fdd",
			padding: "4px",
			backgroundColor: "#333",
			color: "#FFF",
			opacity: 0.80
		}).appendTo("body");
	});

	jQuery.fn.useTooltip = function () {
		jQuery(this).bind("plothover", function (event, pos, item) 
		{
			if (item) {
				var x = item.datapoint[0].toFixed(2)

				jQuery("#tooltip").html(item.series.label + " of <?php if ($metric_two == 'price') { echo '£'; } ?>" + x<?php if ($metric_two == 'price') { echo ' + " PCM"'; } ?><?php if ($metric_two == 'bedrooms') { echo ' + " bedrooms"'; } ?>)
					.css({top: item.pageY+5, left: item.pageX})
					.fadeIn(200);
			} else {
				jQuery("#tooltip").hide();
			}
		});
	};

</script>
<?php
	}
}
