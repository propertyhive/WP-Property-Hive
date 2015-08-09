<?php
/**
 * Template Loader
 *
 * @class 		PH_Template_Loader
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		BIOSTALL
 */
class PH_Template_Loader {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
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
        
		if ( is_single() && get_post_type() == 'property' ) {

			$file 	= 'single-property.php';
			$find[] = $file;
			$find[] = PH_TEMPLATE_PATH . $file;
            

		} elseif ( is_post_type_archive( 'property' ) || is_page( ph_get_page_id( 'search_results' ) ) ) {

			$file 	= 'archive-property.php';
			$find[] = $file;
			$find[] = PH_TEMPLATE_PATH . $file;

		}

		if ( $file ) {
			$template       = locate_template( $find );
			$status_options = get_option( 'propertyhive_status_options', array() );
			if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
				$template = PH()->plugin_path() . '/templates/' . $file;
		}

		return $template;
	}

}

new PH_Template_Loader();