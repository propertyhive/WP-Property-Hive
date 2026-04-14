<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PH_Text_Substitution {

	public function __construct() {

		$current_settings = get_option( 'propertyhive_template_assistant', array() );

		if ( isset($current_settings['text_translations']) && is_array($current_settings['text_translations']) && !empty($current_settings['text_translations']) )
        {
			add_filter( 'gettext', array( $this, 'do_text_translation'), 20, 3 );
		}
	}

	public function do_text_translation( $translated_text, $text, $domain )
    {
    	if ( $domain != 'propertyhive' )
    	{
    		return $translated_text;
    	}

    	$current_settings = get_option( 'propertyhive_template_assistant', array() );

    	if ( isset($current_settings['text_translations']) && is_array($current_settings['text_translations']) && !empty($current_settings['text_translations']) )
        {
	        foreach ( $current_settings['text_translations'] as $text_translation )
	        {
	            if ( isset($text_translation['search']) && $text_translation['search'] == $translated_text )
	            {
	                $translated_text = $text_translation['replace'];
	            }
	        }
	    }

        return $translated_text;
    }
}

new PH_Text_Substitution();