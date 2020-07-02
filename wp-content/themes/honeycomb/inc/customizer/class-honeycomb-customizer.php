<?php
/**
 * Honeycomb Customizer Class
 *
 * @author   PropertyHive
 * @package  honeycomb
 * @since    2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Honeycomb_Customizer' ) ) :

	/**
	 * The Honeycomb Customizer class
	 */
	class Honeycomb_Customizer {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'customize_register',              array( $this, 'customize_register' ), 10 );
			add_filter( 'body_class',                      array( $this, 'layout_class' ) );
			add_action( 'wp_enqueue_scripts',              array( $this, 'add_customizer_css' ), 130 );
			add_action( 'after_setup_theme',               array( $this, 'custom_header_setup' ) );
			add_action( 'customize_controls_print_styles', array( $this, 'customizer_custom_control_css' ) );
			add_action( 'customize_register',              array( $this, 'edit_default_customizer_settings' ), 99 );
			add_action( 'init',                            array( $this, 'default_theme_mod_values' ), 10 );

			add_action( 'after_switch_theme',              array( $this, 'set_honeycomb_style_theme_mods' ) );
			add_action( 'customize_save_after',            array( $this, 'set_honeycomb_style_theme_mods' ) );
		}

		/**
		 * Returns an array of the desired default Honeycomb Options
		 *
		 * @return array
		 */
		public static function get_honeycomb_default_setting_values() {
			return apply_filters( 'honeycomb_setting_default_values', $args = array(
				'honeycomb_heading_color'               => '#484c51',
				'honeycomb_text_color'                  => '#43454b',
				'honeycomb_accent_color'                => '#f6700c',
				'honeycomb_header_background_color'     => '#2c2d33',
				'honeycomb_header_text_color'           => '#9aa0a7',
				'honeycomb_header_link_color'           => '#d5d9db',
				'honeycomb_footer_background_color'     => '#f0f0f0',
				'honeycomb_footer_heading_color'        => '#494c50',
				'honeycomb_footer_text_color'           => '#61656b',
				'honeycomb_footer_link_color'           => '#2c2d33',
				'honeycomb_button_background_color'     => '#f6700c',
				'honeycomb_button_text_color'           => '#ffffff',
				'honeycomb_button_alt_background_color' => '#2c2d33',
				'honeycomb_button_alt_text_color'       => '#ffffff',
				'honeycomb_layout'                      => 'right',
				'background_color'                       => '#ffffff',
			) );
		}

		/**
		 * Adds a value to each Honeycomb setting if one isn't already present.
		 *
		 * @uses get_honeycomb_default_setting_values()
		 */
		public function default_theme_mod_values() {
			foreach ( self::get_honeycomb_default_setting_values() as $mod => $val ) {
				add_filter( 'theme_mod_' . $mod, array( $this, 'get_theme_mod_value' ), 10 );
			}
		}

		/**
		 * Get theme mod value.
		 *
		 * @param string $value
		 * @return string
		 */
		public function get_theme_mod_value( $value ) {
			$key = substr( current_filter(), 10 );

			$set_theme_mods = get_theme_mods();

			if ( isset( $set_theme_mods[ $key ] ) ) {
				return $value;
			}

			$values = $this->get_honeycomb_default_setting_values();

			return isset( $values[ $key ] ) ? $values[ $key ] : $value;
		}

		/**
		 * Set Customizer setting defaults.
		 * These defaults need to be applied separately as child themes can filter honeycomb_setting_default_values
		 *
		 * @param  array $wp_customize the Customizer object.
		 * @uses   get_honeycomb_default_setting_values()
		 */
		public function edit_default_customizer_settings( $wp_customize ) {
			foreach ( self::get_honeycomb_default_setting_values() as $mod => $val ) {
				$wp_customize->get_setting( $mod )->default = $val;
			}
		}

		/**
		 * Setup the WordPress core custom header feature.
		 *
		 * @uses honeycomb_header_style()
		 * @uses honeycomb_admin_header_style()
		 * @uses honeycomb_admin_header_image()
		 */
		public function custom_header_setup() {
			add_theme_support( 'custom-header', apply_filters( 'honeycomb_custom_header_args', array(
				'default-image' => '',
				'header-text'   => false,
				'width'         => 2000,
				'height'        => 200,
				'flex-width'    => true,
				'flex-height'   => true,
			) ) );
		}

		/**
		 * Add postMessage support for site title and description for the Theme Customizer along with several other settings.
		 *
		 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
		 * @since  1.0.0
		 */
		public function customize_register( $wp_customize ) {

			// Move background color setting alongside background image.
			$wp_customize->get_control( 'background_color' )->section   = 'background_image';
			$wp_customize->get_control( 'background_color' )->priority  = 20;

			// Change background image section title & priority.
			$wp_customize->get_section( 'background_image' )->title     = __( 'Background', 'honeycomb' );
			$wp_customize->get_section( 'background_image' )->priority  = 30;

			// Change header image section title & priority.
			$wp_customize->get_section( 'header_image' )->title         = __( 'Header', 'honeycomb' );
			$wp_customize->get_section( 'header_image' )->priority      = 25;

			// Selective refresh.
			if ( function_exists( 'add_partial' ) ) {
				$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
				$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

				$wp_customize->selective_refresh->add_partial( 'custom_logo', array(
					'selector'        => '.site-branding',
					'render_callback' => array( $this, 'get_site_logo' ),
				) );

				$wp_customize->selective_refresh->add_partial( 'blogname', array(
					'selector'        => '.site-title.beta a',
					'render_callback' => array( $this, 'get_site_name' ),
				) );

				$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
					'selector'        => '.site-description',
					'render_callback' => array( $this, 'get_site_description' ),
				) );
			}

			/**
			 * Custom controls
			 */
			require_once dirname( __FILE__ ) . '/class-honeycomb-customizer-control-radio-image.php';
			require_once dirname( __FILE__ ) . '/class-honeycomb-customizer-control-arbitrary.php';

			/**
			 * Add the typography section
			 */
			$wp_customize->add_section( 'honeycomb_typography' , array(
				'title'      			=> __( 'Typography', 'honeycomb' ),
				'priority'   			=> 45,
			) );

			/**
			 * Heading color
			 */
			$wp_customize->add_setting( 'honeycomb_heading_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_heading_color', '#484c51' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_heading_color', array(
				'label'	   				=> __( 'Heading color', 'honeycomb' ),
				'section'  				=> 'honeycomb_typography',
				'settings' 				=> 'honeycomb_heading_color',
				'priority' 				=> 20,
			) ) );

			/**
			 * Text Color
			 */
			$wp_customize->add_setting( 'honeycomb_text_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_text_color', '#43454b' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_text_color', array(
				'label'					=> __( 'Text color', 'honeycomb' ),
				'section'				=> 'honeycomb_typography',
				'settings'				=> 'honeycomb_text_color',
				'priority'				=> 30,
			) ) );

			/**
			 * Accent Color
			 */
			$wp_customize->add_setting( 'honeycomb_accent_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_accent_color', '#96588a' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_accent_color', array(
				'label'	   				=> __( 'Link / accent color', 'honeycomb' ),
				'section'  				=> 'honeycomb_typography',
				'settings' 				=> 'honeycomb_accent_color',
				'priority' 				=> 40,
			) ) );

			$wp_customize->add_control( new Arbitrary_Honeycomb_Control( $wp_customize, 'honeycomb_header_image_heading', array(
				'section'  				=> 'header_image',
				'type' 					=> 'heading',
				'label'					=> __( 'Header background image', 'honeycomb' ),
				'priority' 				=> 6,
			) ) );

			/**
			 * Header Background
			 */
			$wp_customize->add_setting( 'honeycomb_header_background_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_header_background_color', '#2c2d33' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_header_background_color', array(
				'label'	   				=> __( 'Background color', 'honeycomb' ),
				'section'  				=> 'header_image',
				'settings' 				=> 'honeycomb_header_background_color',
				'priority' 				=> 15,
			) ) );

			/**
			 * Header text color
			 */
			$wp_customize->add_setting( 'honeycomb_header_text_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_header_text_color', '#9aa0a7' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_header_text_color', array(
				'label'	   				=> __( 'Text color', 'honeycomb' ),
				'section'  				=> 'header_image',
				'settings' 				=> 'honeycomb_header_text_color',
				'priority' 				=> 20,
			) ) );

			/**
			 * Header link color
			 */
			$wp_customize->add_setting( 'honeycomb_header_link_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_header_link_color', '#d5d9db' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_header_link_color', array(
				'label'	   				=> __( 'Link color', 'honeycomb' ),
				'section'  				=> 'header_image',
				'settings' 				=> 'honeycomb_header_link_color',
				'priority' 				=> 30,
			) ) );

			/**
			 * Footer section
			 */
			$wp_customize->add_section( 'honeycomb_footer' , array(
				'title'      			=> __( 'Footer', 'honeycomb' ),
				'priority'   			=> 28,
				'description' 			=> __( 'Customise the look & feel of your web site footer.', 'honeycomb' ),
			) );

			/**
			 * Footer Background
			 */
			$wp_customize->add_setting( 'honeycomb_footer_background_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_footer_background_color', '#f0f0f0' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_footer_background_color', array(
				'label'	   				=> __( 'Background color', 'honeycomb' ),
				'section'  				=> 'honeycomb_footer',
				'settings' 				=> 'honeycomb_footer_background_color',
				'priority'				=> 10,
			) ) );

			/**
			 * Footer heading color
			 */
			$wp_customize->add_setting( 'honeycomb_footer_heading_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_footer_heading_color', '#494c50' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_footer_heading_color', array(
				'label'	   				=> __( 'Heading color', 'honeycomb' ),
				'section'  				=> 'honeycomb_footer',
				'settings' 				=> 'honeycomb_footer_heading_color',
				'priority'				=> 20,
			) ) );

			/**
			 * Footer text color
			 */
			$wp_customize->add_setting( 'honeycomb_footer_text_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_footer_text_color', '#61656b' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_footer_text_color', array(
				'label'	   				=> __( 'Text color', 'honeycomb' ),
				'section'  				=> 'honeycomb_footer',
				'settings' 				=> 'honeycomb_footer_text_color',
				'priority'				=> 30,
			) ) );

			/**
			 * Footer link color
			 */
			$wp_customize->add_setting( 'honeycomb_footer_link_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_footer_link_color', '#2c2d33' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_footer_link_color', array(
				'label'	   				=> __( 'Link color', 'honeycomb' ),
				'section'  				=> 'honeycomb_footer',
				'settings' 				=> 'honeycomb_footer_link_color',
				'priority'				=> 40,
			) ) );

			/**
			 * Buttons section
			 */
			$wp_customize->add_section( 'honeycomb_buttons' , array(
				'title'      			=> __( 'Buttons', 'honeycomb' ),
				'priority'   			=> 45,
				'description' 			=> __( 'Customise the look & feel of your web site buttons.', 'honeycomb' ),
			) );

			/**
			 * Button background color
			 */
			$wp_customize->add_setting( 'honeycomb_button_background_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_button_background_color', '#96588a' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_button_background_color', array(
				'label'	   				=> __( 'Background color', 'honeycomb' ),
				'section'  				=> 'honeycomb_buttons',
				'settings' 				=> 'honeycomb_button_background_color',
				'priority' 				=> 10,
			) ) );

			/**
			 * Button text color
			 */
			$wp_customize->add_setting( 'honeycomb_button_text_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_button_text_color', '#ffffff' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_button_text_color', array(
				'label'	   				=> __( 'Text color', 'honeycomb' ),
				'section'  				=> 'honeycomb_buttons',
				'settings' 				=> 'honeycomb_button_text_color',
				'priority' 				=> 20,
			) ) );

			/**
			 * Button alt background color
			 */
			$wp_customize->add_setting( 'honeycomb_button_alt_background_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_button_alt_background_color', '#2c2d33' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_button_alt_background_color', array(
				'label'	   				=> __( 'Alternate button background color', 'honeycomb' ),
				'section'  				=> 'honeycomb_buttons',
				'settings' 				=> 'honeycomb_button_alt_background_color',
				'priority' 				=> 30,
			) ) );

			/**
			 * Button alt text color
			 */
			$wp_customize->add_setting( 'honeycomb_button_alt_text_color', array(
				'default'           	=> apply_filters( 'honeycomb_default_button_alt_text_color', '#ffffff' ),
				'sanitize_callback' 	=> 'sanitize_hex_color',
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'honeycomb_button_alt_text_color', array(
				'label'	   				=> __( 'Alternate button text color', 'honeycomb' ),
				'section'  				=> 'honeycomb_buttons',
				'settings' 				=> 'honeycomb_button_alt_text_color',
				'priority' 				=> 40,
			) ) );

			/**
			 * Layout
			 */
			$wp_customize->add_section( 'honeycomb_layout' , array(
				'title'      			=> __( 'Layout', 'honeycomb' ),
				'priority'   			=> 50,
			) );

			$wp_customize->add_setting( 'honeycomb_layout', array(
				'default'    			=> apply_filters( 'honeycomb_default_layout', $layout = is_rtl() ? 'left' : 'right' ),
				'sanitize_callback' 	=> 'honeycomb_sanitize_choices',
			) );

			$wp_customize->add_control( new Honeycomb_Custom_Radio_Image_Control( $wp_customize, 'honeycomb_layout', array(
				'settings'				=> 'honeycomb_layout',
				'section'				=> 'honeycomb_layout',
				'label'					=> __( 'General Layout', 'honeycomb' ),
				'priority'				=> 1,
				'choices'				=> array(
											'right' => get_template_directory_uri() . '/assets/images/customizer/controls/2cr.png',
											'left'  => get_template_directory_uri() . '/assets/images/customizer/controls/2cl.png',
				),
			) ) );
		}

		/**
		 * Get all of the Honeycomb theme mods.
		 *
		 * @return array $honeycomb_theme_mods The Honeycomb Theme Mods.
		 */
		public function get_honeycomb_theme_mods() {
			$honeycomb_theme_mods = array(
				'background_color'            => honeycomb_get_content_background_color(),
				'accent_color'                => get_theme_mod( 'honeycomb_accent_color' ),
				'header_background_color'     => get_theme_mod( 'honeycomb_header_background_color' ),
				'header_link_color'           => get_theme_mod( 'honeycomb_header_link_color' ),
				'header_text_color'           => get_theme_mod( 'honeycomb_header_text_color' ),
				'footer_background_color'     => get_theme_mod( 'honeycomb_footer_background_color' ),
				'footer_link_color'           => get_theme_mod( 'honeycomb_footer_link_color' ),
				'footer_heading_color'        => get_theme_mod( 'honeycomb_footer_heading_color' ),
				'footer_text_color'           => get_theme_mod( 'honeycomb_footer_text_color' ),
				'text_color'                  => get_theme_mod( 'honeycomb_text_color' ),
				'heading_color'               => get_theme_mod( 'honeycomb_heading_color' ),
				'button_background_color'     => get_theme_mod( 'honeycomb_button_background_color' ),
				'button_text_color'           => get_theme_mod( 'honeycomb_button_text_color' ),
				'button_alt_background_color' => get_theme_mod( 'honeycomb_button_alt_background_color' ),
				'button_alt_text_color'       => get_theme_mod( 'honeycomb_button_alt_text_color' ),
			);

			return apply_filters( 'honeycomb_theme_mods', $honeycomb_theme_mods );
		}

		/**
		 * Get Customizer css.
		 *
		 * @see get_honeycomb_theme_mods()
		 * @return array $styles the css
		 */
		public function get_css() {
			$honeycomb_theme_mods = $this->get_honeycomb_theme_mods();
			$brighten_factor       = apply_filters( 'honeycomb_brighten_factor', 25 );
			$darken_factor         = apply_filters( 'honeycomb_darken_factor', -25 );

			$styles                = '
			.main-navigation ul li a,
			.site-title a,
			ul.menu li a,
			.site-branding h1 a,
			.site-footer .honeycomb-handheld-footer-bar a:not(.button),
			button.menu-toggle,
			button.menu-toggle:hover {
				color: ' . $honeycomb_theme_mods['header_link_color'] . ';
			}

			button.menu-toggle,
			button.menu-toggle:hover {
				border-color: ' . $honeycomb_theme_mods['header_link_color'] . ';
			}

			.main-navigation ul li a:hover,
			.main-navigation ul li:hover > a,
			.site-title a:hover,
			a.cart-contents:hover,
			.site-header-cart .widget_shopping_cart a:hover,
			.site-header-cart:hover > li > a,
			.site-header ul.menu li.current-menu-item > a {
				color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['header_link_color'], 50 ) . ';
			}

			table th {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -7 ) . ';
			}

			table tbody td {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -2 ) . ';
			}

			table tbody tr:nth-child(2n) td {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -4 ) . ';
			}

			.site-header,
			.secondary-navigation ul ul,
			.main-navigation ul.menu > li.menu-item-has-children:after,
			.secondary-navigation ul.menu ul,
			.honeycomb-handheld-footer-bar,
			.honeycomb-handheld-footer-bar ul li > a,
			.honeycomb-handheld-footer-bar ul li.search .site-search,
			button.menu-toggle,
			button.menu-toggle:hover {
				background-color: ' . $honeycomb_theme_mods['header_background_color'] . ';
			}

			p.site-description,
			.site-header,
			.honeycomb-handheld-footer-bar {
				color: ' . $honeycomb_theme_mods['header_text_color'] . ';
			}

			.honeycomb-handheld-footer-bar ul li.cart .count,
			button.menu-toggle:after,
			button.menu-toggle:before,
			button.menu-toggle span:before {
				background-color: ' . $honeycomb_theme_mods['header_link_color'] . ';
			}

			.honeycomb-handheld-footer-bar ul li.cart .count {
				color: ' . $honeycomb_theme_mods['header_background_color'] . ';
			}

			.honeycomb-handheld-footer-bar ul li.cart .count {
				border-color: ' . $honeycomb_theme_mods['header_background_color'] . ';
			}

			h1, h2, h3, h4, h5, h6 {
				color: ' . $honeycomb_theme_mods['heading_color'] . ';
			}

			.widget h1 {
				border-bottom-color: ' . $honeycomb_theme_mods['heading_color'] . ';
			}

			body,
			.secondary-navigation a,
			.onsale,
			.pagination .page-numbers li .page-numbers:not(.current), .propertyhive-pagination .page-numbers li .page-numbers:not(.current) {
				color: ' . $honeycomb_theme_mods['text_color'] . ';
			}

			.widget-area .widget a,
			.hentry .entry-header .posted-on a,
			.hentry .entry-header .byline a {
				color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['text_color'], 50 ) . ';
			}

			a  {
				color: ' . $honeycomb_theme_mods['accent_color'] . ';
			}

			.propertyhive-my-account .my-account-navigation ul li.active a {
				background: ' . $honeycomb_theme_mods['accent_color'] . ';
			}

			a:focus,
			.button:focus,
			.button.alt:focus,
			button:focus,
			input[type="button"]:focus,
			input[type="reset"]:focus,
			input[type="submit"]:focus {
				outline-color: ' . $honeycomb_theme_mods['accent_color'] . ';
			}

			button, input[type="button"], input[type="reset"], input[type="submit"], .button, .widget a.button, .property_actions li a {
				background-color: ' . $honeycomb_theme_mods['button_background_color'] . ';
				border-color: ' . $honeycomb_theme_mods['button_background_color'] . ';
				color: ' . $honeycomb_theme_mods['button_text_color'] . ';
			}

			button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .button:hover, .widget a.button:hover {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['button_background_color'], $darken_factor ) . ';
				border-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['button_background_color'], $darken_factor ) . ';
				color: ' . $honeycomb_theme_mods['button_text_color'] . ';
			}

			button.alt, input[type="button"].alt, input[type="reset"].alt, input[type="submit"].alt, .button.alt, .widget-area .widget a.button.alt, .pagination .page-numbers li .page-numbers.current {
				background-color: ' . $honeycomb_theme_mods['button_alt_background_color'] . ';
				border-color: ' . $honeycomb_theme_mods['button_alt_background_color'] . ';
				color: ' . $honeycomb_theme_mods['button_alt_text_color'] . ';
			}

			button.alt:hover, input[type="button"].alt:hover, input[type="reset"].alt:hover, input[type="submit"].alt:hover, .button.alt:hover, .widget-area .widget a.button.alt:hover {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['button_alt_background_color'], $darken_factor ) . ';
				border-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['button_alt_background_color'], $darken_factor ) . ';
				color: ' . $honeycomb_theme_mods['button_alt_text_color'] . ';
			}

			#comments .comment-list .comment-content .comment-text {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -7 ) . ';
			}

			.site-footer {
				background-color: ' . $honeycomb_theme_mods['footer_background_color'] . ';
				color: ' . $honeycomb_theme_mods['footer_text_color'] . ';
			}

			.site-footer a:not(.button) {
				color: ' . $honeycomb_theme_mods['footer_link_color'] . ';
			}

			.site-footer h1, .site-footer h2, .site-footer h3, .site-footer h4, .site-footer h5, .site-footer h6 {
				color: ' . $honeycomb_theme_mods['footer_heading_color'] . ';
			}

			#order_review,
			#payment .payment_methods > li .payment_box {
				background-color: ' . $honeycomb_theme_mods['background_color'] . ';
			}

			#payment .payment_methods > li {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -5 ) . ';
			}

			#payment .payment_methods > li:hover {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -10 ) . ';
			}

			@media screen and ( min-width: 768px ) {
				.secondary-navigation ul.menu a:hover {
					color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['header_text_color'], $brighten_factor ) . ';
				}

				.secondary-navigation ul.menu a {
					color: ' . $honeycomb_theme_mods['header_text_color'] . ';
				}

				.site-header-cart .widget_shopping_cart,
				.main-navigation ul.menu ul.sub-menu,
				.main-navigation ul.nav-menu ul.children {
					background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['header_background_color'], -8 ) . ';
				}
			}';

			return apply_filters( 'honeycomb_customizer_css', $styles );
		}

		/**
		 * Get Customizer css associated with Property Hive.
		 *
		 * @see get_honeycomb_theme_mods()
		 * @return array $propertyhive_styles the Property Hive css
		 */
		public function get_propertyhive_css() {
			$honeycomb_theme_mods = $this->get_honeycomb_theme_mods();
			$brighten_factor       = apply_filters( 'honeycomb_brighten_factor', 25 );
			$darken_factor         = apply_filters( 'honeycomb_darken_factor', -25 );

			$propertyhive_styles    = '
			a.cart-contents,
			.site-header-cart .widget_shopping_cart a {
				color: ' . $honeycomb_theme_mods['header_link_color'] . ';
			}

			ul.properties li.property .price,
			.widget_search form:before {
				color: ' . $honeycomb_theme_mods['text_color'] . ';
			}

			.propertyhive-breadcrumb a,
			.property_meta a {
				color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['text_color'], 50 ) . ';
			}

			.widget_price_filter .ui-slider .ui-slider-range,
			.widget_price_filter .ui-slider .ui-slider-handle {
				background-color: ' . $honeycomb_theme_mods['accent_color'] . ';
			}

			.propertyhive-breadcrumb {
				background-color: ' . honeycomb_adjust_color_brightness( $honeycomb_theme_mods['background_color'], -7 ) . ';
			}';

			return apply_filters( 'honeycomb_customizer_propertyhive_css', $propertyhive_styles );
		}

		/**
		 * Assign Honeycomb styles to individual theme mods.
		 *
		 * @return void
		 */
		public function set_honeycomb_style_theme_mods() {
			set_theme_mod( 'honeycomb_styles', $this->get_css() );
			set_theme_mod( 'honeycomb_propertyhive_styles', $this->get_propertyhive_css() );
		}

		/**
		 * Add CSS in <head> for styles handled by the theme customizer
		 * If the Customizer is active pull in the raw css. Otherwise pull in the prepared theme_mods if they exist.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function add_customizer_css() {
			$honeycomb_styles             = get_theme_mod( 'honeycomb_styles' );
			$honeycomb_propertyhive_styles = get_theme_mod( 'honeycomb_propertyhive_styles' );

			if ( is_customize_preview() || ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) || ( false === $honeycomb_styles && false === $honeycomb_propertyhive_styles ) ) {
				wp_add_inline_style( 'honeycomb-style', $this->get_css() );
				wp_add_inline_style( 'honeycomb-propertyhive-style', $this->get_propertyhive_css() );
			} else {
				wp_add_inline_style( 'honeycomb-style', get_theme_mod( 'honeycomb_styles' ) );
				wp_add_inline_style( 'honeycomb-propertyhive-style', get_theme_mod( 'honeycomb_propertyhive_styles' ) );
			}
		}

		/**
		 * Layout classes
		 * Adds 'right-sidebar' and 'left-sidebar' classes to the body tag
		 *
		 * @param  array $classes current body classes.
		 * @return string[]          modified body classes
		 * @since  1.0.0
		 */
		public function layout_class( $classes ) {
			$left_or_right = get_theme_mod( 'honeycomb_layout' );

			$classes[] = $left_or_right . '-sidebar';

			return $classes;
		}

		/**
		 * Add CSS for custom controls
		 *
		 * This function incorporates CSS from the Kirki Customizer Framework
		 *
		 * The Kirki Customizer Framework, Copyright Aristeides Stathopoulos (@aristath),
		 * is licensed under the terms of the GNU GPL, Version 2 (or later)
		 *
		 * @link https://github.com/reduxframework/kirki/
		 * @since  1.5.0
		 */
		public function customizer_custom_control_css() {
			?>
			<style>
			.customize-control-radio-image .image.ui-buttonset input[type=radio] {
				height: auto;
			}

			.customize-control-radio-image .image.ui-buttonset label {
				display: inline-block;
				width: 48%;
				padding: 1%;
				box-sizing: border-box;
			}

			.customize-control-radio-image .image.ui-buttonset label.ui-state-active {
				background: none;
			}

			.customize-control-radio-image .customize-control-radio-buttonset label {
				background: #f7f7f7;
				line-height: 35px;
			}

			.customize-control-radio-image label img {
				opacity: 0.5;
			}

			#customize-controls .customize-control-radio-image label img {
				height: auto;
			}

			.customize-control-radio-image label.ui-state-active img {
				background: #dedede;
				opacity: 1;
			}

			.customize-control-radio-image label.ui-state-hover img {
				opacity: 1;
				box-shadow: 0 0 0 3px #f6f6f6;
			}
			</style>
			<?php
		}

		/**
		 * Get site logo.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_site_logo() {
			return honeycomb_site_title_or_logo( false );
		}

		/**
		 * Get site name.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_site_name() {
			return get_bloginfo( 'name', 'display' );
		}

		/**
		 * Get site description.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_site_description() {
			return get_bloginfo( 'description', 'display' );
		}
	}

endif;

return new Honeycomb_Customizer();
