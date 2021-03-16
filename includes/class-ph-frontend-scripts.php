<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle frontend forms
 *
 * @class 		PH_Frontend_Scripts
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Frontend_Scripts {

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'check_jquery' ), 25 );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend
	 * @return array
	 */
	public static function get_styles() {
		return apply_filters( 'propertyhive_enqueue_styles', array(
			'propertyhive-general' => array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/css/propertyhive.css',
				'deps'    => '',
				'version' => PH_VERSION,
				'media'   => 'all'
			),
		) );
	}

	/**
	 * Register/queue frontend scripts.
	 *
	 * @access public
	 * @return void
	 */
	public static function load_scripts() {
		global $post;

		//$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
		$frontend_script_path = $assets_path . 'js/frontend/';

		// Register any scripts for later use, or used as dependencies
		wp_register_script( 'jquery-cookie', $assets_path . 'js/jquery-cookie/jquery.cookie' . $suffix . '.js', array( 'jquery' ), '1.3.1', true );

		wp_register_script( 'propertyhive_fancybox', $assets_path . 'js/fancybox/jquery.fancybox' . $suffix . '.js', array( 'jquery' ), '3.5.7', true );
		wp_register_style( 'propertyhive_fancybox_css', $assets_path . 'css/jquery.fancybox' . $suffix . '.css', array(), '3.5.7' );

		if ( get_option('propertyhive_lettings_fees_display_search_results', '') == 'yes' )
		{
			wp_enqueue_script( 'propertyhive_fancybox' );
			wp_enqueue_style( 'propertyhive_fancybox_css' );
		}

		if ( is_property() ) {
			wp_enqueue_script( 'propertyhive_fancybox' );
			wp_enqueue_style( 'propertyhive_fancybox_css' );

		    wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.7.2', true );
            wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
            wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css', array(), '2.7.2' );
        }

		// Global frontend scripts
		wp_enqueue_script( 'propertyhive_search', $frontend_script_path . 'search' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
		wp_enqueue_script( 'propertyhive_make_enquiry', $frontend_script_path . 'make-enquiry' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
        //wp_enqueue_script( 'propertyhive', $frontend_script_path . 'propertyhive' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );

        wp_register_script( 'multiselect', $assets_path . 'js/multiselect/jquery.multiselect' . /*$suffix .*/ '.js', array('jquery'), '2.4.18', true );
        wp_enqueue_style( 'multiselect', $assets_path . 'css/jquery.multiselect.css', array(), '2.4.18' );
        
		// CSS Styles
		$enqueue_styles = self::get_styles();

		if ( $enqueue_styles ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				wp_enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
		}
	}

	/**
	 * Localize scripts only when enqueued
	 */
	public static function localize_printed_scripts() {
		global $wp;

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

		if ( wp_script_is( 'propertyhive_search' ) ) {
			wp_localize_script( 'propertyhive_search', 'propertyhive_search_params', apply_filters( 'propertyhive_search_params', array(
				'custom_departments'	=> ph_get_custom_departments(),
			) ) );
		}

		if ( wp_script_is( 'propertyhive_make_enquiry' ) ) {
			wp_localize_script( 'propertyhive_make_enquiry', 'propertyhive_make_property_enquiry_params', apply_filters( 'propertyhive_make_property_enquiry_params', array(
				'ajax_url'        => PH()->ajax_url()
			) ) );
		}

		if ( wp_script_is( 'propertyhive_account' ) ) {
			wp_localize_script( 'propertyhive_account', 'propertyhive_account_params', apply_filters( 'propertyhive_account_params', array(
				'ajax_url'        		=> PH()->ajax_url(),
				'my_account_url'  		=> get_permalink( ph_get_page_id('my_account') ),
				'custom_departments'	=> ph_get_custom_departments(),
				'login_nonce'	  		=> wp_create_nonce( "ph_login" ),
				'register_nonce'	  	=> wp_create_nonce( "ph_register" ),
				'details_nonce'	  		=> wp_create_nonce( "ph_details" ),
				'requirements_nonce'	=> wp_create_nonce( "ph_requirements" ),
			) ) );
		}
	}

	/**
	 * PH requires jQuery 1.8 since it uses functions like .on() for events and .parseHTML.
	 * If, by the time wp_print_scrips is called, jQuery is outdated (i.e not
	 * using the version in core) we need to deregister it and register the
	 * core version of the file.
	 *
	 * @access public
	 * @return void
	 */
	public static function check_jquery() {
		global $wp_scripts;

		// Enforce minimum version of jQuery
		if ( ! empty( $wp_scripts->registered['jquery']->ver ) && ! empty( $wp_scripts->registered['jquery']->src ) && 0 >= version_compare( $wp_scripts->registered['jquery']->ver, '1.8' ) ) {
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', '/wp-includes/js/jquery/jquery.js', array(), '1.8' );
			wp_enqueue_script( 'jquery' );
		}
	}
}

PH_Frontend_Scripts::init();
