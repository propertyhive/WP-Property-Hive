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
	 * Reports page.
	 *
	 * Handles the display of the main Property Hive reports page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {

		global $current_section, $current_tab;

		$tabs = array(
			'property-stock-analysis' => 'Property Stock Analysis'
		);

		if ( $current_tab == '' )
		{
			reset($tabs);
			$current_tab = key($tabs);
		}

		include 'views/html-admin-reports.php';
	}
}

endif;