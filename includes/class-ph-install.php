<?php
/**
 * Installation related functions and actions.
 *
 * @author 		BIOSTALL
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
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_PLUGIN_FILE, array( $this, 'install' ) );

		add_action( 'admin_init', array( $this, 'install_actions' ) );
		add_action( 'admin_init', array( $this, 'check_version' ), 5 );
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
		// Install - Add pages button
        if ( ! empty( $_GET['install_propertyhive_pages'] ) ) {

			self::create_pages();

			// We no longer need to install pages
			delete_option( '_ph_needs_pages' );
			delete_transient( '_ph_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=ph-about&ph-installed=true' ) );
			exit;

		// Skip button
		} /*elseif ( ! empty( $_GET['skip_install_propertyhive_pages'] ) ) {

			// We no longer need to install pages
			delete_option( '_ph_needs_pages' );
			delete_transient( '_ph_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=ph-about' ) );
			exit;

		// Update button
		} elseif ( ! empty( $_GET['do_update_propertyhive'] ) ) {

			$this->update();

			// Update complete
			delete_option( '_ph_needs_pages' );
			delete_option( '_ph_needs_update' );
			delete_transient( '_ph_activation_redirect' );

			// What's new redirect
			wp_redirect( admin_url( 'index.php?page=ph-about&ph-updated=true' ) );
			exit;
		}*/
	}

	/**
	 * Install Property Hive
	 */
	public function install() {
        
		$this->create_options();
		/*$this->create_tables();*/
		$this->create_roles();
        
		// Register post types
		include_once( 'class-ph-post-types.php' );
		PH_Post_types::register_post_types();
		PH_Post_types::register_taxonomies();

		// Also register endpoints - this needs to be done prior to rewrite rule flush
		/*PH()->query->init_query_vars();
		PH()->query->add_endpoints();*/

		$this->create_terms();
        $this->create_primary_office();
		/*$this->create_cron_jobs();
		$this->create_files();
		$this->create_css_from_less();

		// Clear transient cache
		ph_delete_property_transients();
		ph_delete_contact_transients();
		ph_delete_enquiry_transients();*/

		// Queue upgrades
		$current_version = get_option( 'propertyhive_version', null );
		$current_db_version = get_option( 'propertyhive_db_version', null );
        
        update_option( 'propertyhive_db_version', PH()->version );

		// Check if pages are needed
		if ( ph_get_page_id( 'search_results' ) < 1 ) {
			update_option( '_ph_needs_pages', 1 );
		}
		
		// Update version
        update_option( 'propertyhive_version', PH()->version );

		// Flush rules after install
		flush_rewrite_rules();

		// Redirect to welcome screen
		set_transient( '_ph_activation_redirect', 1, 60 * 60 );
	}

	/**
	 * Handle updates
	 */
	public function update() {
		// Do updates
		$current_db_version = get_option( 'propertyhive_db_version' );

		/*if ( version_compare( $current_db_version, '1.4', '<' ) ) {
			include( 'updates/propertyhive-update-1.4.php' );
			update_option( 'propertyhive_db_version', '1.4' );
		}

		if ( version_compare( $current_db_version, '2.1.0', '<' ) || PH_VERSION == '2.1-bleeding' ) {
			include( 'updates/propertyhive-update-2.1.php' );
			update_option( 'propertyhive_db_version', '2.1.0' );
		}*/

		update_option( 'propertyhive_db_version', PH()->version );
	}

	/**
	 * Create cron jobs (clear them first)
	 */
	private function create_cron_jobs() {
		// Cron jobs
		/*wp_clear_scheduled_hook( 'propertyhive_scheduled_sales' );
		wp_clear_scheduled_hook( 'propertyhive_cancel_unpaid_orders' );
		wp_clear_scheduled_hook( 'propertyhive_cleanup_sessions' );

		$ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';

		wp_schedule_event( strtotime( '00:00 tomorrow ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' ), 'daily', 'propertyhive_scheduled_sales' );

		$held_duration = get_option( 'propertyhive_hold_stock_minutes', null );

		if ( is_null( $held_duration ) ) {
			$held_duration = '60';
		}

		if ( $held_duration != '' ) {
			wp_schedule_single_event( time() + ( absint( $held_duration ) * 60 ), 'propertyhive_cancel_unpaid_orders' );
		}

		wp_schedule_event( time(), 'twicedaily', 'propertyhive_cleanup_sessions' );*/
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
          'post_type'     => 'page'
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
				    'name' => 'For Sale'
				),
				array(
				    'name' => 'Under Offer'
				),
				array(
				    'name' => 'Sold STC'
				),
				array(
				    'name' => 'Sold'
				),
				array(
				    'name' => 'To Let'
				),
				array(
				    'name' => 'Let Agreed'
				),
				array(
				    'name' => 'Let'
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
		);

        $previous_term_id = '';
		foreach ( $taxonomies as $taxonomy => $terms ) 
		{
			foreach ( $terms as $term ) 
			{
				if ( ! get_term_by( 'slug', sanitize_title( $term['name'] ), $taxonomy ) ) 
				{
				    $args = array();
                    if ($term['child_of_previous'])
                    {
                        $args = array('parent' => $previous_term_id);
                    }
					$return = wp_insert_term( $term['name'], $taxonomy, $args );
                    if (!isset($term['child_of_previous']) || (isset($term['child_of_previous']) && !$term['child_of_previous']))
                    {
                        $previous_term_id = $return['term_id'];
                    }
				}
			}
		}
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
        
		/*// Include settings so that we can run through defaults
		include_once( 'admin/class-ph-admin-settings.php' );

		$settings = PH_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}

			// Special case to install the inventory settings.
			if ( $section instanceof PH_Settings_Products ) {
				foreach ( $section->get_settings( 'inventory' ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}*/
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		propertyhive_x_table_name - Table description
	 *
	 * @access public
	 * @return void
	 */
	private function create_tables() {
		/*global $wpdb, $propertyhive;

		$wpdb->hide_errors();

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );*/

		/**
		 * Update schemas before DBDELTA
		 *
		 * Before updating, remove any primary keys which could be modified due to schema updates
		 */
		/*if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}propertyhive_downloadable_product_permissions';" ) ) {
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}propertyhive_downloadable_product_permissions` LIKE 'permission_id';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}propertyhive_downloadable_product_permissions DROP PRIMARY KEY, ADD `permission_id` bigint(20) NOT NULL PRIMARY KEY AUTO_INCREMENT;" );
			}
		}

		// PropertyHive Tables
		$propertyhive_tables = "
	CREATE TABLE {$wpdb->prefix}propertyhive_attribute_taxonomies (
	  attribute_id bigint(20) NOT NULL auto_increment,
	  attribute_name varchar(200) NOT NULL,
	  attribute_label longtext NULL,
	  attribute_type varchar(200) NOT NULL,
	  attribute_orderby varchar(200) NOT NULL,
	  PRIMARY KEY  (attribute_id),
	  KEY attribute_name (attribute_name)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_termmeta (
	  meta_id bigint(20) NOT NULL auto_increment,
	  propertyhive_term_id bigint(20) NOT NULL,
	  meta_key varchar(255) NULL,
	  meta_value longtext NULL,
	  PRIMARY KEY  (meta_id),
	  KEY propertyhive_term_id (propertyhive_term_id),
	  KEY meta_key (meta_key)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_downloadable_product_permissions (
	  permission_id bigint(20) NOT NULL auto_increment,
	  download_id varchar(32) NOT NULL,
	  product_id bigint(20) NOT NULL,
	  order_id bigint(20) NOT NULL DEFAULT 0,
	  order_key varchar(200) NOT NULL,
	  user_email varchar(200) NOT NULL,
	  user_id bigint(20) NULL,
	  downloads_remaining varchar(9) NULL,
	  access_granted datetime NOT NULL default '0000-00-00 00:00:00',
	  access_expires datetime NULL default null,
	  download_count bigint(20) NOT NULL DEFAULT 0,
	  PRIMARY KEY  (permission_id),
	  KEY download_order_key_product (product_id,order_id,order_key,download_id),
	  KEY download_order_product (download_id,order_id,product_id)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_order_items (
	  order_item_id bigint(20) NOT NULL auto_increment,
	  order_item_name longtext NOT NULL,
	  order_item_type varchar(200) NOT NULL DEFAULT '',
	  order_id bigint(20) NOT NULL,
	  PRIMARY KEY  (order_item_id),
	  KEY order_id (order_id)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_order_itemmeta (
	  meta_id bigint(20) NOT NULL auto_increment,
	  order_item_id bigint(20) NOT NULL,
	  meta_key varchar(255) NULL,
	  meta_value longtext NULL,
	  PRIMARY KEY  (meta_id),
	  KEY order_item_id (order_item_id),
	  KEY meta_key (meta_key)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_tax_rates (
	  tax_rate_id bigint(20) NOT NULL auto_increment,
	  tax_rate_country varchar(200) NOT NULL DEFAULT '',
	  tax_rate_state varchar(200) NOT NULL DEFAULT '',
	  tax_rate varchar(200) NOT NULL DEFAULT '',
	  tax_rate_name varchar(200) NOT NULL DEFAULT '',
	  tax_rate_priority bigint(20) NOT NULL,
	  tax_rate_compound int(1) NOT NULL DEFAULT 0,
	  tax_rate_shipping int(1) NOT NULL DEFAULT 1,
	  tax_rate_order bigint(20) NOT NULL,
	  tax_rate_class varchar(200) NOT NULL DEFAULT '',
	  PRIMARY KEY  (tax_rate_id),
	  KEY tax_rate_country (tax_rate_country),
	  KEY tax_rate_state (tax_rate_state),
	  KEY tax_rate_class (tax_rate_class),
	  KEY tax_rate_priority (tax_rate_priority)
	) $collate;
	CREATE TABLE {$wpdb->prefix}propertyhive_tax_rate_locations (
	  location_id bigint(20) NOT NULL auto_increment,
	  location_code varchar(255) NOT NULL,
	  tax_rate_id bigint(20) NOT NULL,
	  location_type varchar(40) NOT NULL,
	  PRIMARY KEY  (location_id),
	  KEY tax_rate_id (tax_rate_id),
	  KEY location_type (location_type),
	  KEY location_type_code (location_type,location_code)
	) $collate;
	";
		dbDelta( $propertyhive_tables );*/
	}

	/**
	 * Create roles and capabilities
	 */
	public function create_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
            
			// Customer role
			/*add_role( 'customer', __( 'Customer', 'propertyhive' ), array(
				'read' 						=> true,
				'edit_posts' 				=> false,
				'delete_posts' 				=> false
			) );

			// Shop manager role
			add_role( 'shop_manager', __( 'Shop Manager', 'propertyhive' ), array(
				'level_9'                => true,
				'level_8'                => true,
				'level_7'                => true,
				'level_6'                => true,
				'level_5'                => true,
				'level_4'                => true,
				'level_3'                => true,
				'level_2'                => true,
				'level_1'                => true,
				'level_0'                => true,
				'read'                   => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
				'edit_users'             => true,
				'edit_posts'             => true,
				'edit_pages'             => true,
				'edit_published_posts'   => true,
				'edit_published_pages'   => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_others_posts'      => true,
				'edit_others_pages'      => true,
				'publish_posts'          => true,
				'publish_pages'          => true,
				'delete_posts'           => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'delete_others_posts'    => true,
				'delete_others_pages'    => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'unfiltered_html'        => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'list_users'             => true
			) );*/

			$capabilities = $this->get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					//$wp_roles->add_cap( 'shop_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
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

		/*$capability_types = array( 'product', 'shop_order', 'shop_coupon' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}*/

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
		}

		if ( is_object( $wp_roles ) ) {

			$capabilities = $this->get_core_capabilities();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'shop_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
				}
			}

			remove_role( 'customer' );
			remove_role( 'shop_manager' );
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
