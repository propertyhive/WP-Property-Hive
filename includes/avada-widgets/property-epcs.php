<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


fusion_builder_map( array(
    'name'        => esc_attr__( 'Property EPCs', 'propertyhive' ),
    'shortcode'   => 'avada_' . str_replace("-", "_", sanitize_title($widget)),
    'icon'        => 'fusiona-video', // Use a Fusion icon
    'preview'    => dirname( PH_PLUGIN_FILE ) . '/includes/avada-widgets/' . sanitize_title($widget) . '-preview.php',
	'preview_id' => 'fusion-builder-block-module-' . sanitize_title($widget) . '-preview-template',
    'params'      => array(
    	[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Show Title', 'propertyhive' ),
			'param_name'  => 'show_title',
			'value'       => [
				'yes'  => esc_attr__( 'Yes', 'propertyhive' ),
				'no' => esc_attr__( 'No', 'propertyhive' ),
			],
			'default'     => 'yes',
		],
    ),
) );