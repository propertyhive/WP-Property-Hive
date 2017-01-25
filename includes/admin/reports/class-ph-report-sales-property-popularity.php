<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PH_Report_Sales_Property_Popularity
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Reports
 * @version     1.0.0
 */
class PH_Report_Sales_Property_Popularity extends PH_Admin_Report {

	private function get_property_view_data( $date_from, $date_to )
	{
		global $post;

		$return = array();

		$original_date_from = $date_from;
		$original_date_to = $date_to;

		$secs_diff = strtotime($date_to) - strtotime($date_from);
		
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
					'value' => 'residential-sales'
				)
			)
		);

		$property_query = new WP_Query( $args );

		if ( $property_query->have_posts() )
		{
			while ( $property_query->have_posts() )
			{
				$property_query->the_post();

				$view_statistics = get_post_meta( get_the_ID(), '_view_statistics', TRUE );

				$total_views = 0;
				$previous_time_frame = false;

				if ( is_array($view_statistics) && !empty($view_statistics) )
				{
					$date_from = $original_date_from;
					$date_to = $original_date_to;

					while (strtotime($date_from) <= strtotime($date_to)) 
					{
						if ( isset( $view_statistics[$date_from] ) )
						{
							$total_views += $view_statistics[$date_from];
						}
		                $date_from = date("Y-m-d", strtotime("+1 day", strtotime($date_from)));
					}

					$date_from = $original_date_from;
					$date_to = $original_date_to;

					$date_from = date("Y-m-d", strtotime($original_date_from) - $secs_diff - 86400);
					$date_to = date("Y-m-d", strtotime($date_to) - $secs_diff - 86400);

					while (strtotime($date_from) <= strtotime($date_to)) 
					{
						if ( isset( $view_statistics[$date_from] ) )
						{
							if ( $previous_time_frame === false ) { $previous_time_frame = 0; }
							$previous_time_frame += $view_statistics[$date_from];
						}
		                $date_from = date("Y-m-d", strtotime("+1 day", strtotime($date_from)));
					}
				}

				//if ( $total_views > 0 || $previous_time_frame !== FALSE )
				//{
					$return[get_the_ID()] = array(
						'total_views' => $total_views,
						'previous_time_frame' => $previous_time_frame
					);
				//}
			}
		}
		return $return;
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$duration = ( ( isset($_GET['duration']) ) ? ($_GET['duration'] - 1) : 6 );

		$date_from = ( ( isset($_GET['date_from']) ) ? $_GET['date_from'] : date("Y-m-d", strtotime($duration . " days ago")) );
		$date_to = ( ( isset($_GET['date_to']) ) ? $_GET['date_to'] : date("Y-m-d") );

		$property_view_data = $this->get_property_view_data( $date_from, $date_to );

		// Order by total views
		$popular_order = array();
		foreach ( $property_view_data as $property_id => $view_totals )
		{
			$popular_order[$view_totals['total_views'].'.'.$property_id] = $property_id;
		}

		krsort($popular_order);
?>

<style type="text/css">

