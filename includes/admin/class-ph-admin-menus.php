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
		add_action( 'admin_menu', array( $this, 'crm_only_mode_menu' ), 99 );

		add_action( 'admin_head', array( $this, 'menu_highlight' ) );
	}

	public function crm_only_mode_menu()
	{
		$current_user = wp_get_current_user();

		$user_id = $current_user->ID;

		$crm_only_mode = get_user_meta( $user_id, 'crm_only_mode', TRUE );

		if ( $crm_only_mode == '1' )
		{
			global $menu, $submenu, $wp_filter;
			
			// remove all top-level menu items that isn't the Dashboard
			foreach ( $menu as $i => $menuitem )
			{
				if ( 
					( !isset($menuitem[2]) || ( isset($menuitem[2]) && $menuitem[2] != 'index.php' ) )
					&&
					apply_filters( 'propertyhive_remove_menu_item_in_crm_only_mode', true, $menuitem ) === true
				)
				{
					unset($menu[$i]);
				}
			}
			if ( isset($submenu['propertyhive']) )
			{
				unset($submenu['propertyhive'][0]);
				$position = 5;
				foreach ( $submenu['propertyhive'] as $submenuitem )
				{
					$callback = '';
					if ( substr($submenuitem[2], 0, 3) == 'ph-' )
					{
						$callback = array( $this, substr($submenuitem[2], 3)  . '_page' );
					}
					elseif ( isset($wp_filter['property-hive_page_' . $submenuitem[2]]) )
					{
						// get class name from callbacks then convert it to class name
						// i.e. convert PH_Property_Import to PHPI()
						$class_name = '';
						$function_name = '';
						if ( isset($wp_filter['property-hive_page_' . $submenuitem[2]]->callbacks) )
						{
							foreach ( $wp_filter['property-hive_page_' . $submenuitem[2]]->callbacks as $priority => $filter_callbacks )
							{
								foreach ( $filter_callbacks as $oddkey => $filter_callback )
								{
									if ( isset($filter_callback['function']) && count($filter_callback['function']) >= 2 )
									{
										$class_name = get_class($filter_callback['function'][0]);
										$explode_class_name = explode("_", $class_name);

										$class_name_bits = array();
										foreach ( $explode_class_name as $exploded_class_name_bit )
										{
											if ( $exploded_class_name_bit == 'PH' )
											{
												$class_name_bits[] = $exploded_class_name_bit;
											}
											else
											{
												$class_name_bits[] = strtoupper(substr($exploded_class_name_bit, 0, 1));
											}
										}

										$class_name = implode("", $class_name_bits);
										$function_name = $filter_callback['function'][1];
									}
								}
							}
						}

						if ( $class_name != '' && $function_name != '' )
						{
							$callback = array( $class_name(), $function_name );
						}
					}
					add_menu_page( $submenuitem[3], $submenuitem[0], $submenuitem[1], $submenuitem[2], $callback, $this->get_menu_icon($submenuitem[2]), $position );
					$position += 5;
				}
				unset($submenu['propertyhive']);
			}
		}
	}

	/**
	 * Add menu items
	 */
	public function admin_menu() {
		global $menu, $propertyhive;
        
	    //if ( current_user_can( 'manage_propertyhive' ) )
	    	$menu[] = array( '', 'read', 'separator-propertyhive', '', 'wp-menu-separator propertyhive' );

	    add_menu_page( __( 'Property Hive', 'propertyhive' ), __( 'Property Hive', 'propertyhive' ), 'manage_propertyhive', 'propertyhive' , array( $this, 'settings_page' ), $this->get_menu_icon(), '54.5' );

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

	    if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' && get_option('propertyhive_module_disabled_tenancies', '') != 'yes' )
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
            $key_date_query = new WP_Query( $args );
            if ( $key_date_query->have_posts() )
            {
                $count = ' <span class="update-plugins count-' . $key_date_query->found_posts . '"><span class="plugin-count">' . $key_date_query->found_posts . '</span></span>';
            }
            add_submenu_page( 'propertyhive', __( 'Management', 'propertyhive' ), __( 'Management', 'propertyhive' ) . $count, 'manage_propertyhive', 'edit.php?post_type=key_date&orderby=date_due&order=asc&status=upcoming_and_overdue&filter_action=Filter' );
	    }

    	if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
	        add_submenu_page( null, __( 'Applicant Matching Properties', 'propertyhive'), __( 'Applicant Matching Properties', 'propertyhive' ), 'manage_propertyhive', 'ph-matching-properties', array($this, 'matching_properties_page'));
	        add_submenu_page( null, __( 'Generate Applicant List', 'propertyhive'), __( 'Generate Applicant List', 'propertyhive' ), 'manage_propertyhive', 'ph-generate-applicant-list', array($this, 'generate_applicant_list_page'));
	        add_submenu_page( null, __( 'Applicant Matching Applicants', 'propertyhive'), __( 'Applicant Matching Properties', 'propertyhive' ), 'manage_propertyhive', 'ph-matching-applicants', array($this, 'matching_applicants_page'));
	        add_submenu_page( null, __( 'Merge Duplicate Contacts', 'propertyhive'), __( 'Merge Duplicate Contacts', 'propertyhive' ), 'manage_propertyhive', 'ph-merge-duplicate-contacts', array($this, 'generate_merge_duplicate_contacts_page'));
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

		$current_user = wp_get_current_user();

		$user_id = $current_user->ID;

		$crm_only_mode = get_user_meta( $user_id, 'crm_only_mode', TRUE );

		if ( $crm_only_mode == '1' )
		{
			if ( $post_type == 'contact' && isset($_GET['_contact_type']) && !empty(ph_clean($_GET['_contact_type'])) )
			{
				$parent_file = 'edit.php?post_type=contact&_contact_type=' . ph_clean($_GET['_contact_type']);
			}
		}
		else
		{
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
		$ph_admin_applicant_list = new PH_Admin_Applicant_List();
		$ph_admin_applicant_list->output();
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
	 * Init the merge contacts page
	 */
	public function generate_merge_duplicate_contacts_page() {
		include_once( 'class-ph-admin-merge-contacts.php' );
		$ph_admin_merge_contacts = new PH_Admin_Merge_Contacts();
		$ph_admin_merge_contacts->output();
	}

	private function get_menu_icon( $section = '' )
	{
		// extract post type
		$explode_section = explode("?", $section, 2);
		$tab = '';
		if ( count($explode_section) == 2 )
		{
			parse_str( $explode_section[1], $array );
			
			if ( isset($array['post_type']) )
			{
				$section = $array['post_type'];
			}
			elseif ( isset($array['page']) )
			{
				$section = $array['page'];
				if ( isset($array['tab']) )
				{
					$tab = $array['tab'];
				}
			}
		}

		$icon = PH()->plugin_url() . '/assets/images/menu-icon.png';
		switch ( $section )
		{
			case "contact":
			{
				$icon = "dashicons-admin-users";
				break;
			}
			case "property":
			{
				$icon = "dashicons-admin-home";
				break;
			}
			case "appraisal":
			{
				$icon = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzgwIiBoZWlnaHQ9IjM5MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KIDxnPgogIDx0aXRsZT5MYXllciAxPC90aXRsZT4KICA8cGF0aCBmaWxsPSIjZjBmMGYxIiBpZD0ic3ZnXzEiIGQ9Im0xOTAuMjA5MDU3LDYuMjExNDA4Yy0xMDMuNzU0LDAgLTE4OC4xNjEsODQuNDEzIC0xODguMTYxLDE4OC4xNjdzODQuNDA3LDE4OC4xNTUgMTg4LjE2MSwxODguMTU1YzEwMy43NiwwIDE4OC4xNjcsLTg0LjQwMSAxODguMTY3LC0xODguMTU1cy04NC40MDcsLTE4OC4xNjcgLTE4OC4xNjcsLTE4OC4xNjd6bTAsMzQ2LjY0MmMtODcuMzgzLDAgLTE1OC40NzYsLTcxLjA5MyAtMTU4LjQ3NiwtMTU4LjQ3NmMwLC04Ny4zOTUgNzEuMDkyLC0xNTguNDg3IDE1OC40NzYsLTE1OC40ODdjODcuMzg5LDAgMTU4LjQ4Nyw3MS4wOTMgMTU4LjQ4NywxNTguNDg3YzAuMDAxLDg3LjM4MyAtNzEuMDk4LDE1OC40NzYgLTE1OC40ODcsMTU4LjQ3NnoiLz4KICA8cGF0aCBmaWxsPSIjZjBmMGYxIiBpZD0ic3ZnXzIiIGQ9Im0yMTcuMjIxMDYsMTE1LjY4MjRjMTQuODA2LDAgMTguODY3LDEwLjczOSAyMi45MjksMjMuNTIybDEzLjkzOCwtMTcuNzA3Yy00LjY0NSwtMTguNTg3IC0xNy40MjIsLTI5LjYxMSAtNDIuOTY3LC0yOS42MTFjLTQzLjg0NSwwIC00OS4wNjYsMzEuOTMxIC00OS4wNjYsNTkuNzk0bDAsMzAuMjA1bC0yMS40OSwwbC01LjgwMywyNC4zOTFsMjUuODM1LDBjLTMuMTkzLDMyLjgxMSAtMTEuNjEyLDYzLjU3NSAtMzQuMjYxLDkwLjU4MmwxMjUuMTQ2LDBsMCwtMzAuMTk1bC03MC41NjcsMGM5LjAwMiwtMjAuNjIxIDEyLjQ5OCwtMzguNjE0IDEzLjk0OCwtNjAuMzg5bDQ4LjQ3OSwwbDUuODEsLTI0LjM5MWwtNTMuMTM0LDBsMCwtMjQuMzkxYzAsLTIyLjY0MSAxLjE2NiwtNDEuODEgMjEuMjAzLC00MS44MXoiLz4KIDwvZz4KPC9zdmc+";
				break;
			}
			case "viewing":
			{
				$icon = "dashicons-visibility";
				break;
			}
			case "enquiry":
			{
				$icon = "dashicons-admin-comments";
				break;
			}
			case "offer":
			case "sale":
			{
				$icon = "dashicons-tag";
				break;
			}
			case "tenancy":
			{
				$icon = "dashicons-admin-network";
				break;
			}
			case "key_date":
			{
				$icon = "dashicons-admin-network";
				break;
			}
			case "ph-reports":
			{
				$icon = "dashicons-chart-bar";
				break;
			}
			case "ph-settings":
			{
				$icon = "dashicons-admin-settings";
				if ( $tab == 'addons' )
				{
					$icon = "dashicons-insert";
				}
				break;
			}
		}
		$icon = apply_filters( 'propertyhive_menu_icon', $icon, $section );
		return $icon;
	}

}

endif;

return new PH_Admin_Menus();