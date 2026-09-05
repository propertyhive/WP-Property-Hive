<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PH_Report_Sales_Pipeline
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Reports
 * @version     1.0.0
 */
class PH_Report_Sales_Pipeline extends PH_Admin_Report {

	/**
	 * Get sales departments to include in the report.
	 *
	 * Includes active custom departments based on Residential Sales.
	 *
	 * @return array
	 */
	private function get_sales_departments()
	{
		$departments = array( 'residential-sales' );
		$custom_departments = ph_get_custom_departments();

		foreach ( $custom_departments as $key => $custom_department )
		{
			if ( isset($custom_department['based_on']) && $custom_department['based_on'] == 'residential-sales' )
			{
				$departments[] = $key;
			}
		}

		return apply_filters( 'propertyhive_sales_pipeline_departments', $departments );
	}

	/**
	 * Get IDs for all sales properties within the selected office.
	 *
	 * Offers and sales do not store an office directly, so these IDs are also
	 * used to scope those stages through their related property.
	 *
	 * @param int $office_id Selected office ID.
	 * @return array
	 */
	private function get_property_ids( $office_id = 0 )
	{
		$meta_query = array(
			array(
				'key' => '_department',
				'value' => $this->get_sales_departments(),
				'compare' => 'IN'
			)
		);

		if ( $office_id > 0 )
		{
			$meta_query[] = array(
				'key' => '_office_id',
				'value' => $office_id
			);
		}

		$query = new WP_Query(
			array(
				'post_type' => 'property',
				'post_status' => 'publish',
				'fields' => 'ids',
				'nopaging' => true,
				'meta_query' => $meta_query
			)
		);

		return array_map( 'absint', $query->posts );
	}

	/**
	 * Get IDs for a post type, optionally scoped to an office or properties.
	 *
	 * @param string $post_type Post type to query.
	 * @param int    $office_id Selected office ID.
	 * @param array  $property_ids Sales property IDs.
	 * @return array
	 */
	private function get_record_ids( $post_type, $office_id, $property_ids )
	{
		$meta_query = array();

		if ( $post_type == 'appraisal' )
		{
			$meta_query[] = array(
				'key' => '_department',
				'value' => $this->get_sales_departments(),
				'compare' => 'IN'
			);

			if ( $office_id > 0 )
			{
				$meta_query[] = array(
					'key' => '_office_id',
					'value' => $office_id
				);
			}
		}
		else
		{
			$meta_query[] = array(
				'key' => '_property_id',
				'value' => !empty($property_ids) ? $property_ids : array( 0 ),
				'compare' => 'IN'
			);
		}

		$query = new WP_Query(
			array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'fields' => 'ids',
				'nopaging' => true,
				'meta_query' => $meta_query
			)
		);

