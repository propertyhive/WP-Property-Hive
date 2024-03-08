<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Template Loader
 *
 * @class 		PH_Template_Loader
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Template_Loader {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init()
	{
		$priority = 10;
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'oxygen/functions.php' ) )
        {
        	$priority = 99;
        }
		add_filter( 'template_include', array( $this, 'template_loader' ), $priority );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. propertyhive looks for theme
	 * overrides in /theme/propertyhive/ by default
	 *
	 * For beginners, it also looks for a propertyhive.php template first. If the user adds
	 * this to the theme (containing a propertyhive() inside) this will be used for all
	 * propertyhive templates.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public function template_loader( $template ) {
	    
		$find = array( 'propertyhive.php' );
		$file = '';
        
		if ( is_single() && get_post_type() == 'property' ) 
		{	
			$use_property_hive_template = true;

			// Check for single Divi property page template
			if ( class_exists( 'ET_Theme_Builder_Request' ) )
			{
				$template_query = new WP_Query(
					array(
						'post_type'              => ET_THEME_BUILDER_TEMPLATE_POST_TYPE,
						'post_status'            => 'publish',
						'posts_per_page'         => 1,
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'meta_query'             => array(
							array(
								'key'     => '_et_enabled',
								'value'   => '1',
								'compare' => '=',
							),
							array(
								'key'     => '_et_body_layout_id',
								'value'   => '0',
								'compare' => '!=',
							),
							array(
								'key'     => "_et_use_on",
								'value'   => 'singular:post_type:property:all',
								'compare' => '=',
							),
							array(
								'key'     => '_et_theme_builder_marked_as_unused',
								'compare' => 'NOT EXISTS',
							),
						),
					)
				);

				if ( $template_query->have_posts() ) 
				{
					$use_property_hive_template = false;
				}
				wp_reset_postdata();
			}

			// Check for single Bricks Builder property page template
			if ( defined('BRICKS_DB_TEMPLATE_SLUG') )
			{
				$template_query = new WP_Query(
					array(
						'post_type'              => BRICKS_DB_TEMPLATE_SLUG,
						'post_status'            => 'publish',
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'meta_query'             => array(
							array(
								'key'     => '_bricks_template_type',
								'compare' => 'content',
							),
						),
					)
				);

				if ( $template_query->have_posts() ) 
				{
					while ( $template_query->have_posts() )
					{
						$template_query->the_post();

						$template_settings = get_post_meta( get_the_ID(), '_bricks_template_settings', TRUE );

						if ( 
							isset($template_settings['templateConditions']) && 
							is_array($template_settings['templateConditions']) &&
							!empty($template_settings['templateConditions'])
						)
						{
							foreach ( $template_settings['templateConditions'] as $templateCondition )
							{
								if ( 
									isset($templateCondition['main']) && 
									$templateCondition['main'] == 'postType' &&
									isset($templateCondition['postType']) && 
									is_array($templateCondition['postType']) &&
									in_array('property', $templateCondition['postType'])
								)
								{
									$use_property_hive_template = false;
								}
							}
						}
					}
				}
				wp_reset_postdata();
			}

			if ( $use_property_hive_template )
			{
				$file 	= 'single-property.php';
				$find[] = $file;
				$find[] = PH_TEMPLATE_PATH . $file;
			}
		} 
		elseif ( is_post_type_archive( 'property' ) || is_page( ph_get_page_id( 'search_results' ) ) ) 
		{
			$use_property_hive_template = true;

			// Check for single Bricks Builder property page template
			if ( defined('BRICKS_DB_TEMPLATE_SLUG') )
			{
				$template_query = new WP_Query(
					array(
						'post_type'              => BRICKS_DB_TEMPLATE_SLUG,
						'post_status'            => 'publish',
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'meta_query'             => array(
							array(
								'key'     => '_bricks_template_type',
								'compare' => 'archive',
							),
						),
					)
				);

				if ( $template_query->have_posts() ) 
				{
					while ( $template_query->have_posts() )
					{
						$template_query->the_post();

						$template_settings = get_post_meta( get_the_ID(), '_bricks_template_settings', TRUE );

						if ( 
							isset($template_settings['templateConditions']) && 
							is_array($template_settings['templateConditions']) &&
							!empty($template_settings['templateConditions'])
						)
						{
							foreach ( $template_settings['templateConditions'] as $templateCondition )
							{
								if ( 
									isset($templateCondition['main']) && 
									$templateCondition['main'] == 'archiveType' &&
									is_array($templateCondition['archiveType']) &&
									in_array('postType', $templateCondition['archiveType']) &&
									is_array($templateCondition['archivePostTypes']) &&
									in_array('property', $templateCondition['archivePostTypes'])
								)
								{
									$use_property_hive_template = false;
								}
							}
						}
					}
				}
			}

			if ( $use_property_hive_template )
			{
				$file 	= 'archive-property.php';
				$find[] = $file;
				$find[] = PH_TEMPLATE_PATH . $file;
			}

		}

		if ( $file ) 
		{
			$template = locate_template( array_unique($find) );
			if ( ! $template )
			{
				$template = PH()->plugin_path() . '/templates/' . $file;
			}
		}

		$template = apply_filters( 'propertyhive_loaded_template', $template );

		return $template;
	}

}

new PH_Template_Loader();