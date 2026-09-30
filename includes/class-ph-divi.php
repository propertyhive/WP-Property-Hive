<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Divi {

	public function __construct()
	{
		$dependency_interface = ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

	    if ( file_exists( $dependency_interface ) ) 
	    {
	        include_once( 'divi-5/includes/modules/Modules.php' );
	    }
        
        add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( $this, 'propertyhive_enqueue_divi5_vb_assets' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'propertyhive_enqueue_divi5_frontend_assets' ) );

        add_action( 'admin_notices', array( $this, 'propertyhive_divi5_migration_notice' ) );

	    add_action( 'et_builder_ready', array( $this, 'register_widgets' ) );
	}

	/**
	 * Determine whether any current content still contains a Property Hive
	 * Divi 4 module shortcode.
	 *
	 * @return bool
	 */
	private function has_legacy_divi_modules()
	{
		global $wpdb;

		$shortcode_prefix = '%' . $wpdb->esc_like( '[et_pb_property_' ) . '%';
		$post_types = array( 'page', 'post', 'project', 'et_pb_layout' );

		foreach (
			array(
				'ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE',
				'ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE',
				'ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE',
			) as $post_type_constant
		) {
			if ( defined( $post_type_constant ) ) {
				$post_types[] = constant( $post_type_constant );
			}
		}

		if ( function_exists( 'et_builder_get_enabled_builder_post_types' ) ) {
			$post_types = array_merge( $post_types, et_builder_get_enabled_builder_post_types() );
		}

		$post_types        = array_values( array_unique( array_filter( $post_types ) ) );
		$post_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
		$query_parameters  = array_merge( array( $shortcode_prefix ), $post_types );
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_content LIKE %s
				AND post_status = 'publish'
				AND post_type IN ( {$post_placeholders} )
				LIMIT 1",
				$query_parameters
			)
		);

		$has_legacy_modules = ! empty( $post_id );

		return $has_legacy_modules;
	}

	/**
	 * Prompt administrators to migrate stored Property Hive Divi 4 modules.
	 */
	public function propertyhive_divi5_migration_notice()
	{
		$migration_complete = function_exists( 'et_get_option' )
			&& et_get_option( 'et_d5_readiness_conversion_finished', false );

		if (
			! current_user_can( 'manage_options' )
			|| ! function_exists( 'et_builder_d5_enabled' )
			|| ! et_builder_d5_enabled()
			|| $migration_complete
			|| ! $this->has_legacy_divi_modules()
		) {
			return;
		}

		$migration_url = add_query_arg(
			'page',
			'et_d5_readiness',
			admin_url( 'admin.php' )
		);
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Property Hive: Divi 5 migration required', 'propertyhive' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'Property Hive has detected legacy Divi 4 modules in your layouts. These modules must be migrated before they will display correctly with Divi 5.', 'propertyhive' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $migration_url ); ?>">
					<?php esc_html_e( 'Review Divi 5 Migration', 'propertyhive' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public function propertyhive_enqueue_divi5_vb_assets() 
	{
	    if (
	        ! function_exists( 'et_builder_d5_enabled' )
	        || ! et_builder_d5_enabled()
	        || ! function_exists( 'et_core_is_fb_enabled' )
	        || ! et_core_is_fb_enabled()
	    ) {
	        return;
	    }

	    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
	        [
	            'name'    => 'propertyhive-divi5-builder-script',
	            'version' => defined( 'PH_VERSION' ) ? PH_VERSION : '1.0.0',
	            'script'  => [
	                'src'                => PH()->plugin_url() . '/includes/divi-5/includes/scripts/bundle.js',
	                'deps'               => [
	                    'react',
	                    'jquery',
	                    'divi-module-library',
	                    'wp-hooks',
	                    'wp-data',
	                    'divi-rest',
	                ],
	                'enqueue_top_window' => false,
	                'enqueue_app_window' => true,
	            ],
	        ]
	    );
	}

	public function propertyhive_enqueue_divi5_frontend_assets() 
	{
		if ( !function_exists( 'et_builder_d5_enabled' ) || !et_builder_d5_enabled() ) 
        {
        	return;
        }

	    wp_enqueue_style(
	        'propertyhive-divi5-frontend',
	        PH()->plugin_url() . '/includes/divi-5/includes/styles/bundle.css',
	        [],
	        defined( 'PH_VERSION' ) ? PH_VERSION : '1.0.0'
	    );
	}

	public function register_widgets()
	{
		if (
	        function_exists( 'et_builder_d5_enabled' )
	        && et_builder_d5_enabled()
	    ) {
	        return;
	    }

		if ( class_exists('ET_Builder_Module') ) 
		{
			$widgets = array(
				'Property Price',
				'Property Images',
				'Property Image',
				'Property Gallery',
				'Property Address Name Number',
				'Property Address Street',
				'Property Address Line 2',
				'Property Address Town City',
				'Property Address County',
				'Property Address Postcode',
				'Property Address Full',
				'Property Features',
				'Property Summary Description',
				'Property Full Description',
				'Property Actions',
				'Property Meta',
				'Property Availability',
				'Property Type',
				'Property Bedrooms',
				'Property Bathrooms',
				'Property Reception Rooms',
				'Property Reference Number',
				'Property Floor Area',
				'Property Tenure',
				'Property Council Tax Band',
				'Property Let Available Date',
				'Property Map',
				'Property Map Link',
				'Property Street View',
				'Property Floorplans',
				'Property Floorplans Link',
				'Property EPCs',
				'Property EPCs Link',
				'Property Enquiry Form',
				'Property Enquiry Form Link',
				'Property Brochures Link',
				'Property Embedded Virtual Tours',
				'Property Virtual Tours Link',
				'Property Office Name',
				'Property Office Telephone Number',
				'Property Office Email Address',
				'Property Office Address',
				'Property Negotiator Name',
				'Property Negotiator Telephone Number',
				'Property Negotiator Email Address',
				'Property Negotiator Photo',
			);

			$widgets = apply_filters( 'propertyhive_divi_widgets', $widgets );

			foreach ( $widgets as $widget )
			{
				$widget_dir = 'divi-widgets';
				$widget_dir = apply_filters( 'propertyhive_divi_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
				if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
				{
					require_once( $widget_dir . "/" . sanitize_title($widget) . ".php" );
					$class_name = 'Divi_' . str_replace(" ", "_", $widget) . '_Widget';
					new $class_name();
				}
			}
		}
	}
}

new PH_Divi();
