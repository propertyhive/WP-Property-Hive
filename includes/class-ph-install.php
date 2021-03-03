<?php
/**
 * Installation related functions and actions.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Install' ) ) :

/**
 * PH_Install Class
 */
class PH_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.4.68' => array(
			'propertyhive_update_1468_on_market_change_dates',
		),
	);

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_PLUGIN_FILE, array( $this, 'deactivate' ) );

		add_action( 'admin_init', array( $this, 'install_actions' ) );
		add_action( 'admin_init', array( $this, 'check_version' ), 5 );

        add_filter( 'cron_schedules', array( $this, 'custom_cron_recurrence' ) );
		//add_action( 'in_plugin_update_message-propertyhive/propertyhive.php', array( $this, 'in_plugin_update_message' ) );
	}

	/**
	 * check_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_version() {
	    if ( ! defined( 'IFRAME_REQUEST' ) && ( get_option( 'propertyhive_version' ) != PH()->version || get_option( 'propertyhive_db_version' ) != PH()->version ) ) {
			$this->install();

			do_action( 'propertyhive_updated' );
		}
	}

	/**
	 * Install actions such as installing pages when a button is clicked.
	 */
	public function install_actions() {


	}

	/**
	 * Install Property Hive
	 */
	public function install() {

		$this->create_options();
		$this->create_tables();
		$this->create_roles();
        
		// Register post types
		include_once( 'class-ph-post-types.php' );
		PH_Post_types::register_post_types();
		PH_Post_types::register_taxonomies();

        $this->create_primary_office();
		$this->create_cron_jobs();

        $this->update();

		// Clear transient cache

		// Queue upgrades
		$current_version = get_option( 'propertyhive_version', null );
		$current_db_version = get_option( 'propertyhive_db_version', null );

        // No existing version set. This must be a new fresh install
        if ( is_null( $current_version ) && is_null( $current_db_version ) ) 
        {
            $this->create_terms();
            set_transient( '_ph_activation_redirect', 1, 30 );
        }
        
        update_option( 'propertyhive_db_version', PH()->version );

		// Check if pages are needed
		if ( ph_get_page_id( 'search_results' ) < 1 ) {
			update_option( '_ph_needs_pages', 1 );
		}
		
		// Update version
        update_option( 'propertyhive_version', PH()->version );

		// Flush rules after install
		flush_rewrite_rules();
	}

	/**
	 * Deactivate Property Hive
	 */
	public function deactivate() {
		// Cron jobs
		wp_clear_scheduled_hook( 'propertyhive_update_currency_exchange_rates' );
        wp_clear_scheduled_hook( 'propertyhive_process_email_log' );
        wp_clear_scheduled_hook( 'propertyhive_auto_email_match' );
        wp_clear_scheduled_hook( 'propertyhive_check_licenses' );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Handle updates
	 */
	public function update() {
		// Do updates
		$current_db_version = get_option( 'propertyhive_db_version' );

		include( 'ph-update-functions.php' );
		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					add_action('property_run_update_actions', $update_callback);
				}
			}
		}
		do_action('property_run_update_actions');
	}

	/**
	 * Create cron jobs (clear them first)
	 */
	private function create_cron_jobs() {
		
        // Cron jobs
		wp_clear_scheduled_hook( 'propertyhive_update_currency_exchange_rates' );
        wp_clear_scheduled_hook( 'propertyhive_process_email_log' );
        wp_clear_scheduled_hook( 'propertyhive_auto_email_match' );
        wp_clear_scheduled_hook( 'propertyhive_check_licenses' );

		$ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';

		// Schedule for midnight as it's likely traffic will be quieter at that time
		wp_schedule_event( strtotime( '00:00 tomorrow ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' ), 'daily', 'propertyhive_update_currency_exchange_rates' );
        
        wp_schedule_event( time(), 'every_fifteen_minutes', 'propertyhive_process_email_log' );

        $auto_property_match_enabled = get_option( 'propertyhive_auto_property_match', '' );

        if ( $auto_property_match_enabled == 'yes' )
        {
            wp_schedule_event( strtotime( '02:00 tomorrow ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' ), 'daily', 'propertyhive_auto_email_match' );
        }

        // Schedule for 1am as it's likely traffic will be quieter at that time
        // 1am so it doesn't run at exactly the same time as the exchange rate cron
        wp_schedule_event( strtotime( '01:00 tomorrow ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' ), 'daily', 'propertyhive_check_licenses' );
	}

    public function custom_cron_recurrence( $schedules ) 
    {
        $schedules['every_fifteen_minutes'] = array(
            'interval'  => 900,
            'display'   => __( 'Every 15 Minutes', 'textdomain' )
        );
         
        return $schedules;
    }

	/**
	 * Create pages that the plugin relies on
	 *
	 * @access public
	 * @return void
	 */
	public static function create_pages() {

        // Create page object
        $my_post = array(
          'post_title'    => __( 'Property Search', 'propertyhive' ),
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_type'     => 'page',
          'comment_status'    => 'closed',
          'ping_status'    => 'closed',
        );
        
        // Insert the post into the database
        $page_id = wp_insert_post( $my_post );
        
        update_option( 'propertyhive_search_results_page_id', $page_id );
	}

	/**
	 * Add the default terms for PH taxonomies - property types, tenures etc. Modify this at your own risk.
	 *
	 * @access public
	 * @return void
	 */
	private function create_terms() {

		$taxonomies = array(
			'availability' => array(
				array(
				    'name' => 'For Sale',
                    'departments' => array('residential-sales', 'commercial')
				),
				array(
				    'name' => 'Under Offer',
                    'departments' => array('residential-sales', 'commercial')
				),
				array(
				    'name' => 'Sold STC',
                    'departments' => array('residential-sales', 'commercial')
				),
				array(
				    'name' => 'Sold',
                    'departments' => array('residential-sales', 'commercial')
				),
				array(
				    'name' => 'To Let',
                    'departments' => array('residential-lettings', 'commercial')
				),
				array(
				    'name' => 'Let Agreed',
                    'departments' => array('residential-lettings', 'commercial')
				),
				array(
				    'name' => 'Let',
                    'departments' => array('residential-lettings', 'commercial')
				),
			),
			'property_type' => array(
			    // HOUSE
				array(
				    'name' => 'House'
				),
				array(
                    'name' => 'Detached House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Semi-Detached House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Terraced House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'End of Terrace House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Mews',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Link Detached House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Town House',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Cottage',
                    'child_of_previous' => true
                ),
                // BUNGALOW
                array(
                    'name' => 'Bungalow'
                ),
                array(
                    'name' => 'Detached Bungalow',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Semi-Detached Bungalow',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Terraced Bungalow',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'End of Terrace House',
                    'child_of_previous' => true
                ),
                // FLATS
                array(
                    'name' => 'Flat / Apartment'
                ),
                array(
                    'name' => 'Flat',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Apartment',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Maisonette',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Studio',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Penthouse',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Duplex',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Triplex',
                    'child_of_previous' => true
                ),
                // OTHER
                array(
                    'name' => 'Other'
                ),
                array(
                    'name' => 'Commercial',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Land',
                    'child_of_previous' => true
                ),
                array(
                    'name' => 'Garage',
                    'child_of_previous' => true
                ),
			),
			'commercial_property_type' => array(
				array(
                    'name' => 'Office'
                ),
                array(
                    'name' => 'Industrial'
                ),
                array(
                    'name' => 'Retail'
                ),
                array(
                    'name' => 'Land'
                ),
                array(
                    'name' => 'Health'
                ),
                array(
                    'name' => 'Motoring'
                ),
                array(
                    'name' => 'Leisure'
                ),
                array(
                    'name' => 'Investment'
                ),
			),
			'outside_space' => array(
                array(
                    'name' => 'Balcony'
                ),
                array(
                    'name' => 'South Facing Garden'
                ),
            ),
            'parking' => array(
                array(
                    'name' => 'On Road Parking'
                ),
                array(
                    'name' => 'Off Road Parking'
                ),
                array(
                    'name' => 'Driveway'
                ),
                array(
                    'name' => 'Single Garage'
                ),
                array(
                    'name' => 'Double Garage'
                ),
                array(
                    'name' => 'Triple Garage'
                ),
                array(
                    'name' => 'Carport'
                ),
            ),
            'price_qualifier' => array(
                array(
                    'name' => 'Guide Price'
                ),
                array(
                    'name' => 'Fixed Price'
                ),
                array(
                    'name' => 'Offers Over'
                ),
                array(
                    'name' => 'OIRO'
                )
            ),
            'sale_by' => array(
                array(
                    'name' => 'Tender'
                ),
                array(
                    'name' => 'Private Treaty'
                ),
                array(
                    'name' => 'Auction'
                ),
            ),
            'tenure' => array(
                array(
                    'name' => 'Freehold'
                ),
                array(
                    'name' => 'Leasehold'
                )
            ),
            'commercial_tenure' => array(
                array(
                    'name' => 'Freehold'
                ),
                array(
                    'name' => 'Leasehold'
                )
            ),
            'furnished' => array(
                array(
                    'name' => 'Furnished'
                ),
                array(
                    'name' => 'Part Furnished'
                ),
                array(
                    'name' => 'Unfurnished'
                )
            ),
            'marketing_flag' => array(
                array(
                    'name' => 'New Instruction'
                ),
                array(
                    'name' => 'Chain Free'
                )
            ),
		);

        $availability_departments = get_option( 'propertyhive_availability_departments', array() );
        if ( !is_array($availability_departments) ) { $availability_departments = array(); }

        $previous_term_id = '';
		foreach ( $taxonomies as $taxonomy => $terms ) 
		{
			// Make sure no terms for this taxonomy exist already
			$existing_terms = get_terms( $taxonomy );
			if ( is_wp_error( $existing_terms ) || (!is_wp_error( $existing_terms ) && empty( $existing_terms ) ) )
			{
				foreach ( $terms as $term ) 
				{
					if ( ! get_term_by( 'slug', sanitize_title( $term['name'] ), $taxonomy ) ) 
					{
					    $args = array();
	                    if ( isset($term['child_of_previous']) && $term['child_of_previous'] === true )
	                    {
	                        $args = array('parent' => $previous_term_id);
	                    }
						$return = wp_insert_term( $term['name'], $taxonomy, $args );
                        if ( !is_wp_error($return) )
                        {
    	                    if ( !isset($term['child_of_previous']) || (isset($term['child_of_previous']) && !$term['child_of_previous']) )
    	                    {
    	                        $previous_term_id = $return['term_id'];
    	                    }

                            if ( $taxonomy == 'availability' && is_array($term['departments']) && !empty($term['departments']) )
                            {
                                $availability_departments[$return['term_id']] = $term['departments'];
                            }
                        }
                        else
                        {
                            // Hmm... an error occurred
                            // $error_string = $result->get_error_message(); // do something with this?
                        }
					}
				}
			}
		}

        update_option( 'propertyhive_availability_departments', $availability_departments );
	}

    /**
     * Add the first office so at least one exists
     *
     * @access public
     * @return void
     */
    function create_primary_office() {
        
        $args = array(
            'post_type' => 'office'
        );
        $office_query = new WP_Query($args);
        if (!$office_query->have_posts())
        {
            // Insert office
            $office_post = array(
              'post_title'    => 'My Office',
              'post_content'  => '',
              'post_status'   => 'publish',
              'post_type'     => 'office',
            );
            
            // Insert the post into the database
            $office_post_id = wp_insert_post( $office_post );
            
            update_post_meta($office_post_id, 'primary', '1');
        }
    }

	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	function create_options() {

        add_option( 'propertyhive_active_departments_sales', 'yes', '', 'yes' );
        add_option( 'propertyhive_active_departments_lettings', 'yes', '',  'yes' );
        add_option( 'propertyhive_primary_department', 'residential-sales', '',  'yes' );

        add_option( 'propertyhive_default_country', 'GB', '', 'yes' );
        add_option( 'propertyhive_countries', array('GB'), '', 'yes' );

        add_option( 'propertyhive_applicant_match_price_range_percentage_lower', 20, '', 'no' );
        add_option( 'propertyhive_applicant_match_price_range_percentage_higher', 5, '', 'no' );

        add_option( 'propertyhive_install_timestamp', time(), '', 'no' );
        add_option( 'propertyhive_review_prompt_due_timestamp', strtotime('+30 days'), '', 'no' );

        add_option( 'propertyhive_enquiry_auto_responder_email_subject', __( 'Thank you for your enquiry', 'propertyhive' ), '', 'no' );
        add_option( 'propertyhive_enquiry_auto_responder_email_body', __( "Thank you for your recent property enquiry about [property_address_hyperlinked]. A member of our team will be in touch shortly.

Kind regards, 

" . get_bloginfo('name') . "

[similar_properties]", 'propertyhive' ), '', 'no' );

        add_option( 'propertyhive_property_match_default_email_subject', __( 'We found [property_count] that might be of interest to you', 'propertyhive' ), '', 'no' );
        add_option( 'propertyhive_property_match_default_email_body', __( "Hi [contact_dear],

Based on your requirements, we've found [property_count] below that we believe might be suitable for you:

[properties]

If you have any questions or require more information about any of the properties shown above please let me know.

Kind regards, 

" . get_bloginfo('name'), 'propertyhive' ), '', 'no' );

        add_option( 'propertyhive_viewing_applicant_booking_confirmation_email_subject', 'Your Viewing On [property_address]', '', 'no' );
        add_option( 'propertyhive_viewing_applicant_booking_confirmation_email_body', "Dear [applicant_dear],

This is confirmation that your viewing on [property_address] has been booked for [viewing_time] on [viewing_date].

Should you need to cancel or amend your booking please do not hesitate to contact us.

" . get_bloginfo('name'), '', 'no' );

        add_option( 'propertyhive_viewing_owner_booking_confirmation_email_subject', 'Viewing Booked On [property_address]', '', 'no' );
        add_option( 'propertyhive_viewing_owner_booking_confirmation_email_body', "Dear [owner_dear],

This is confirmation that a viewing has been booked at your property, [property_address], for [viewing_time] on [viewing_date].

Should you need to cancel or amend this viewing please do not hesitate to contact us.

" . get_bloginfo('name'), '', 'no' );

	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		ph_email_log - Queueing table for emails
	 *
	 * @access public
	 * @return void
	 */
	private function create_tables() {
		global $wpdb, $propertyhive;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $tables = "
        CREATE TABLE {$wpdb->prefix}ph_email_log (
            email_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            property_ids text NOT NULL,
            applicant_profile_id tinyint(3) unsigned NOT NULL,
            to_email_address varchar(255) NOT NULL,
            cc_email_address varchar(255) NOT NULL,
            bcc_email_address varchar(255) NOT NULL,
            from_name varchar(255) NOT NULL,
            from_email_address varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            body longtext NOT NULL,
            lock_id varchar(23) NOT NULL,
            locked_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            status varchar(5) NOT NULL,
            send_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            sent_by bigint(20) unsigned NOT NULL,
            PRIMARY KEY  (email_id)
        ) $collate;";

        dbDelta( $tables );
	}

	/**
	 * Create roles and capabilities
	 */
	public function create_roles() {
		global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
        
        // Property Hive Contact role
        add_role( 'property_hive_contact', __( 'Property Hive Contact', 'propertyhive' ), array(
            'read' => true,
        ) );

		if ( is_object( $wp_roles ) ) {

			$capabilities = $this->get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'editor', $cap );
				}
			}
		}
	}

	/**
	 * Get capabilities for PropertyHive - these are assigned to admin/shop manager during installation or reset
	 *
	 * @access public
	 * @return array
	 */
	public function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_propertyhive'/*,
			'view_propertyhive_reports'*/
		);

		return $capabilities;
	}

	/**
	 * propertyhive_remove_roles function.
	 *
	 * @access public
	 * @return void
	 */
	public function remove_roles() {
		/*global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}*/
	}

	/**
	 * Create files/directories
	 */
	private function create_files() {
		// Install files and folders for uploading files and prevent hotlinking
		$upload_dir =  wp_upload_dir();

		/*$files = array(
			array(
				'base' 		=> $upload_dir['basedir'] . '/propertyhive_uploads',
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all'
			),
			array(
				'base' 		=> $upload_dir['basedir'] . '/propertyhive_uploads',
				'file' 		=> 'index.html',
				'content' 	=> ''
			),
			array(
				'base' 		=> WP_PLUGIN_DIR . "/" . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/logs',
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all'
			),
			array(
				'base' 		=> WP_PLUGIN_DIR . "/" . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/logs',
				'file' 		=> 'index.html',
				'content' 	=> ''
			)
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}*/
	}

	/**
	 * Active plugins pre update option filter
	 *
	 * @param string $new_value
	 * @return string
	 */
	function pre_update_option_active_plugins( $new_value ) {
		/*$old_value = (array) get_option( 'active_plugins' );

		if ( $new_value !== $old_value && in_array( W3TC_FILE, (array) $new_value ) && in_array( W3TC_FILE, (array) $old_value ) ) {
			$this->_config->set( 'notes.plugins_updated', true );
			try {
				$this->_config->save();
			} catch( Exception $ex ) {}
		}

		return $new_value;*/
	}

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 *
	 * @return void
	 */
	function in_plugin_update_message( $args ) {
		/*$transient_name = 'ph_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

			$response = wp_remote_get( 'https://plugins.svn.wordpress.org/propertyhive/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {

				// Output Upgrade Notice
				$matches        = null;
				$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( PH_VERSION ) . '\s*=|$)~Uis';
				$upgrade_notice = '';

				if ( preg_match( $regexp, $response['body'], $matches ) ) {
					$version        = trim( $matches[1] );
					$notices        = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );
					
					if ( version_compare( PH_VERSION, $version, '<' ) ) {

						$upgrade_notice .= '<div class="ph_plugin_upgrade_notice">';

						foreach ( $notices as $index => $line ) {
							$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
						}

						$upgrade_notice .= '</div> ';
					}
				}

				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );*/
	}
}

endif;

return new PH_Install();
