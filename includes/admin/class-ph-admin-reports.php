<?php
/**
 * PropertyHive Admin Reports Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Reports' ) ) :

/**
 * PH_Admin_Reports
 */
class PH_Admin_Reports {

	/**
	 * Handles the display of the main Property Hive reports page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {

		$reports        = self::get_reports();
		$first_tab      = array_keys( $reports );
		$current_tab    = ! empty( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : $first_tab[0];
		$current_report = isset( $_GET['report'] ) ? sanitize_title( $_GET['report'] ) : current( array_keys( $reports[ $current_tab ]['reports'] ) );

		include_once( 'reports/class-ph-admin-report.php' );
		include_once( 'views/html-admin-page-reports.php' );
	}

	/**
	 * Returns the definitions for the reports to show in admin.
	 *
	 * @return array
	 */
	public static function get_reports() 
	{
		$reports = array(
			'properties' => array(
				'title' => __( 'Properties', 'propertyhive' ),
				'reports' => array()
			)
		);

		if ( get_option('propertyhive_active_department_sales', '') != 'yes' )
	    {
	    	$reports['properties']['reports']['sales_property_stock_analysis'] = array(
					'title'       => __( 'Sales Property Stock Analysis', 'propertyhive' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' )
				);
			$reports['properties']['reports']['sales_property_popularity'] = array(
				'title'       => __( 'Sales Property Popularity', 'propertyhive' ),
				'description' => '',
				'hide_title'  => true,
				'callback'    => array( __CLASS__, 'get_report' )
			);
	    }

	    if ( get_option('propertyhive_active_department_lettings', '') != 'yes' )
	    {
	    	$reports['properties']['reports']['lettings_property_stock_analysis'] = array(
					'title'       => __( 'Lettings Property Stock Analysis', 'propertyhive' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' )
				);
			$reports['properties']['reports']['lettings_property_popularity'] = array(
				'title'       => __( 'Lettings Property Popularity', 'propertyhive' ),
				'description' => '',
				'hide_title'  => true,
				'callback'    => array( __CLASS__, 'get_report' )
			);
	    }

		/*if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
			$reports['applicants'] = array(
				'title'  => __( 'Applicants', 'propertyhive' ),
				'reports' => array(
					"applicant_demand_analysis" => array(
						'title'       => __( 'Applicant Demand Analysis', 'propertyhive' ),
						'description' => '',
						'hide_title'  => true,
						'callback'    => array( __CLASS__, 'get_report' )
					),
				)
			);
		}*/

		$reports = apply_filters( 'propertyhive_admin_reports', $reports );

		foreach ( $reports as $key => $report_group ) {
			if ( isset( $reports[ $key ]['charts'] ) ) {
				$reports[ $key ]['reports'] = $reports[ $key ]['charts'];
			}

			foreach ( $reports[ $key ]['reports'] as $report_key => $report ) {
				if ( isset( $reports[ $key ]['reports'][ $report_key ]['function'] ) ) {
					$reports[ $key ]['reports'][ $report_key ]['callback'] = $reports[ $key ]['reports'][ $report_key ]['function'];
				}
			}
		}

		return $reports;
	}

	/**
	 * Get a report from our reports subfolder.
	 */
	public static function get_report( $name ) {
		$name  = sanitize_title( str_replace( '_', '-', $name ) );
		$class = 'PH_Report_' . str_replace( '-', '_', $name );

		include_once( apply_filters( 'ph_admin_reports_path', 'reports/class-ph-report-' . $name . '.php', $name, $class ) );

		if ( ! class_exists( $class ) )
			return;

		$report = new $class();
		$report->output_report();
	}
}

endif;