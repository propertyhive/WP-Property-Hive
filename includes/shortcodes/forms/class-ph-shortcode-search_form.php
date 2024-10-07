<?php
class PH_Shortcode_Search_form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_search_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
        $atts = shortcode_atts( array(
			'id' 					=> 'shortcode',
			'default_department' 	=> ''
		), $atts, 'property_search_form' );

		$form_controls = ph_get_search_form_fields();

		$form_controls = apply_filters( 'propertyhive_search_form_fields_' . $atts['id'], $form_controls, $atts );
		$form_controls = apply_filters( 'propertyhive_search_form_fields', $form_controls, $atts );

		// We 100% need department so make sure it exists. If it doesn't, set a hidden field
	    if ( !isset($form_controls['department']) )
	    {
	        $original_form_controls = ph_get_search_form_fields();
	        $original_department = $original_form_controls['department'];
	        $original_department['type'] = 'hidden';

	        $form_controls['department'] = $original_department;
	    }

		$form_controls = apply_filters( 'propertyhive_search_form_fields_after_' . $atts['id'], $form_controls, $atts );
		$form_controls = apply_filters( 'propertyhive_search_form_fields_after', $form_controls, $atts );

	    if (
	    	isset($atts['default_department']) && in_array($atts['default_department'], array_keys( ph_get_departments() )) &&
	    	( !isset($_REQUEST['department']) )
	    )
	    {
	    	$form_controls['department']['value'] = $atts['default_department'];
	    }

		ob_start();

		ph_get_template( 'global/search-form.php', array( 'form_controls' => $form_controls, 'id' => $atts['id'] ) );

		return ob_get_clean();
    }
}

new PH_Shortcode_Search_form();