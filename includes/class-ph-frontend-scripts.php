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
			/*'propertyhive-layout' => array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/css/propertyhive-layout.css',
				'deps'    => '',
				'version' => PH_VERSION,
				'media'   => 'all'
			),
			'propertyhive-smallscreen' => array(
				'src'     => str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/css/propertyhive-smallscreen.css',
				'deps'    => 'propertyhive-layout',
				'version' => PH_VERSION,
				'media'   => 'only screen and (max-width: ' . apply_filters( 'propertyhive_style_smallscreen_breakpoint', $breakpoint = '768px' ) . ')'
			),*/
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

		$carouselInUse = has_shortcode( $post->post_content, 'featured_property_carousel' );
		if ( is_property() ) {
			wp_enqueue_script( 'prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.5', true );
			wp_enqueue_script( 'prettyPhoto-init', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery','prettyPhoto' ), PH_VERSION, true );
			wp_enqueue_style( 'propertyhive_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css' );

			wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init.property' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
			
            wp_enqueue_script( 'propertyhive_actions', $frontend_script_path . 'actions' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
            
		    wp_enqueue_script( 'ph-single-property' );
        }
        if ( is_property() || $carouselInUse ) {
            wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.2.2', true );
            wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css' );
        }
        if ( $carouselInUse ) {
            wp_enqueue_script( 'flexslider-init-shortcode', $assets_path . 'js/flexslider/jquery.flexslider.init.shortcode' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
        }
 
		// Global frontend scripts
		wp_enqueue_script( 'propertyhive_search', $frontend_script_path . 'search' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
		wp_enqueue_script( 'propertyhive_make_enquiry', $frontend_script_path . 'make-enquiry' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
        //wp_enqueue_script( 'propertyhive', $frontend_script_path . 'propertyhive' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );
		
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

		if ( wp_script_is( 'propertyhive_make_enquiry' ) ) {
			wp_localize_script( 'propertyhive_make_enquiry', 'propertyhive_make_property_enquiry_params', apply_filters( 'propertyhive_make_property_enquiry_params', array(
				'ajax_url'        => PH()->ajax_url()
			) ) );
		}

		if ( wp_script_is( 'propertyhive_account' ) ) {
			wp_localize_script( 'propertyhive_account', 'propertyhive_account_params', apply_filters( 'propertyhive_account_params', array(
				'ajax_url'        		=> PH()->ajax_url(),
				'my_account_url'  		=> get_permalink( ph_get_page_id('my_account') ),
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
