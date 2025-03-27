<?php

fusion_builder_map( array(
    'name'        => esc_attr__( $widget, 'propertyhive' ),
    'shortcode'   => 'avada_' . str_replace("-", "_", sanitize_title($widget)),
    'icon'        => 'fusiona-image', // Use a Fusion icon
    //'preview'    => dirname( PH_PLUGIN_FILE ) . '/includes/avada-widgets/' . sanitize_title($widget) . '-preview.php',
	//'preview_id' => 'fusion-builder-block-module-' . sanitize_title($widget) . '-preview-template',
    'params'      => array(
    	[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Hide Thumbnails', 'fusion-builder' ),
			'param_name'  => 'hide_thumbnails',
			'value'       => [
				'yes'  => esc_attr__( 'Yes', 'fusion-builder' ),
				'no' => esc_attr__( 'No', 'fusion-builder' ),
			],
			'default'     => 'no',
		],
    ),
) );