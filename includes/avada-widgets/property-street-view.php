<?php

fusion_builder_map( array(
    'name'        => esc_attr__( $widget, 'propertyhive' ),
    'shortcode'   => 'avada_' . str_replace("-", "_", sanitize_title($widget)),
    'icon'        => 'fusiona-map', // Use a Fusion icon
    //'preview'    => dirname( PH_PLUGIN_FILE ) . '/includes/avada-widgets/' . sanitize_title($widget) . '-preview.php',
	//'preview_id' => 'fusion-builder-block-module-' . sanitize_title($widget) . '-preview-template',
    'params'      => array(
    	[
		    'type'        => 'textfield',
		    'heading'     => esc_attr__( 'Height (px)', 'fusion-builder' ),
		    'param_name'  => 'height',
		    'input_type'  => 'number',
		    'value'       => 400,
		],
    ),
) );