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

		add_action( 'wp_dashboard_setup', array( $this, 'hide_non_property_hive_widgets' ), 9999 );
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

        if ( 
        	(
        		get_option('propertyhive_module_disabled_appraisals', '') != 'yes' &&
        		get_option('propertyhive_module_disabled_viewings', '') != 'yes'
       		)
       		|| 
       		apply_filters( 'propertyhive_show_my_upcoming_appointments_dashboard_widget', false ) === true
        )
        {
        	wp_add_dashboard_widget( 'propertyhive_dashboard_my_upcoming_appointments', __( 'My Upcoming Appointments', 'propertyhive' ), array( $this, 'my_upcoming_appointments_widget' ) );
        }

		if (
			get_option( 'propertyhive_module_disabled_tenancies', '' ) != 'yes' &&
			get_option( 'propertyhive_active_departments_lettings' ) == 'yes'
		)
		{
			wp_add_dashboard_widget( 'propertyhive_dashboard_upcoming_overdue_key_dates', __( 'Upcoming/Overdue Key Dates', 'propertyhive' ), array( $this, 'upcoming_overdue_key_dates_widget' ) );
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
	 * Property Hive Viewings Awaiting Applicant Feedback Widget
	 */
	public function viewings_awaiting_applicant_feedback_widget()
	{
		echo '<div id="ph_dashboard_viewings_awaiting_applicant_feedback">Loading...</div>';
	}

	/*
	 * Property Hive My Upcoming Appointments Widget
	 */
	public function my_upcoming_appointments_widget()
	{
		echo '<div id="ph_dashboard_my_upcoming_appointments">Loading...</div>';
	}

	/*
	 * Property Hive Upcoming & Overdue Key Dates Widget
	 */
	public function upcoming_overdue_key_dates_widget()
	{
		echo '<div id="ph_dashboard_upcoming_overdue_key_dates">Loading...</div>';
	}

	public function hide_non_property_hive_widgets()
	{
		$current_user = wp_get_current_user();

		$user_id = $current_user->ID;

		$crm_only_mode = get_user_meta( $user_id, 'crm_only_mode', TRUE );

		if ( $crm_only_mode == '1' )
		{
			global $wp_meta_boxes;

			if ( isset($wp_meta_boxes['dashboard']['normal']['core']) )
			{
				foreach ( $wp_meta_boxes['dashboard']['normal']['core'] as $key => $widget )
				{
					if ( strpos($key, 'property') === false && strpos($key, 'hive') === false )
					{
						unset($wp_meta_boxes['dashboard']['normal']['core'][$key]);
					}
				}
			}
			if ( isset($wp_meta_boxes['dashboard']['side']['core']) )
			{
				foreach ( $wp_meta_boxes['dashboard']['side']['core'] as $key => $widget )
				{
					if ( strpos($key, 'property') === false && strpos($key, 'hive') === false )
					{
						unset($wp_meta_boxes['dashboard']['side']['core'][$key]);
					}
				}
			}
		}
	}
}

endif;

return new PH_Admin_Dashboard();