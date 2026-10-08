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

        add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', array( $this, 'propertyhive_add_divi5_icon_assets' ), 10, 3 );
        add_filter( 'divi_frontend_assets_dynamic_assets_late_global_assets_list', array( $this, 'propertyhive_add_divi5_icon_assets' ), 10, 3 );

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

		$builder_script_url = add_query_arg(
			'propertyhive_divi5_fa_base',
			$this->propertyhive_divi5_fontawesome_base_url(),
			PH()->plugin_url() . '/includes/divi-5/includes/scripts/bundle.js'
		);
		$builder_script_path = dirname( __FILE__ ) . '/divi-5/includes/scripts/bundle.js';
		$builder_script_version = defined( 'PH_VERSION' ) ? PH_VERSION : '1.0.0';

		if ( file_exists( $builder_script_path ) ) {
			$builder_script_version .= '.' . filemtime( $builder_script_path );
		}

	    \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
	        [
	            'name'    => 'propertyhive-divi5-builder-script',
	            'version' => $builder_script_version,
	            'script'  => [
	                'src'                => $builder_script_url,
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

		// Divi's dynamic-assets detector does not reliably enqueue its Font
		// Awesome stylesheet for third-party modules. Add the font faces to the
		// Property Hive stylesheet so icon glyphs always have a matching font.
		wp_add_inline_style(
			'propertyhive-divi5-frontend',
			$this->propertyhive_divi5_fontawesome_css()
		);
	}

	/**
	 * Get the URL of the Font Awesome files supplied by Divi.
	 *
	 * @return string
	 */
	private function propertyhive_divi5_fontawesome_base_url()
	{
		if ( defined( 'ET_BUILDER_PLUGIN_URI' ) ) {
			$divi_base_url = ET_BUILDER_PLUGIN_URI;
		} else {
			$divi_base_url = get_template_directory_uri();
		}

		return trailingslashit( $divi_base_url ) . 'core/admin/fonts/fontawesome';
	}

	/**
	 * Build the Font Awesome font-face declarations required by Divi icons.
	 *
	 * @return string
	 */
	private function propertyhive_divi5_fontawesome_css()
	{
		$font_base_url = trailingslashit( $this->propertyhive_divi5_fontawesome_base_url() );

		return sprintf(
			'@font-face{font-family:"FontAwesome";font-style:normal;font-weight:400;font-display:block;src:url("%1$sfa-regular-400.woff2") format("woff2"),url("%1$sfa-regular-400.woff") format("woff")}@font-face{font-family:"FontAwesome";font-style:normal;font-weight:900;font-display:block;src:url("%1$sfa-solid-900.woff2") format("woff2"),url("%1$sfa-solid-900.woff") format("woff")}@font-face{font-family:"FontAwesome";font-style:normal;font-weight:400;font-display:block;src:url("%1$sfa-brands-400.woff2") format("woff2"),url("%1$sfa-brands-400.woff") format("woff")}',
			esc_url_raw( $font_base_url )
		);
	}

	/**
	 * Determine whether the current Divi 5 layout contains a Property Hive
	 * module that can render a font icon.
	 *
	 * @param mixed $dynamic_assets_instance Divi dynamic assets instance.
	 * @return bool
	 */
	private function has_propertyhive_divi5_icon_module( $dynamic_assets_instance )
	{
		if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
			return true;
		}

		$icon_modules = array(
			'propertyhive/property-availability',
			'propertyhive/property-bathrooms',
			'propertyhive/property-bedrooms',
			'propertyhive/property-council-tax-band',
			'propertyhive/property-features',
			'propertyhive/property-floor-area',
			'propertyhive/property-let-available-date',
			'propertyhive/property-price',
			'propertyhive/property-reception-rooms',
			'propertyhive/property-reference-number',
			'propertyhive/property-tenure',
			'propertyhive/property-type',
		);

		if ( ! is_object( $dynamic_assets_instance ) ) {
			return false;
		}

		if ( method_exists( $dynamic_assets_instance, 'get_saved_page_blocks' ) ) {
			$used_blocks = $dynamic_assets_instance->get_saved_page_blocks();
			if ( array_intersect( $icon_modules, is_array( $used_blocks ) ? $used_blocks : array() ) ) {
				return true;
			}
		}

		if ( method_exists( $dynamic_assets_instance, 'get_block_assets_list' ) ) {
			$block_assets = $dynamic_assets_instance->get_block_assets_list();
			if ( array_intersect( $icon_modules, array_keys( is_array( $block_assets ) ? $block_assets : array() ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Ensure Divi loads its Font Awesome files for Property Hive modules.
	 * Divi's core detector does not automatically inspect third-party modules.
	 *
	 * @param array $global_asset_list Current Divi dynamic asset list.
	 * @param array $assets_args Divi dynamic asset context.
	 * @param mixed $dynamic_assets_instance Divi dynamic assets instance.
	 * @return array
	 */
	public function propertyhive_add_divi5_icon_assets( $global_asset_list, $assets_args = array(), $dynamic_assets_instance = null )
	{
		if (
			! function_exists( 'et_builder_d5_enabled' )
			|| ! et_builder_d5_enabled()
		) {
			return $global_asset_list;
		}

		$assets_prefix = $assets_args['assets_prefix'] ?? '';
		if (
			'' === $assets_prefix
			&& class_exists( '\\ET\\Builder\\FrontEnd\\Assets\\DynamicAssetsUtils' )
		) {
			$assets_prefix = \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path();
		}

		if ( '' !== $assets_prefix ) {
			$global_asset_list['et_icons_fa'] = array(
				'css' => $assets_prefix . '/css/icons_fa_all.css',
			);
		}

		return $global_asset_list;
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
