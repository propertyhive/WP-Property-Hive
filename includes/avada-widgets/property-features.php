<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


fusion_builder_map( array(
    'name'        => esc_attr__( 'Property Features', 'propertyhive' ),
    'shortcode'   => 'avada_' . str_replace("-", "_", sanitize_title($widget)),
    'icon'        => 'fusiona-list-ul', // Use a Fusion icon
    'preview'    => dirname( PH_PLUGIN_FILE ) . '/includes/avada-widgets/' . sanitize_title($widget) . '-preview.php',
	'preview_id' => 'fusion-builder-block-module-' . sanitize_title($widget) . '-preview-template',
    'params'      => array(
    	[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Show Title', 'propertyhive' ),
			'param_name'  => 'show_title',
			'value'       => [
				'on'  => esc_attr__( 'On', 'propertyhive' ),
				'off' => esc_attr__( 'Off', 'propertyhive' ),
			],
			'default'     => 'on',
		],
        [
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Alignment', 'propertyhive' ),
			'description' => esc_attr__( 'Choose to align the output left, right or center.', 'propertyhive' ),
			'param_name'  => 'content_align',
			'responsive'  => [
				'state'         => 'large',
				'default_value' => true,
			],
			'value'       => [
				'left'   => esc_attr__( 'Left', 'propertyhive' ),
				'center' => esc_attr__( 'Center', 'propertyhive' ),
				'right'  => esc_attr__( 'Right', 'propertyhive' ),
			],
			'default'     => 'left',
			'group'       => esc_attr__( 'Design', 'propertyhive' ),
		],
		[
			'type'             => 'typography',
			//'remove_from_atts' => true,
			'global'           => true,
			'heading'          => esc_attr__( 'Typography', 'propertyhive' ),
			'param_name'       => 'main_typography',
			'group'            => esc_attr__( 'Design', 'propertyhive' ),
			'choices'          => [
				'font-family'    => 'features_font',
				'font-size'      => 'font_size',
				'line-height'    => 'line_height',
				'letter-spacing' => 'letter_spacing',
				'text-transform' => 'text_transform',
			],
			'default'          => [
				'font-family'    => '',
				'variant'        => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Font Color', 'propertyhive' ),
			'param_name'  => 'text_color',
			'value'       => '',
			'group'       => esc_attr__( 'Design', 'propertyhive' ),
		],
    ),
) );