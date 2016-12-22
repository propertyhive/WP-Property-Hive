<?php
/**
 * Admin Dashboard
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'PH_Admin_Dashboard' ) ) :

/**
 * PH_Admin_Dashboard Class.
 */
class PH_Admin_Dashboard {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Only hook in admin parts if the user has admin access
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
		}
	}

	/**
	 * Init dashboard widgets.
	 */
	public function init() {
		wp_add_dashboard_widget( 'propertyhive_dashboard_news', __( 'Property Hive News', 'propertyhive' ), array( $this, 'news_widget' ) );

		if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
        {
        	wp_add_dashboard_widget( 'propertyhive_dashboard_viewings_awaiting_applicant_feedback', __( 'Viewings Awaiting Applicant Feedback', 'propertyhive' ), array( $this, 'viewings_awaiting_applicant_feedback_widget' ) );
        }
	}

	/*
	 * Property Hive News Widget
	 */
	public function news_widget()
	{
		echo '<div id="ph_dashboard_news">Loading...</div>';
	}

	/*
	 * Property Hive News Widget
	 */
	public function viewings_awaiting_applicant_feedback_widget()
	{
		echo '<div id="ph_dashboard_viewings_awaiting_applicant_feedback">Loading...</div>';
	}
}

endif;

return new PH_Admin_Dashboard();