<?php
/**
 * Elementor Property Additional Field Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Additional_Field_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-additional-field';
	}

	public function get_title() {
		return __( 'Additional Field', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-meta-data';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'additional', 'custom' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Additional Field', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$current_settings = get_option( 'propertyhive_template_assistant', array() );

        $custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

        $options = array();
        foreach ( $custom_fields as $custom_field )
        {
            if ( substr($custom_field['meta_box'], 0, 9) == 'property_' )
            {
            	$options[$custom_field['field_name']] = __( $custom_field['field_label'], 'propertyhive' );
            }
        }
        foreach ( $custom_fields as $custom_field )
        {
            if ( substr($custom_field['meta_box'], 0, 7) == 'office_' )
            {
            	$options['office-' . $custom_field['field_name']] = __( $custom_field['field_label'], 'propertyhive' ) . ' (' . __( 'Office custom field', 'propertyhive' ) . ')';
            }
        }

		$this->add_control(
			'field',
			[
				'label' => __( 'Field', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::ICONS,
			]
		);

		$this->add_control(
			'before',
			[
				'label' => __( 'Before', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'after',
			[
				'label' => __( 'After', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::TEXT,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Additional Field', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Typography', 'propertyhive' ),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}}',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset($property->id) ) 
		{
			return;
		}

		if ( !isset($settings['field']) || empty($settings['field']) )
		{
			return;
		}

		$field_name = '';
		$field_value = '';
		if ( substr($settings['field'], 0, 7) == 'office-' )
		{
			$field_name = str_replace('office-', '', $settings['field']);
			$office_id = (int)$property->_office_id;
			if ( !empty($office_id) )
			{
				$field_value = get_post_meta( $office_id, $field_name, true );
			}
			$field_meta_box = 'office';
		}
		else
		{
			// Property field
			$field_name = $settings['field'];
			if ( $property->{$settings['field']} != '' && !empty($property->{$settings['field']}) )
			{
				$field_value = $property->{$settings['field']};
			}
		}

		if ( !empty($field_value) )
		{
			$current_settings = get_option( 'propertyhive_template_assistant', array() );

        	$custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

        	foreach ( $custom_fields as $custom_field )
        	{
        		if ( $custom_field['field_name'] == $field_name )
        		{
        			if ( isset($custom_field['field_type']) )
        			{
	        			switch ( $custom_field['field_type'] )
	        			{
	        				case "image":
	        				{
                                $image = wp_get_attachment_image_src( $field_value, 'full' );
                                if ($image !== FALSE)
                                {
                                    $field_value = '<img src="' . $image[0] . '" alt="">';
                                }
	        				}
	        			}
	        		}
	        		break;
        		}
        	}
		}

		if ( !empty($field_value) )
		{
	        echo '<div class="elementor-widget-additional-field elementor-widget-additional-field-' . $settings['field'] . '">';
	        if ( isset($settings['icon']) && !empty($settings['icon']) )
	        {
	        	\Elementor\Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
	        	echo ' ';
	        }
	        if ( isset($settings['before']) && !empty($settings['before']) )
	        {
	        	echo $settings['before'] . ' ';
	        }
	        echo $field_value;
	        if ( isset($settings['after']) && !empty($settings['after']) )
	        {
	        	echo ' ' . $settings['after'];
	        }
	        echo '</div>';
	    }

	}

}