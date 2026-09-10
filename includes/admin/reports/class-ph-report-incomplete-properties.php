<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PH_Report_Incomplete_Properties
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Reports
 * @version     1.0.0
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Report_Incomplete_Properties; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Report_Incomplete_Properties extends PH_Admin_Report {

	/**
	 * Output the report.
	 */
	public function output_report() {
?>

<style type="text/css">

.chart-with-sidebar { padding:12px 12px 12px 249px; background:#FFF; border:1px solid #DDD; min-height:300px; }
.chart-sidebar { width:225px; margin-left:-237px; float:left; }
.chart-main {  }

</style>

<br>

<div class="chart-with-sidebar">

	<div class="chart-sidebar">

		<form method="post" action="">

			<label for="missing">Show Properties Missing:</label>
			<select name="missing" id="missing" style="width:100%;">
				<option value="">One or more items</option>
				<option value="_photos"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_photos' ) { echo ' selected'; } ?>>Photos</option>
				<option value="_floorplans"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_floorplans' ) { echo ' selected'; } ?>>Floorplans</option>
				<option value="_epcs"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_epcs' ) { echo ' selected'; } ?>>EPCS</option>
				<option value="_brochures"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_brochures' ) { echo ' selected'; } ?>>Brochures</option>
				<option value="_virtual_tours"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_virtual_tours' ) { echo ' selected'; } ?>>Virtual Tours</option>
				<option value="summary"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == 'summary' ) { echo ' selected'; } ?>>Summary Description</option>
				<option value="_latitude"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['missing']) && $_POST['missing'] == '_latitude' ) { echo ' selected'; } ?>>Map Co-ordinates</option>
			</select>

			<br><br>

			<label for="department">Department:</label>
			<select name="department" id="department" style="width:100%;">
				<option value="">All</option>

				<?php
					$departments = ph_get_departments();

			        foreach ( $departments as $key => $value )
			        {
			            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
			            {
			            	echo '<option value="' . esc_attr($key) . '"';
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
			            	if ( isset($_POST['department']) && $_POST['department'] == $key ) { echo ' selected'; }
			            	echo '>' . esc_html($value) . '</option>';
			           	}
			        }
				?>
			</select>

			<br><br>

			<label for="on_market">Market Status:</label>
			<select name="on_market" id="on_market" style="width:100%;">
				<option value=""<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['on_market']) && $_POST['on_market'] == '' ) { echo ' selected'; } ?>>On Market Properties Only</option>
				<option value="all"<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 if ( isset($_POST['on_market']) && $_POST['on_market'] == 'all' ) { echo ' selected'; } ?>>All Properties</option>
			</select>

			<br><br>

			<label for="metric_two">Office:</label>
			<select name="office_id" id="office_id" style="width:100%;">
				<option value="">All Offices</option>
				<?php 
					$args = array(
						'post_type' => 'office',
						'orderby' => 'post_title',
						'order' => 'ASC',
						'nopaging' => true,
					);

					$office_query = new WP_Query( $args );

					if ( $office_query->have_posts() )
					{
						while ( $office_query->have_posts() )
						{
							$office_query->the_post();
					?>
					<option value="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
 echo esc_attr(get_the_ID()); ?>"<?php if ( isset($_POST['office_id']) && ($_POST['office_id'] == get_the_ID()) ) { echo ' selected'; } ?>><?php echo esc_html(get_the_title(get_the_ID())); ?></option>
					<?php 
						} 
					}

					wp_reset_postdata();
				?>
			</select>

			<br><br>
			<input type="submit" value="Update" class="button button-primary">

		</form>

	</div>

	<div class="chart-main">
		
		<?php
			$args = array(
				'post_type' => 'property',
				'nopaging' => true,
				'fields' => 'ids',
			);

			$meta_query = array('relation' => 'AND');

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
			if ( isset($_POST['on_market']) && $_POST['on_market'] == 'all' )
			{

			}
			else
			{
				$meta_query[] = array(
					'key' => '_on_market',
					'value' => 'yes',
				);
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
			if ( isset($_POST['department']) && is_string( $_POST['department'] ) && $_POST['department'] != '' )
			{
				$meta_query[] = array(
					'key' => '_department',
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
					'value' => ph_clean( wp_unslash( $_POST['department'] ) ),
				);
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
			if ( isset($_POST['office_id']) && is_scalar( $_POST['office_id'] ) && $_POST['office_id'] != '' )
			{
				$meta_query[] = array(
					'key' => '_office_id',
					// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
					'value' => (int)$_POST['office_id']
				);
			}

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Incomplete-property report must filter existing department, office and market metadata before inspecting all matching properties; the query retrieves IDs only.
			$args['meta_query'] = $meta_query;

			$property_query = new WP_Query( $args );

			if ( $property_query->have_posts() )
			{
				echo '<table>
				<tr>
				<th style="text-align:left;">Property Address</th>
				<th style="text-align:left;">Missing</th>
				</tr>';
				while ( $property_query->have_posts() )
				{
					$property_query->the_post();

					$property = new PH_Property( get_the_ID() );

					$missing = array();

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_photos') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						$photo = $property->get_main_photo_src();
						if ( $photo === false )
						{
							$missing[] = 'Photos';
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_floorplans') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        				{
        					$floorplans = $property->_floorplan_urls;
        					if (isset($floorplans) && is_array($floorplans) && !empty($floorplans) && isset($floorplans[0]) && isset($floorplans[0]['url']))
            				{

        					}
        					else
        					{
        						$missing[] = 'Floorplans';
        					}
        				}
        				else
        				{
							$floorplans = $property->get_floorplan_attachment_ids();
							if ( $floorplans === false || ( is_array($floorplans) && empty($floorplans) ) )
							{
								$missing[] = 'Floorplans';
							}
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_epcs') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        				{
        					$epcs = $property->_epc_urls;
        					if (isset($epcs) && is_array($epcs) && !empty($epcs) && isset($epcs[0]) && isset($epcs[0]['url']))
            				{

        					}
        					else
        					{
        						$missing[] = 'EPCs';
        					}
        				}
        				else
        				{
							$epcs = $property->get_epc_attachment_ids();
							if ( $epcs === false || ( is_array($epcs) && empty($epcs) ) )
							{
								$missing[] = 'EPCs';
							}
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_brochures') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        				{
        					$brochures = $property->_brochure_urls;
        					if (isset($brochures) && is_array($brochures) && !empty($brochures) && isset($brochures[0]) && isset($brochures[0]['url']))
            				{

        					}
        					else
        					{
        						$missing[] = 'Brochures';
        					}
        				}
        				else
        				{
							$brochures = $property->get_epc_attachment_ids();
							if ( $brochures === false || ( is_array($brochures) && empty($brochures) ) )
							{
								$missing[] = 'Brochures';
							}
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_virtual_tours') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						$virtual_tours = $property->get_virtual_tour_urls();
						if ( $virtual_tours === false || ( is_array($virtual_tours) && empty($virtual_tours) ) )
						{
							$missing[] = 'Virtual Tours';
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == 'summary') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						$summary = $property->post_excerpt;
						if ( $summary === false || $summary == '' )
						{
							$missing[] = 'Summary';
						}
					}

					if ( 
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '_latitude') ||  
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						(isset($_POST['missing']) && $_POST['missing'] == '') ||
						// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only report filters and form display on the authorized CRM reports screen.
						!isset($_POST['missing'])
					)
					{
						$latitude = $property->latitude;
						if ( $latitude === false || $latitude == '' )
						{
							$missing[] = 'Map Co-ordinates';
						}
					}

					if ( !empty($missing) )
					{
						echo '<tr>';

						echo '<td><a href="' . esc_url(get_edit_post_link( get_the_ID() )) . '">' . esc_html($property->get_formatted_full_address()) . '</a></td>';
						
						echo '<td>' . esc_html(implode(", ", $missing)) . '</td>';

						echo '</tr>';
					}
				}
				echo '</table>';
			}
			wp_reset_postdata();
		?>

	</div>

</div>

<?php
	}

}
