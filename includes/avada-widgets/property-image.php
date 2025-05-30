<?php

fusion_builder_map( array(
    'name'        => esc_attr__( $widget, 'propertyhive' ),
    'shortcode'   => 'avada_' . str_replace("-", "_", sanitize_title($widget)),
    'icon'        => 'fusiona-image', // Use a Fusion icon
    //'preview'    => dirname( PH_PLUGIN_FILE ) . '/includes/avada-widgets/' . sanitize_title($widget) . '-preview.php',
	//'preview_id' => 'fusion-builder-block-module-' . sanitize_title($widget) . '-preview-template',
    'params'      => array(
    	[
		    'type'        => 'textfield',
		    'heading'     => esc_attr__( 'Image #', 'fusion-builder' ),
		    'param_name'  => 'image_number',
		    'input_type'  => 'number',
		    'value'       => '1',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Image Size', 'fusion-builder' ),
			'param_name'  => 'image_size',
			'value'       => [
				'thumbnail' => __( 'Thumbnail', 'propertyhive' ),
				'medium' => __( 'Medium', 'propertyhive' ),
				'large' => __( 'Large', 'propertyhive' ),
				'full' => __( 'Full', 'propertyhive' ),
			],
			'default'     => 'large',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Output Ratio', 'fusion-builder' ),
			'param_name'  => 'output_ratio',
			'value'       => [
				'' => __( 'Uploaded Ratio', 'propertyhive' ),
				'3:2' => __( '3:2', 'propertyhive' ),
				'4:3' => __( '4:3', 'propertyhive' ),
				'16:9' => __( '16:9', 'propertyhive' ),
				'1:1' => __( 'Square', 'propertyhive' ),
			],
			'default'     => '',
		],
    ),
) );