		return array_map( 'absint', $query->posts );
	}

	/**
	 * Return an empty set of status totals.
	 *
	 * @param array $statuses Status keys.
	 * @return array
	 */
	private function prepare_status_counts( $statuses )
	{
		return array_fill_keys( $statuses, 0 );
	}

	/**
	 * Build the pipeline snapshot.
	 *
	 * @param int $office_id Selected office ID.
	 * @return array
	 */
	private function get_pipeline_data( $office_id = 0 )
	{
		$property_ids = $this->get_property_ids( $office_id );
		$appraisal_ids = $this->get_record_ids( 'appraisal', $office_id, $property_ids );
		$offer_ids = $this->get_record_ids( 'offer', $office_id, $property_ids );
		$sale_ids = $this->get_record_ids( 'sale', $office_id, $property_ids );

		$data = array(
			'appraisals' => $this->prepare_status_counts( array( 'pending', 'carried_out', 'won', 'instructed', 'lost', 'cancelled' ) ),
			'instructions' => array(
				'total' => count($property_ids),
				'on_market' => 0,
				'off_market' => 0,
			),
			'offers' => $this->prepare_status_counts( array( 'pending', 'accepted', 'declined', 'withdrawn' ) ),
			'sales' => $this->prepare_status_counts( array( 'current', 'exchanged', 'completed', 'fallen_through' ) ),
			'properties_with_offers' => array(),
			'active_sale_value' => 0,
		);

		foreach ( $appraisal_ids as $appraisal_id )
		{
			$status = get_post_meta( $appraisal_id, '_status', true );
			if ( isset($data['appraisals'][$status]) )
			{
				++$data['appraisals'][$status];
			}
		}

		foreach ( $property_ids as $property_id )
		{
			if ( get_post_meta( $property_id, '_on_market', true ) == 'yes' )
			{
				++$data['instructions']['on_market'];
			}
			else
			{
				++$data['instructions']['off_market'];
			}
		}

		foreach ( $offer_ids as $offer_id )
		{
			$status = get_post_meta( $offer_id, '_status', true );
			if ( isset($data['offers'][$status]) )
			{
				++$data['offers'][$status];
			}

			$property_id = (int)get_post_meta( $offer_id, '_property_id', true );
			if ( $property_id > 0 )
			{
				$data['properties_with_offers'][$property_id] = $property_id;
			}
		}

		foreach ( $sale_ids as $sale_id )
		{
			$status = get_post_meta( $sale_id, '_status', true );
			if ( isset($data['sales'][$status]) )
			{
				++$data['sales'][$status];
			}

			if ( in_array($status, array('current', 'exchanged')) )
			{
				$data['active_sale_value'] += (float)get_post_meta( $sale_id, '_amount', true );
			}
		}

		$data['properties_with_offers'] = count($data['properties_with_offers']);

		return apply_filters( 'propertyhive_sales_pipeline_data', $data, $office_id );
	}

	/**
	 * Calculate a percentage without risking division by zero.
	 *
	 * @param int $part Part value.
	 * @param int $total Total value.
	 * @return float
	 */
	private function get_percentage( $part, $total )
	{
		return $total > 0 ? round( ($part / $total) * 100, 1 ) : 0;
	}

	/**
	 * Output one pipeline section.
	 *
	 * @param string $title Section title.
	 * @param string $description Section description.
	 * @param array  $stages Stage definitions.
	 * @return void
	 */
	private function output_pipeline_section( $title, $description, $stages )
	{
		?>
		<div class="ph-pipeline-section">
			<h2><?php echo esc_html($title); ?></h2>
			<p><?php echo esc_html($description); ?></p>
			<div class="ph-pipeline-stages">
				<?php foreach ( $stages as $stage ) { ?>
					<div class="ph-pipeline-stage<?php echo !empty($stage['outcome']) ? ' ph-pipeline-stage-outcome' : ''; ?>">
						<strong><?php echo esc_html(number_format_i18n($stage['count'])); ?></strong>
						<span><?php echo esc_html($stage['label']); ?></span>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the report.
	 */
	public function output_report()
	{
		$office_id = isset($_GET['office_id']) ? absint(wp_unslash($_GET['office_id'])) : 0;
		$data = $this->get_pipeline_data( $office_id );

		$appraisal_outcomes = $data['appraisals']['won'] + $data['appraisals']['instructed'] + $data['appraisals']['lost'];
		$appraisal_successes = $data['appraisals']['won'] + $data['appraisals']['instructed'];
		$appraisal_success_rate = $this->get_percentage( $appraisal_successes, $appraisal_outcomes );
		$offer_coverage = $this->get_percentage( $data['properties_with_offers'], $data['instructions']['total'] );
		$sale_outcomes = $data['sales']['completed'] + $data['sales']['fallen_through'];
		$fall_through_rate = $this->get_percentage( $data['sales']['fallen_through'], $sale_outcomes );

		$sections = array(
			'appraisals' => array(
				'title' => __( '1. Appraisals', 'propertyhive' ),
				'description' => __( 'Prospective instructions from booking through to an outcome.', 'propertyhive' ),
				'stages' => array(
					array( 'label' => __( 'Booked', 'propertyhive' ), 'count' => $data['appraisals']['pending'] ),
					array( 'label' => __( 'Awaiting Outcome', 'propertyhive' ), 'count' => $data['appraisals']['carried_out'] ),
					array( 'label' => __( 'Won', 'propertyhive' ), 'count' => $data['appraisals']['won'] ),
					array( 'label' => __( 'Instructed', 'propertyhive' ), 'count' => $data['appraisals']['instructed'] ),
					array( 'label' => __( 'Lost', 'propertyhive' ), 'count' => $data['appraisals']['lost'], 'outcome' => true ),
					array( 'label' => __( 'Cancelled', 'propertyhive' ), 'count' => $data['appraisals']['cancelled'], 'outcome' => true ),
				)
			),
			'instructions' => array(
				'title' => __( '2. Instructions', 'propertyhive' ),
				'description' => __( 'Residential sales properties currently held in Property Hive.', 'propertyhive' ),
				'stages' => array(
					array( 'label' => __( 'Total Instructions', 'propertyhive' ), 'count' => $data['instructions']['total'] ),
					array( 'label' => __( 'On Market', 'propertyhive' ), 'count' => $data['instructions']['on_market'] ),
					array( 'label' => __( 'Off Market', 'propertyhive' ), 'count' => $data['instructions']['off_market'], 'outcome' => true ),
				)
			),
			'offers' => array(
				'title' => __( '3. Offers', 'propertyhive' ),
				'description' => __( 'Offers received against residential sales instructions.', 'propertyhive' ),
				'stages' => array(
					array( 'label' => __( 'Pending', 'propertyhive' ), 'count' => $data['offers']['pending'] ),
					array( 'label' => __( 'Accepted', 'propertyhive' ), 'count' => $data['offers']['accepted'] ),
					array( 'label' => __( 'Declined', 'propertyhive' ), 'count' => $data['offers']['declined'], 'outcome' => true ),
					array( 'label' => __( 'Withdrawn', 'propertyhive' ), 'count' => $data['offers']['withdrawn'], 'outcome' => true ),
				)
			),
			'sales' => array(
				'title' => __( '4. Sales Progression', 'propertyhive' ),
				'description' => __( 'Agreed sales moving towards exchange and completion.', 'propertyhive' ),
				'stages' => array(
					array( 'label' => __( 'Sale Agreed', 'propertyhive' ), 'count' => $data['sales']['current'] ),
					array( 'label' => __( 'Exchanged', 'propertyhive' ), 'count' => $data['sales']['exchanged'] ),
					array( 'label' => __( 'Completed', 'propertyhive' ), 'count' => $data['sales']['completed'] ),
					array( 'label' => __( 'Fallen Through', 'propertyhive' ), 'count' => $data['sales']['fallen_through'], 'outcome' => true ),
				)
			)
		);

		$sections = apply_filters( 'propertyhive_sales_pipeline_sections', $sections, $data, $office_id );
		?>
		<style type="text/css">
			.ph-sales-pipeline-header { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin:20px 0; }
			.ph-sales-pipeline-header h2 { margin:0 0 5px; font-size:23px; }
			.ph-sales-pipeline-header p { margin:0; color:#646970; }
			.ph-sales-pipeline-filter { display:flex; align-items:flex-end; gap:8px; }
			.ph-sales-pipeline-filter label { display:block; margin-bottom:4px; font-weight:600; }
			.ph-sales-pipeline-filter select { min-width:220px; }
			.ph-pipeline-highlights { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; margin:0 0 20px; }
			.ph-pipeline-highlight { padding:18px; background:#FFF; border:1px solid #DCDCDE; border-radius:3px; }
			.ph-pipeline-highlight strong { display:block; margin-bottom:4px; color:#1D2327; font-size:24px; line-height:1.2; }
			.ph-pipeline-highlight span { color:#646970; }
			.ph-sales-pipeline-journey { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; }
			.ph-pipeline-section { padding:20px; background:#FFF; border:1px solid #DCDCDE; border-radius:3px; }
			.ph-pipeline-section h2 { margin:0 0 4px; font-size:17px; }
			.ph-pipeline-section > p { min-height:36px; margin:0 0 16px; color:#646970; }
			.ph-pipeline-stages { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:8px; }
			.ph-pipeline-stage { position:relative; min-height:72px; padding:14px; background:#F0F6FC; border-left:4px solid #2271B1; }
			.ph-pipeline-stage-outcome { background:#F6F7F7; border-left-color:#8C8F94; }
			.ph-pipeline-stage strong { display:block; color:#1D2327; font-size:22px; line-height:1.2; }
			.ph-pipeline-stage span { display:block; margin-top:4px; color:#50575E; }
			.ph-sales-pipeline-note { margin-top:16px; padding:12px 16px; background:#FFF; border-left:4px solid #72AEE6; }
			@media screen and (max-width:1100px) { .ph-pipeline-highlights, .ph-sales-pipeline-journey { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
			@media screen and (max-width:782px) { .ph-sales-pipeline-header { display:block; } .ph-sales-pipeline-filter { margin-top:15px; } .ph-pipeline-highlights, .ph-sales-pipeline-journey { grid-template-columns:1fr; } }
		</style>

		<div class="ph-sales-pipeline-header">
			<div>
				<h2><?php echo esc_html(__( 'Sales Pipeline', 'propertyhive' )); ?></h2>
				<p><?php echo esc_html(__( 'A current snapshot of the residential sales journey from appraisal to completion.', 'propertyhive' )); ?></p>
			</div>
			<form method="get" action="" class="ph-sales-pipeline-filter">
				<input type="hidden" name="page" value="ph-reports">
				<input type="hidden" name="tab" value="crm">
				<input type="hidden" name="report" value="sales_pipeline">
				<div>
					<label for="office_id"><?php echo esc_html(__( 'Office', 'propertyhive' )); ?></label>
					<select name="office_id" id="office_id">
						<option value=""><?php echo esc_html(__( 'All Offices', 'propertyhive' )); ?></option>
						<?php
						$office_query = new WP_Query(
							array(
								'post_type' => 'office',
								'post_status' => 'publish',
								'orderby' => 'post_title',
								'order' => 'ASC',
								'nopaging' => true,
							)
						);

						while ( $office_query->have_posts() )
						{
							$office_query->the_post();
							?>
							<option value="<?php echo esc_attr(get_the_ID()); ?>"<?php selected( get_the_ID(), $office_id ); ?>><?php echo esc_html(get_the_title()); ?></option>
							<?php
						}
						wp_reset_postdata();
						?>
					</select>
				</div>
				<input type="submit" value="<?php echo esc_attr(__( 'Update', 'propertyhive' )); ?>" class="button button-primary">
			</form>
		</div>

		<div class="ph-pipeline-highlights">
			<div class="ph-pipeline-highlight">
				<strong><?php echo esc_html($appraisal_success_rate . '%'); ?></strong>
				<span><?php echo esc_html(__( 'Appraisal success rate', 'propertyhive' )); ?></span>
			</div>
			<div class="ph-pipeline-highlight">
				<strong><?php echo esc_html($offer_coverage . '%'); ?></strong>
				<span><?php echo esc_html(__( 'Instructions with an offer', 'propertyhive' )); ?></span>
			</div>
			<div class="ph-pipeline-highlight">
				<strong><?php echo wp_kses_post('&pound;' . ph_display_price_field($data['active_sale_value'])); ?></strong>
				<span><?php echo esc_html(__( 'Active sales agreed value', 'propertyhive' )); ?></span>
			</div>
			<div class="ph-pipeline-highlight">
				<strong><?php echo esc_html($fall_through_rate . '%'); ?></strong>
				<span><?php echo esc_html(__( 'Fall-through rate', 'propertyhive' )); ?></span>
			</div>
		</div>

		<div class="ph-sales-pipeline-journey">
			<?php
			foreach ( $sections as $section )
			{
				$this->output_pipeline_section( $section['title'], $section['description'], $section['stages'] );
			}
			?>
		</div>

		<div class="ph-sales-pipeline-note">
			<?php echo esc_html(__( 'This report is a live status snapshot. Completed and unsuccessful outcomes are historical totals, while the other figures show records currently at each stage.', 'propertyhive' )); ?>
		</div>
		<?php
	}
}
