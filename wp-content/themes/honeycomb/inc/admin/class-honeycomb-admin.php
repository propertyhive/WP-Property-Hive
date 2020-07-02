<?php
/**
 * Honeycomb Admin Class
 *
 * @author   Property Hive
 * @package  honeycomb
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Honeycomb_Admin' ) ) :
	/**
	 * The Honeycomb admin class
	 */
	class Honeycomb_Admin {

		/**
		 * Setup class.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
			add_action( 'admin_notices', array( $this, 'honeycomb_error_notices') );

			add_action( 'add_meta_boxes', array( $this, 'honeycomb_register_page_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'honeycomb_save_meta_boxes' ), 1, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		}

		/**
	     * Output error message if core Property Hive plugin isn't active
	     */
	    public function honeycomb_error_notices() 
	    {
	        if (!is_plugin_active('propertyhive/propertyhive.php'))
	        {
	            $message = __( "The Property Hive plugin must be installed and activated before you can use the Honeycomb theme", 'propertyhive' );
	            echo"<div class=\"error\"> <p>$message</p></div>";
	        }
	    }

		public function admin_styles()
		{
			if (is_plugin_active('propertyhive/propertyhive.php'))
       		{
				$screen = get_current_screen();

				if ( $screen->id == 'page' )
				{
					// Admin styles for PH pages only
		            wp_enqueue_style( 'propertyhive_admin_styles', PH()->plugin_url() . '/assets/css/admin.css', array(), PH_VERSION );
		        }
		    }
		}

		public function honeycomb_register_page_meta_boxes()
		{
			if (is_plugin_active('propertyhive/propertyhive.php'))
       		{
				add_meta_box( 'honeycomb-page-banner', __( 'Page Banner', 'honeycomb' ), array( $this, 'honeycomb_display_page_banner_meta_box' ), 'page' );
			}
		}

		public function honeycomb_display_page_banner_meta_box()
		{
			wp_nonce_field( 'propertyhive_save_page_data', 'propertyhive_meta_nonce' );
        
	        echo '<div class="propertyhive_meta_box">';
	        
	        echo '<div class="options_group">';

			$args = array( 
	            'id' => '_banner_type', 
	            'label' => __( 'Banner Type', 'propertyhive' ), 
	            'desc_tip' => false,
	            'options' => array(
	            	'' => __( 'No Banner', 'honeycomb' ),
	            	'map' => __( 'Map', 'honeycomb' ) . ' - ' . __( 'Requires the Property Hive Map Search add on', 'honeycomb' ),
	            	'revslider' => __( 'Revolution Slider', 'honeycomb' ) . ' - ' . __( 'Requires the Revolution Slider plugin', 'honeycomb' ),
	            	'featured' => __( 'Use Featured Image', 'honeycomb' ),
	            )
	        );

	        propertyhive_wp_radio( $args );

	        if ( class_exists( 'RevSlider' ) ) 
			{
				$args = array( 
		            'id' => '_banner_rev_slider', 
		            'label' => __( 'Revolution Slider Name', 'honeycomb' ), 
		            'desc_tip' => false,
		            'description' => __( 'Applicable if \'Banner Type\' is \'Revolution Slider\'', 'honeycomb' ),
		            'type' => 'text'
		        );
		        propertyhive_wp_text_input( $args );
			}

	        echo '</div>';

	        echo '</div>';
		}


		public function honeycomb_save_meta_boxes( $post_id, $post )
		{
			if (is_plugin_active('propertyhive/propertyhive.php'))
       		{
				// $post_id and $post are required
				if ( empty( $post_id ) || empty( $post ) ) {
					return;
				}

				// Dont' save meta boxes for revisions or autosaves
				if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
					return;
				}

				// Check the nonce
				if ( empty( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( $_POST['propertyhive_meta_nonce'], 'propertyhive_save_page_data' ) ) {
					return;
				}
		        
				// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
				if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
					return;
				}

				// Check user has permission to edit
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

				update_post_meta( $post_id, '_banner_type', $_POST['_banner_type'] );

				if ( class_exists( 'RevSlider' ) ) 
				{
					update_post_meta( $post_id, '_banner_rev_slider', $_POST['_banner_rev_slider'] );
				}
			}
		}

	}

endif;

return new Honeycomb_Admin();
