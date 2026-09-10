<?php
/**
 * Plugin Name: Property Hive
 * Plugin URI: https://wordpress.org/plugins/propertyhive/
 * Description: Property Hive has everything you need to build estate agency websites
 * Version: 2.2.6
 * Author: PropertyHive
 * Author URI: https://wp-property-hive.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.6
 * Tested up to: 7.1
 * 
 * Text Domain: propertyhive
 *
 * @package PropertyHive
 * @category Core
 * @author PropertyHive
 */
  
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'PropertyHive' ) )
{
    /**
    * Main PropertyHive Class
    *
    * @class PropertyHive
    * @version 2.2.6
    */
    final class PropertyHive {
         
        /**
         * @var string
         */
        public $version = '2.2.6';
         
        /**
         * @var PropertyHive The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Query instance.
         *
         * @var PH_Query
         */
        public $query = null;

        /**
         * REST API instance.
         *
         * @var PH_Rest_Api
         */
        public $rest_api = null;

        /**
         * Email instance.
         *
         * @var PH_Emails
         */
        public $email = null;

        /**
         * License instance.
         *
         * @var PH_Licenses
         */
        public $license = null;

        /**
         * Countries instance.
         *
         * @var PH_Countries
         */
        public $countries = null;
        
        /**
         * Main PropertyHive Instance
         *
         * Ensures only one instance of PropertyHive is loaded or can be loaded.
         *
         * @static
         * @return PropertyHive - Main instance
         */
        public static function instance() 
        {
            if ( is_null( self::$_instance ) ) 
            {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        
        /**
         * Cloning is forbidden.
         *
         * @since 1.0.0
         */
        public function __clone() {
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
        }
    
        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.0.0
         */
        public function __wakeup() {
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
        }
        
        /**
         * PropertyHive Constructor.
         * @access public
         * @return PropertyHive
         */
        public function __construct() 
        {            
            // Auto-load classes on demand
            if ( function_exists( "__autoload" ) ) {
                spl_autoload_register( "__autoload" );
            }
    
            spl_autoload_register( array( $this, 'autoload' ) );
    
            // Define constants
            $this->define_constants();
            
            // Include required files
            $this->includes();
    
            // Hooks
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
            add_filter( 'propertyhive_departments', array( $this, 'setup_custom_departments' ) );
            //add_action( 'widgets_init', array( $this, 'include_widgets' ) );
            add_action( 'init', array( $this, 'init' ), 0 );
            add_action( 'init', array( $this, 'include_template_functions' ) );
            add_action( 'init', array( $this, 'unsubscribe_contact' ), 0 );
            add_action( 'init', array( 'PH_Shortcodes', 'init' ) );
            add_action( 'rest_api_init', array( $this, 'rest_api_includes' ) );
            add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
            add_action( 'wp', array( $this, 'set_cache_constants' ) );
            add_action( 'wp_update_comment_count', array( $this, 'exclude_notes_from_comment_count' ) );

            // Ensure Template Assistant add on is deactivated now the code is merged into core
            add_action('plugins_loaded', function () {
                propertyhive_deactivate_template_assistant();
            }, 1);
    
            // Loaded action
            do_action( 'propertyhive_loaded' );
        }

        public function set_cache_constants()
        {
            $page_ids = array_filter( array( ph_get_page_id( 'my_account' ) ) );

            if ( !empty($page_ids) && is_page( $page_ids ) ) 
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress/cache-plugin integration constant DONOTCACHEPAGE; cache integrations depend on this established global constant name.
                if ( !defined('DONOTCACHEPAGE') ) { define('DONOTCACHEPAGE', TRUE); }
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress/cache-plugin integration constant DONOTCACHEOBJECT; cache integrations depend on this established global constant name.
                if ( !defined('DONOTCACHEOBJECT') ) { define('DONOTCACHEOBJECT', TRUE); }
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress/cache-plugin integration constant DONOTCACHEDB; cache integrations depend on this established global constant name.
                if ( !defined('DONOTCACHEDB') ) { define('DONOTCACHEDB', TRUE); }
            }
        }

        public function setup_custom_departments( $departments )
        {
            $custom_departments = ph_get_custom_departments();

            foreach ( $custom_departments as $key => $custom_department )
            {
                $departments[$key] = $custom_department['name'];
            }

            return $departments;
        }

        public function exclude_notes_from_comment_count($post_id) {
	        global $wpdb;
	        $post_id = (int)$post_id;
	        if ( !$post_id ) {
		        return false;
	        }
	        if ( !$post = get_post($post_id) ) {
		        return false;
	        }

	        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Recount immediately after comment changes, excluding internal CRM notes; a cached count would be stale at this mutation boundary.
	        $new = (int) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*)
				FROM $wpdb->comments
				WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type != 'propertyhive_note' ", $post_id) );
	        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Core comment_count needs the recalculated non-note count; clean_post_cache immediately below invalidates the affected post.
	        $wpdb->update( $wpdb->posts, array('comment_count' => $new), array('ID' => $post_id) );

	        clean_post_cache( $post );
        }

        
        /**
         * Show action links on the plugin screen
         *
         * @param mixed $links
         * @return array
         */
        public function action_links( $links )
        {
            return array_merge( array(
                '<a href="' . admin_url( 'admin.php?page=ph-settings' ) . '">' . __( 'Settings', 'propertyhive' ) . '</a>',
                '<a href="' . esc_url( apply_filters( 'propertyhive_features_url', admin_url( 'admin.php?page=ph-settings&tab=features' ) ) ) . '">' . __( 'Features', 'propertyhive' ) . '</a>',
                '<a href="' . esc_url( apply_filters( 'propertyhive_url', 'https://wp-property-hive.com/', 'propertyhive' ) ) . '" target="_blank">' . __( 'Website', 'propertyhive' ) . '</a>',
            ), $links );
        }
    
        /**
         * Auto-load PH classes on demand to reduce memory consumption.
         *
         * @param mixed $class
         * @return void
         */
        public function autoload( $class )
        {
            $path  = null;
            $class = strtolower( $class );
            $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
            
            if ( strpos( $class, 'ph_shortcode_' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/shortcodes/';
            } elseif ( strpos( $class, 'ph_meta_box' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/admin/post-types/meta-boxes/';
            } elseif ( strpos( $class, 'ph_admin' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/admin/';
            }
    
            if ( $path && is_readable( $path . $file ) ) {
                include_once( $path . $file );
                return;
            }
    
            // Fallback
            if ( strpos( $class, 'ph_' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/';
            }
    
            if ( $path && is_readable( $path . $file ) ) {
                include_once( $path . $file );
                return;
            }
        }
    
        /**
         * Define PH Constants
         */
        private function define_constants() 
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Legacy public PH_* constants are consumed by existing themes and add-ons.
            define( 'PH_PLUGIN_FILE', __FILE__ );
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Preserve the existing public version constant used by add-ons.
            define( 'PH_VERSION', $this->version );
    
            if ( ! defined( 'PH_TEMPLATE_PATH' ) ) {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Preserve the existing theme-overridable template path constant.
                define( 'PH_TEMPLATE_PATH', $this->template_path() );
            }
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        private function includes() {
            include_once( 'includes/ph-core-functions.php' );
            include_once( 'includes/ph-update-functions.php' );
            include_once( 'includes/class-ph-install.php' );
            include_once( 'includes/class-ph-comments.php' );
            include_once( 'includes/class-ph-emails.php' );
            include_once( 'includes/class-ph-licenses.php' );
    
            if ( is_admin() ) {
                include_once( 'includes/admin/class-ph-admin.php' );
                include_once( 'includes/class-ph-third-party-contacts.php' );
            }
    
            if ( defined( 'DOING_AJAX' ) ) {
                $this->ajax_includes();
            }
    
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only Elementor editor detection selects frontend includes; it does not change saved data.
            if ( ! is_admin() || defined( 'DOING_AJAX' ) || ( isset( $_GET['action'] ) && is_string( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ) {
                $this->frontend_includes();
            }
            
            include_once( 'includes/ph-form-functions.php' );               // Form Renderers
            include_once( 'includes/class-ph-form-handler.php' );           // Form Handlers
            include_once( 'includes/class-ph-shortcodes.php' );             // Shortcodes class

            include( 'includes/class-ph-query.php' );                // The main query class
    
            include_once( 'includes/class-ph-post-types.php' );                     // Registers post types
            include_once( 'includes/class-ph-countries.php' );                     // Manages interaction with countries and currency
            
            if ( get_option( 'propertyhive_address_keyword_compare', '=' ) == 'polygon' )
            {
                include_once( 'includes/class-ph-address-keyword-polygon.php' ); // Manages getting and caching polygons associated with search terms
            }
        
            include_once( 'includes/class-ph-user-contacts.php' );          // Handles keeping contacts and users in sync

            include_once( 'includes/class-ph-avada.php' );                  // Avada / Fusion Builder
            include_once( 'includes/class-ph-bricks-builder.php' );         // Bricks Builder
            include_once( 'includes/class-ph-divi.php' );                   // Divi
            include_once( 'includes/class-ph-elementor.php' );              // Elementor
            include_once( 'includes/class-ph-salient.php' );                // Salient / WPBakery
            include_once( 'includes/class-ph-yoast-seo.php' );              // Yoast SEO
            include_once( 'includes/class-ph-rank-math.php' );              // Rank Math
            include_once( 'includes/class-ph-aioseo.php' );                 // All In One SEO
            include_once( 'includes/class-ph-duplicate-post.php' );         // Duplicate Post

            include_once( 'includes/class-ph-search-analytics.php' );       // Search Analytics

            include_once( 'includes/class-ph-additional-fields.php' );      // Additional Fields
            include_once( 'includes/class-ph-text-substitution.php' );      // Text Substitution

            include_once( 'includes/ph-pro-feature-functions.php' );        // Pro Features
            
            $this->query = new PH_Query();
            $this->email = new PH_Emails();
            $this->license = new PH_Licenses();
        }

        public function rest_api_includes()
        {
            include_once( 'includes/class-ph-rest-api.php' );
            $this->rest_api = new PH_Rest_Api();
        }
    
        /**
         * Include required ajax files.
         */
        public function ajax_includes() {
            include_once( 'includes/class-ph-ajax.php' );                   // Ajax functions for admin and the front-end
        }
    
        /**
         * Include required frontend files.
         */
        public function frontend_includes() {
            include_once( 'includes/ph-template-hooks.php' );
            include_once( 'includes/class-ph-template-loader.php' );        // Template Loader
            include_once( 'includes/class-ph-frontend-scripts.php' );       // Frontend Scripts
        }
    
        /**
         * Function used to Init PropertyHive Template Functions - This makes them pluggable by plugins and themes.
         */
        public function include_template_functions() {
            include_once( 'includes/ph-template-functions.php' );
        }
    
        /**
         * Include core widgets
         */
        public function include_widgets() {
            /*include_once( 'includes/abstracts/abstract-ph-widget.php' );
            include_once( 'includes/widgets/class-ph-widget-properties.php' );*/
        }
        
        /**
         * Contacts may store several comma-separated mailbox addresses.
         */
        private function contact_unsubscribe_recipients( $email ) {
            if ( ! is_string( $email ) || '' === $email ) {
                return array();
            }
            $recipients = array_map( 'trim', explode( ',', $email ) );
            foreach ( $recipients as $recipient ) {
                if ( ! is_email( $recipient ) ) {
                    return array();
                }
            }
            return array_values( array_unique( $recipients ) );
        }

        /**
         * Build a durable, email-bound unsubscribe link for an existing contact.
         */
        public function get_contact_unsubscribe_url( $contact_id ) {
            if ( ( ! is_int( $contact_id ) && ! is_string( $contact_id ) ) || ! ctype_digit( (string) $contact_id ) ) {
                return '';
            }
            $contact_id = (int) $contact_id;
            $email = get_post_meta( $contact_id, '_email_address', true );
            if ( ! $contact_id || 'contact' !== get_post_type( $contact_id ) || ! $this->contact_unsubscribe_recipients( $email ) ) {
                return '';
            }
            return $this->contact_unsubscribe_token_url( 'v2|' . $contact_id, $email );
        }

        /**
         * Sign the purpose/version, payload and current email with the site's secret.
         */
        private function contact_unsubscribe_token_url( $payload, $email ) {
            $signature = hash_hmac( 'sha256', 'propertyhive-unsubscribe|' . $payload . '|' . $email, wp_salt( 'auth' ) );
            return add_query_arg( 'ph_unsubscribe', rawurlencode( base64_encode( $payload . '|' . $signature ) ), site_url( '/' ) );
        }

        /**
         * Atomically limit mailbox-verification mail, including concurrent requests.
         */
        private function contact_unsubscribe_mail_slot( $contact_id ) {
            global $wpdb;
            $key = 'propertyhive_unsubscribe_cooldown_' . $contact_id;
            $now = time();
            $previous = get_option( $key, false );
            if ( false !== $previous ) {
                if ( ! is_numeric( $previous ) || (int) $previous > $now ) {
                    return false;
                }
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Compare-and-delete the expired lock atomically: delete_option could remove a newer request's lock. Invalidate the option cache immediately below.
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $key, (string) $previous ) );
                wp_cache_delete( $key, 'options' );
            }
            // INSERT IGNORE must not overwrite another request's newly acquired lock (add_option can update duplicate rows).
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- The unique option_name index is the cross-request lock; clear positive and negative option caches immediately after the atomic insert.
            $acquired = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)", $key, (string) ( $now + 5 * MINUTE_IN_SECONDS ), 'no' ) );
            wp_cache_delete( $key, 'options' );
            wp_cache_delete( 'notoptions', 'options' );
            return 1 === $acquired;
        }

        private function contact_unsubscribe_result( $message, $status = 200 ) {
            wp_die( esc_html( $message ), esc_html__( 'Unsubscribe', 'propertyhive' ), array( 'response' => (int) $status ) );
        }

        /**
         * Signed links authorize unsubscribe; old links first require mailbox proof.
         */
        public function unsubscribe_contact() {
            if ( ! isset( $_GET['ph_unsubscribe'] ) ) {
                return;
            }
            $invalid = __( 'This unsubscribe link is invalid or has expired.', 'propertyhive' );
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Recommended -- Bound the raw token's size before decoding; no value is used until signature/mailbox verification below.
            if ( ! is_string( $_GET['ph_unsubscribe'] ) || strlen( $_GET['ph_unsubscribe'] ) > 512 ) {
                $this->contact_unsubscribe_result( $invalid, 400 );
                return;
            }
            $encoded_token = sanitize_text_field( wp_unslash( $_GET['ph_unsubscribe'] ) );
            $decoded = base64_decode( $encoded_token, true );
            $parts = false !== $decoded ? explode( '|', $decoded ) : array();
            $legacy = count( $parts ) === 2;
            $version = $legacy ? 'legacy' : ( isset( $parts[0] ) ? $parts[0] : '' );
            $id_part = $legacy ? $parts[0] : ( isset( $parts[1] ) ? $parts[1] : '' );
            $contact_id = ctype_digit( $id_part ) ? (int) $id_part : 0;
            $email = $contact_id ? get_post_meta( $contact_id, '_email_address', true ) : '';
            if ( ! $contact_id || 'contact' !== get_post_type( $contact_id ) || ! $this->contact_unsubscribe_recipients( $email ) ) {
                $this->contact_unsubscribe_result( $invalid, 400 );
                return;
            }

            if ( $legacy ) {
                if ( ! hash_equals( md5( $email ), $parts[1] ) ) {
                    $this->contact_unsubscribe_result( $invalid, 400 );
                    return;
                }
                $nonce_action = 'propertyhive-unsubscribe-request-' . $contact_id;
                if ( isset( $_POST['propertyhive_unsubscribe_confirm'] ) ) {
                    if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] || ! is_string( $_POST['propertyhive_unsubscribe_confirm'] ) || '1' !== $_POST['propertyhive_unsubscribe_confirm'] || ! isset( $_POST['_wpnonce'] ) || ! is_string( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), $nonce_action ) ) {
                        $this->contact_unsubscribe_result( $invalid, 400 );
                        return;
                    }
                    if ( $this->contact_unsubscribe_mail_slot( $contact_id ) ) {
                        // Reserve the cooldown before sending, including delivery failures.
                        $nonce = wp_generate_password( 32, false, false );
                        $expires = time() + HOUR_IN_SECONDS;
                        $verify_key = 'propertyhive_unsubscribe_verify_' . $contact_id . '_' . hash( 'sha256', $nonce );
                        set_transient( $verify_key, 1, HOUR_IN_SECONDS );
                        $url = $this->contact_unsubscribe_token_url( 'v3|' . $contact_id . '|' . $expires . '|' . $nonce, $email );
                        $sent = wp_mail(
                            $this->contact_unsubscribe_recipients( $email ),
                            __( 'Confirm your unsubscribe request', 'propertyhive' ),
                            /* translators: %s: Mailbox verification URL. */
                            sprintf( __( "To confirm your unsubscribe request, open this link within one hour:\n\n%s\n\nIf you did not request this, you can ignore this email.", 'propertyhive' ), $url )
                        );
                        if ( ! $sent ) {
                            delete_transient( $verify_key );
                        }
                    }
                    $this->contact_unsubscribe_result( __( 'Please check your inbox for a confirmation link. If you recently requested one, please allow a few minutes before trying again.', 'propertyhive' ) );
                    return;
                }
                $legacy_url = add_query_arg( 'ph_unsubscribe', rawurlencode( $encoded_token ), site_url( '/' ) );
                $form = '<p>' . esc_html__( 'This older unsubscribe link requires email confirmation. Request a confirmation link to continue.', 'propertyhive' ) . '</p>';
                $form .= '<form method="post" action="' . esc_url( $legacy_url ) . '"><input type="hidden" name="propertyhive_unsubscribe_confirm" value="1"><input type="hidden" name="_wpnonce" value="' . esc_attr( wp_create_nonce( $nonce_action ) ) . '"><button type="submit">' . esc_html__( 'Send confirmation link', 'propertyhive' ) . '</button></form>';
                wp_die( wp_kses( $form, array( 'p' => array(), 'form' => array( 'method' => true, 'action' => true ), 'input' => array( 'type' => true, 'name' => true, 'value' => true ), 'button' => array( 'type' => true ) ) ), esc_html__( 'Unsubscribe', 'propertyhive' ), array( 'response' => 200 ) );
                return;
            }

            $is_verification = 'v3' === $version && count( $parts ) === 5;
            if ( ! ( 'v2' === $version && count( $parts ) === 3 ) && ! $is_verification ) {
                $this->contact_unsubscribe_result( $invalid, 400 );
                return;
            }
            $signature = array_pop( $parts );
            $expected = hash_hmac( 'sha256', 'propertyhive-unsubscribe|' . implode( '|', $parts ) . '|' . $email, wp_salt( 'auth' ) );
            if ( ! hash_equals( $expected, $signature ) || ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
                $this->contact_unsubscribe_result( $invalid, 400 );
                return;
            }
            if ( $is_verification ) {
                $verify_key = 'propertyhive_unsubscribe_verify_' . $contact_id . '_' . hash( 'sha256', $parts[3] );
                if ( ! ctype_digit( $parts[2] ) || (int) $parts[2] <= time() || ! get_transient( $verify_key ) || ! delete_transient( $verify_key ) ) {
                    $this->contact_unsubscribe_result( $invalid, 400 );
                    return;
                }
            }

            $methods = get_post_meta( $contact_id, '_forbidden_contact_methods', true );
            $methods = is_array( $methods ) ? $methods : array();
            if ( ! in_array( 'email', $methods, true ) ) {
                $methods[] = 'email';
                update_post_meta( $contact_id, '_forbidden_contact_methods', wp_slash( array_unique( $methods ) ) );
                wp_insert_comment( array(
                    'comment_post_ID' => $contact_id,
                    'comment_author' => 'Property Hive',
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url' => '',
                    'comment_date' => gmdate( 'Y-m-d H:i:s' ),
                    'comment_content' => serialize( array( 'note_type' => 'unsubscribe' ) ),
                    'comment_approved' => 1,
                    'comment_type' => 'propertyhive_note',
                ) );
            }
            $this->contact_unsubscribe_result( __( 'You have been unsubscribed successfully. Please allow up to 24 hours for this to take effect.', 'propertyhive' ) );
        }

        /**
         * Init PropertyHive when WordPress Initialises.
         */
        public function init() {
            // Before init action
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing public Property Hive extension hook before_propertyhive_init; changing the established name would detach installed callbacks.
            do_action( 'before_propertyhive_init' );
    
            // Set up localisation
            $this->load_plugin_textdomain();
    
            // Session class, handles session data for users - can be overwritten if custom handler is needed
            //$session_class = apply_filters( 'propertyhive_session_handler', 'PH_Session_Handler' );
    
            // Load class instances
            //$this->product_factory = new PH_Product_Factory();     // Product Factory to create new product instances
            $this->countries       = new PH_Countries();            // Countries class
            //$this->integrations    = new PH_Integrations();     // Integrations class
           // $this->session         = new $session_class();
    
            // Email Actions
            /*$email_actions = array(
                'propertyhive_low_stock',
                'propertyhive_no_stock',
                'propertyhive_product_on_backorder',
                'propertyhive_order_status_pending_to_processing',
                'propertyhive_order_status_pending_to_completed',
                'propertyhive_order_status_pending_to_on-hold',
                'propertyhive_order_status_failed_to_processing',
                'propertyhive_order_status_failed_to_completed',
                'propertyhive_order_status_completed',
                'propertyhive_new_customer_note',
                'propertyhive_created_customer'
            );
    
            foreach ( $email_actions as $action )
                add_action( $action, array( $this, 'send_transactional_email' ), 10, 10 );*/
    
            // Init action
            do_action( 'propertyhive_init' );
        }
    
        /**
         * Load Localisation files.
         *
         * Note: the first-loaded translation file overrides any following ones if the same translation is present
         */
        public function load_plugin_textdomain() {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core hook plugin_locale; renaming it would break the core hook contract.
            $locale = apply_filters( 'plugin_locale', get_locale(), 'propertyhive' );
    
            // Admin Locale
            if ( is_admin() ) {
                load_textdomain( 'propertyhive', WP_LANG_DIR . "/propertyhive/propertyhive-admin-$locale.mo" );
                load_textdomain( 'propertyhive', dirname( __FILE__ ) . "/i18n/languages/propertyhive-admin-$locale.mo" );
            }
            
            // Global + Frontend Locale
            load_textdomain( 'propertyhive', WP_LANG_DIR . "/propertyhive/propertyhive-$locale.mo" );
            // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Preserve bundled i18n/languages translations and the plugin_locale override on supported WordPress versions; WordPress.org language packs alone do not cover this legacy custom path.
            load_plugin_textdomain( 'propertyhive', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n/languages" );
        }
    
        /**
         * Ensure theme and server variable compatibility and setup image sizes..
         */
        public function setup_environment() {

            // IIS fallback must preserve encoded URLs and query syntax for WordPress routing.
            if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
                // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This is server-to-server URI compatibility state, not output; validate string shape and remove CR/LF while preserving URL encodings and query delimiters.
                $php_self = isset( $_SERVER['PHP_SELF'] ) && is_string( $_SERVER['PHP_SELF'] ) ? str_replace( array( "\r", "\n" ), '', wp_unslash( $_SERVER['PHP_SELF'] ) ) : '';
                $_SERVER['REQUEST_URI'] = substr( $php_self, 1 );
                if ( isset( $_SERVER['QUERY_STRING'] ) && is_string( $_SERVER['QUERY_STRING'] ) ) {
                    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Preserve the existing query string exactly apart from CR/LF; URL output escaping belongs at its eventual output boundary.
                    $_SERVER['REQUEST_URI'] .= '?' . str_replace( array( "\r", "\n" ), '', wp_unslash( $_SERVER['QUERY_STRING'] ) );
                }
            }

            // Legacy NGINX proxy compatibility; only copy syntactically valid IP addresses.
            if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && isset( $_SERVER['HTTP_REMOTE_ADDR'] ) && is_string( $_SERVER['HTTP_REMOTE_ADDR'] ) ) {
                $remote_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REMOTE_ADDR'] ) );
                if ( filter_var( $remote_address, FILTER_VALIDATE_IP ) ) {
                    $_SERVER['REMOTE_ADDR'] = $remote_address;
                }
            }

            if ( ! isset( $_SERVER['HTTPS'] ) && isset( $_SERVER['HTTP_HTTPS'] ) && is_string( $_SERVER['HTTP_HTTPS'] ) ) {
                $https = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HTTPS'] ) );
                if ( '' !== $https && '0' !== $https ) {
                    $_SERVER['HTTPS'] = $https;
                }
            }

            // Support hosts which use HTTP_X_FORWARDED_PROTO instead of HTTPS.
            if ( ! isset( $_SERVER['HTTPS'] ) && isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
                $_SERVER['HTTPS'] = '1';
            }
        }
    
        /** Helper functions ******************************************************/
    
        /**
         * Get the plugin url.
         *
         * @return string
         */
        public function plugin_url() {
            return untrailingslashit( plugins_url( '/', __FILE__ ) );
        }
    
        /**
         * Get the plugin path.
         *
         * @return string
         */
        public function plugin_path() {
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
    
        /**
         * Get the template path.
         *
         * @return string
         */
        public function template_path() {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy public template path filter; themes and extensions can customize the plugin template directory through this exact hook.
            return apply_filters( 'PH_TEMPLATE_PATH', 'propertyhive/' );
        }
    
        /**
         * Get Ajax URL.
         *
         * @return string
         */
        public function ajax_url() {
            return admin_url( 'admin-ajax.php', 'relative' );
        }
    
        /**
         * Return the WC API URL for a given request
         *
         * @param mixed $request
         * @param mixed $ssl (default: null)
         * @return string
         */
        public function api_request_url( $request, $ssl = null ) {
            if ( is_null( $ssl ) ) {
                $scheme = wp_parse_url( get_option( 'home' ), PHP_URL_SCHEME );
            } elseif ( $ssl ) {
                $scheme = 'https';
            } else {
                $scheme = 'http';
            }
    
            if ( get_option('permalink_structure') ) {
                return esc_url_raw( trailingslashit( home_url( '/ph-api/' . $request, $scheme ) ) );
            } else {
                return esc_url_raw( add_query_arg( 'ph-api', $request, trailingslashit( home_url( '', $scheme ) ) ) );
            }
        }

    }

}

/**
 * Returns the main instance of PH to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PropertyHive
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Legacy public global helper PH; the established callable name is part of the plugin/extension API and must remain stable.
function PH() {
    return PropertyHive::instance();
}

// Global for backwards compatibility.
$GLOBALS['propertyhive'] = PH();