.chart-tabs {  }
.chart-tabs ul { list-style-type:none; margin:0; padding:0; }
.chart-tabs ul li  { display:inline-block; margin:0; padding:0; }
.chart-tabs ul li a { display:block; line-height:40px; text-decoration:none; color:#333; padding:0 30px; text-align:center; border:1px solid #DDD; border-bottom:0; }
.chart-tabs ul li.active a { background:#FFF; font-weight:700; }
.chart-tabs ul li a:hover { background:#FFF; }
.chart-container { padding:12px 12px 12px 12px; background:#FFF; border:1px solid #DDD; }
.chart-panel { float:left; width:48%; border:1px solid #DDD; padding:0 12px 12px 12px; box-sizing:border-box; }
.chart-container .chart-panel:nth-child(2) { float:right;  }
.chart-container .chart-panel ul li { padding:8px; margin:0; }
.chart-container .chart-panel ul li:nth-child(odd) { background:#EEE; }
.chart-container .chart-panel ul li:nth-child(1) { background:#FFF; font-weight:700; border-bottom:1px solid #DDD; }
.chart-container .chart-panel ul li .stat { float:right; font-weight:700; }
.chart-container .chart-panel ul li .stat span { font-weight:400; color:#999; }

</style>

<br>

<div class="chart-tabs">
	<ul>
		<li<?php if ( $duration == 0 ) { echo ' class="active"'; } ?>><a href="admin.php?page=ph-reports&tab=properties&report=sales_property_popularity&duration=1">Today</a></li>
		<li<?php if ( $duration == 6 ) { echo ' class="active"'; } ?>><a href="admin.php?page=ph-reports&tab=properties&report=sales_property_popularity&duration=7">Last 7 Days</a></li>
		<li<?php if ( $duration == 29 ) { echo ' class="active"'; } ?>><a href="admin.php?page=ph-reports&tab=properties&report=sales_property_popularity&duration=30">Last 30 Days</a></li>
	</ul>
</div>

<div class="chart-container">

	<div class="chart-panel">
		
		<h3>Most Popular Properties</h3>

		<?php
			if ( !empty($popular_order) )
			{
				$slice_offset = min(10, ceil(count($popular_order) / 2));
				$most_popular = array_slice($popular_order, 0, $slice_offset);

				echo '<ul>
					<li>
						Property
						<div class="stat">Views</div>
					</li>';
				foreach ( $most_popular as $key => $property_id )
				{
					if ( $property_view_data[$property_id]['total_views'] > 0 )
					{
						$property = new PH_Property($property_id);

						echo '<li>
							<a href="' . get_edit_post_link($property_id) . '">' . $property->get_formatted_full_address() . '</a>
							<div class="stat">' . $property_view_data[$property_id]['total_views'] . ' <span>';
						if ( $property_view_data[$property_id]['total_views'] == $property_view_data[$property_id]['previous_time_frame'] )
						{
							echo '<span style="color:#999" title="No views"><span class="dashicons dashicons-arrow-right" style="color:#999"></span>-</span>';
						}
						elseif ( $property_view_data[$property_id]['previous_time_frame'] === false || $property_view_data[$property_id]['previous_time_frame'] == 0 )
						{
							echo '<span style="color:#090" title="No views in previous timeframe"><span class="dashicons dashicons-arrow-up" style="color:#090"></span>&#x221e;</span>';
						}
						else
						{
							if ( $property_view_data[$property_id]['total_views'] >= $property_view_data[$property_id]['previous_time_frame'] )
							{
								$percentage_difference = $property_view_data[$property_id]['total_views'] / $property_view_data[$property_id]['previous_time_frame'];
								$percentage_difference = $percentage_difference * 100;
								$percentage_difference = number_format($percentage_difference, 1);

								echo '<span style="color:#090" title="' . $property_view_data[$property_id]['previous_time_frame'] . ' views in previous timeframe"><span class="dashicons dashicons-arrow-up" style="color:#090"></span>' . $percentage_difference . '%</span>';
							}
							else
							{
								$percentage_difference = $property_view_data[$property_id]['total_views'] / $property_view_data[$property_id]['previous_time_frame'];
								$percentage_difference = $percentage_difference * 100;
								$percentage_difference = number_format($percentage_difference, 1);

								echo '<span style="color:#900" title="' . $property_view_data[$property_id]['previous_time_frame'] . ' views in previous timeframe"><span class="dashicons dashicons-arrow-down" style="color:#900"></span>' . $percentage_difference . '%</span>';
							}
						}
						echo '</span></div>
						</li>';

						unset($popular_order[$key]);
					}
				}
				echo '</ul>';
			}
			else
			{
				echo '<p>No view statistics available</p>';
			}
		?>

	</div>

	<div class="chart-panel">
		
		<h3>Least Popular Properties</h3>

		<?php
			if ( !empty($popular_order) )
			{
				ksort($popular_order, SORT_NUMERIC);

				$slice_offset = min(10, ceil(count($popular_order) / 2));
				$least_popular = array_slice($popular_order, 0, $slice_offset);

				echo '<ul>
					<li>
						Property
						<div class="stat">Views</div>
					</li>';
				foreach ( $least_popular as $key => $property_id )
				{
					$property = new PH_Property($property_id);

					echo '<li>
						<a href="' . get_edit_post_link($property_id) . '">' . $property->get_formatted_full_address() . '</a>
						<div class="stat">' . $property_view_data[$property_id]['total_views'] . ' <span>';
					if ( $property_view_data[$property_id]['total_views'] == $property_view_data[$property_id]['previous_time_frame'] )
					{
						echo '<span style="color:#999" title="No views"><span class="dashicons dashicons-arrow-right" style="color:#999"></span>-</span>';
					}
					elseif ( $property_view_data[$property_id]['previous_time_frame'] === false || $property_view_data[$property_id]['previous_time_frame'] == 0 )
					{
						echo '<span style="color:#090" title="No views in previous timeframe"><span class="dashicons dashicons-arrow-up" style="color:#090"></span>&#x221e;</span>';
					}
					else
					{
						if ( $property_view_data[$property_id]['total_views'] >= $property_view_data[$property_id]['previous_time_frame'] )
						{
							$percentage_difference = $property_view_data[$property_id]['total_views'] / $property_view_data[$property_id]['previous_time_frame'];
							$percentage_difference = $percentage_difference * 100;
							$percentage_difference = number_format($percentage_difference, 1);

							echo '<span style="color:#090" title="' . $property_view_data[$property_id]['previous_time_frame'] . ' views in previous timeframe"><span class="dashicons dashicons-arrow-up" style="color:#090"></span>' . $percentage_difference . '%</span>';
						}
						else
						{
							$percentage_difference = $property_view_data[$property_id]['total_views'] / $property_view_data[$property_id]['previous_time_frame'];
							$percentage_difference = $percentage_difference * 100;
							$percentage_difference = number_format($percentage_difference, 1);

							echo '<span style="color:#900" title="' . $property_view_data[$property_id]['previous_time_frame'] . ' views in previous timeframe"><span class="dashicons dashicons-arrow-down" style="color:#900"></span>' . $percentage_difference . '%</span>';
						}
					}
					echo '</span></div>
					</li>';

					unset($popular_order[$key]);
				}
				echo '</ul>';
			}
			else
			{
				echo '<p>No view statistics available</p>';
			}
		?>

	</div>

	<div style="clear:both"></div>

</div>

<?php
	}

}