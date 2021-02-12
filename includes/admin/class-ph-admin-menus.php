<?php
/**
 * Setup menus in WP admin.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Menus' ) ) :

/**
 * PH_Admin_Menus Class
 */
class PH_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'reports_menu' ), 20 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'add_ons_menu' ), 60 );

		add_action( 'admin_head', array( $this, 'menu_highlight' ) );
		//add_filter( 'menu_order', array( $this, 'menu_order' ) );
		//add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );
	}

	/**
	 * Add menu items
	 */
	public function admin_menu() {
		global $menu, $propertyhive;
        
	    //if ( current_user_can( 'manage_propertyhive' ) )
	    	$menu[] = array( '', 'read', 'separator-propertyhive', '', 'wp-menu-separator propertyhive' );

	    add_menu_page( __( 'Property Hive', 'propertyhive' ), __( 'Property Hive', 'propertyhive' ), 'manage_propertyhive', 'propertyhive' , array( $this, 'settings_page' ), PH()->plugin_url() . '/assets/images/menu-icon.png', '54.5' );

	    add_submenu_page( 'propertyhive', __( 'Properties', 'propertyhive' ), __( 'Properties', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=property'/*, array( $this, 'attributes_page' )*/ );
	    
	    if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
		    add_submenu_page( 'propertyhive', __( 'Property Owners and Landlords', 'propertyhive' ), __( 'Owners &amp; Landlords', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=contact&_contact_type=owner'/*, array( $this, 'attributes_page' )*/ );
	        add_submenu_page( 'propertyhive', __( 'Applicants', 'propertyhive' ), __( 'Applicants', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=contact&_contact_type=applicant'/*, array( $this, 'attributes_page' )*/ );
	        add_submenu_page( 'propertyhive', __( 'Third Party Contacts', 'propertyhive' ), __( 'Third Party Contacts', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=contact&_contact_type=thirdparty'/*, array( $this, 'attributes_page' )*/ );
        }

        if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
	    {
	    	$count = '';
	    	if ( apply_filters( 'propertyhive_show_admin_menu_enquiry_count', TRUE ) === TRUE )
	    	{
		    	$args = array(
		    		'post_type' => 'enquiry',
		    		'nopaging' => true,
		    		'fields' => 'ids',
		    		'meta_query' => array(
		    			array(
		    				'key' => '_status',
		    				'value' => 'open'
		    			),
		    			array(
		    				'key' => '_negotiator_id',
		    				'value' => ''
		    			),
		    		),
		    	);
		    	$enquiry_query = new WP_Query( $args );
		    	if ( $enquiry_query->have_posts() )
		    	{
		    		$count = ' <span class="update-plugins count-' . $enquiry_query->found_posts . '"><span class="plugin-count">' . $enquiry_query->found_posts . '</span></span>';
		    	}
		    }
        	add_submenu_page( 'propertyhive', __( 'Enquiries', 'propertyhive' ), __( 'Enquiries', 'propertyhive' ) . $count, 'manage_propertyhive', 'edit.php?post_type=enquiry'/*, array( $this, 'attributes_page' )*/ );
        }
        
        if ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' )
	    {
        	add_submenu_page( 'propertyhive', __( 'Appraisals', 'propertyhive' ), __( 'Appraisals', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=appraisal'/*, array( $this, 'attributes_page' )*/ );
        }

        if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
	    {
        	add_submenu_page( 'propertyhive', __( 'Viewings', 'propertyhive' ), __( 'Viewings', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=viewing'/*, array( $this, 'attributes_page' )*/ );
        }

        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
	    {
	        add_submenu_page( 'propertyhive', __( 'Offers', 'propertyhive' ), __( 'Offers', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=offer'/*, array( $this, 'attributes_page' )*/ );
	        add_submenu_page( 'propertyhive', __( 'Sales', 'propertyhive' ), __( 'Sales', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=sale'/*, array( $this, 'attributes_page' )*/ );
	    }

	    if ( get_option('propertyhive_module_disabled_tenancies', '') != 'yes' )
	    {
	        add_submenu_page( 'propertyhive', __( 'Tenancies', 'propertyhive' ), __( 'Tenancies', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=tenancy'/*, array( $this, 'attributes_page' )*/ );

            $count = '';
            $args = array(
                'post_type' => 'key_date',
                'nopaging' => true,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => '_key_date_status',
                        'value' => 'pending'
                    ),
                    array(
                        'key' => '_date_due',
                        'value' => date('Y-m-d'),
                        'type' => 'date',
                        'compare' => '<=',
                    ),
                ),
            );
            $enquiry_query = new WP_Query( $args );
            if ( $enquiry_query->have_posts() )
            {
                $count = ' <span class="update-plugins count-' . $enquiry_query->found_posts . '"><span class="plugin-count">' . $enquiry_query->found_posts . '</span></span>';
            }
            add_submenu_page( 'propertyhive', __( 'Management', 'propertyhive' ), __( 'Management', 'propertyhive' ) . $count, 'manage_propertyhive', 'edit.php?post_type=key_date&orderby=date_due&order=asc&status=upcoming_and_overdue&filter_action=Filter' );
	    }

    	if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
	        add_submenu_page( null, __( 'Applicant Matching Properties', 'propertyhive'), __( 'Applicant Matching Properties', 'propertyhive' ), 'manage_propertyhive', 'ph-matching-properties', array($this, 'matching_properties_page'));
	        add_submenu_page( null, __( 'Generate Applicant List', 'propertyhive'), __( 'Generate Applicant List', 'propertyhive' ), 'manage_propertyhive', 'ph-generate-applicant-list', array($this, 'generate_applicant_list_page'));
	        add_submenu_page( null, __( 'Applicant Matching Applicants', 'propertyhive'), __( 'Applicant Matching Properties', 'propertyhive' ), 'manage_propertyhive', 'ph-matching-applicants', array($this, 'matching_applicants_page'));
	    }
    }

	/**
	 * Add menu item
	 */
	public function reports_menu() {
		add_submenu_page( 'propertyhive', __( 'Reports', 'propertyhive' ),  __( 'Reports', 'propertyhive' ) , 'manage_propertyhive', 'ph-reports', array( $this, 'reports_page' ) );
	}

	/**
	 * Add menu item
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'propertyhive', __( 'Property Hive Settings', 'propertyhive' ),  __( 'Settings', 'propertyhive' ) , 'manage_options', 'ph-settings', array( $this, 'settings_page' ) );

		//add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}

	/**
	 * Add menu item
	 */
	public function add_ons_menu() {
		$settings_page = add_submenu_page( 'propertyhive', __( 'Add Ons', 'propertyhive' ),  __( 'Add Ons', 'propertyhive' ) , 'manage_options', 'admin.php?page=ph-settings&tab=addons'/*, array( $this, 'settings_page' )*/ );
	}

	/**
	 * Loads gateways and shipping methods into memory for use within settings.
	 */
	public function settings_page_init() {
		
	}

	/**
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @access public
	 * @return void
	 */
	public function menu_highlight() {
	    
		global $menu, $submenu, $parent_file, $submenu_file, $self, $post_type, $taxonomy;

		$to_highlight_types = array( 'property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date' );

		if ( isset( $post_type ) ) {
			if ( in_array( $post_type, $to_highlight_types ) ) {
				$submenu_file = 'edit.php?post_type=' . esc_attr( $post_type );
				$parent_file  = 'propertyhive';
			}
		}

		if ( isset( $submenu['propertyhive'] ) && isset( $submenu['propertyhive'][1] ) ) {
			$submenu['propertyhive'][0] = $submenu['propertyhive'][1];
			unset( $submenu['propertyhive'][1] );
		}
	}

	/**
	 * Reorder the PH menu items in admin.
	 *
	 * @param mixed $menu_order
	 * @return array
	 */
	public function menu_order( $menu_order ) {
		
        
		// Initialize our custom order array
		$propertyhive_menu_order = array();

		// Get the index of our custom separator
		$propertyhive_separator = array_search( 'separator-propertyhive', $menu_order );

		// Get index of product menu
		$propertyhive_property = array_search( 'edit.php?post_type=property', $menu_order );

		// Loop through menu order and do some rearranging
		foreach ( $menu_order as $index => $item ) :

			if ( ( ( 'propertyhive' ) == $item ) ) :
				$propertyhive_menu_order[] = 'separator-propertyhive';
				$propertyhive_menu_order[] = $item;
				$propertyhive_menu_order[] = 'edit.php?post_type=property';
				unset( $menu_order[$propertyhive_separator] );
				unset( $menu_order[$propertyhive_property] );
			elseif ( !in_array( $item, array( 'separator-propertyhive' ) ) ) :
				$propertyhive_menu_order[] = $item;
			endif;

		endforeach;

		// Return order
		return $propertyhive_menu_order;
	}

	/**
	 * custom_menu_order
	 * @return bool
	 */
	public function custom_menu_order() {
		if ( ! current_user_can( 'manage_propertyhive' ) )
			return false;
		return true;
	}

	/**
	 * Init the reports page
	 */
	public function reports_page() {
		include_once( 'class-ph-admin-reports.php' );
		PH_Admin_Reports::output();
	}

	/**
	 * Init the settings page
	 */
	public function settings_page() {
		include_once( 'class-ph-admin-settings.php' );
		PH_Admin_Settings::output();
	}

	/**
	 * Init the applicant matching properties page
	 */
	public function matching_properties_page() {
		include_once( 'class-ph-admin-matching-properties.php' );
		$ph_admin_matching_properties = new PH_Admin_Matching_Properties();
		$ph_admin_matching_properties->output();
	}

	/**
	 * Init the applicant list page
	 */
	public function generate_applicant_list_page() {
		include_once( 'class-ph-admin-applicant-list.php' );
		PH_Admin_Applicant_List::output();
	}

	/**
	 * Init the property matching applicants page
	 */
	public function matching_applicants_page() {
		include_once( 'class-ph-admin-matching-applicants.php' );
		$ph_admin_matching_applicants = new PH_Admin_Matching_Applicants();
		$ph_admin_matching_applicants->output();
	}

	/**
	 * Init the status page
	 */
	public function status_page() {
		$page = include( 'class-ph-admin-status.php' );
		$page->output();
	}

}

endif;

return new PH_Admin_Menus();