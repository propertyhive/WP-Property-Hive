<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Negotiator_Email_Address_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_negotiator_email_address_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Negotiator Email Address', 'propertyhive' );
        $this->icon = 'G';
    }

    public function get_fields()
    {
        $fields = array(
            'hyperlink' => array(
                'label' => __( 'Hyperlink', 'propertyhive' ),
                'type' => 'select',
                'options' => [
                    'yes' => __( 'Yes', 'propertyhive' ),
                    'no' => __( 'No', 'propertyhive' ),
                ],
                'default_on_front' => 'yes',
                'toggle_slug' => 'main_content',
            ),
        );

        return $fields;
    }

    public function render( $attrs, $content, $render_slug )
    {
        $post_id = get_the_ID();

        $property = new PH_Property($post_id);

        if ( !isset($property->id) ) {
            return;
        }

        $return = '';
        
        if ( isset($this->props['hyperlink']) && $this->props['hyperlink'] == 'yes' ) 
        { 
            $return .= '<a href="mailto:' . esc_attr($property->negotiator_email_address) . '">';
        }

        $return .= esc_html($property->negotiator_email_address);

        if ( isset($this->props['hyperlink']) && $this->props['hyperlink'] == 'yes' ) 
        { 
            $return .= '</a>';
        }

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}