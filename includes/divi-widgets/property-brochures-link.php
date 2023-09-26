<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Brochures_Link_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_brochures_link_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Brochures Link', 'propertyhive' );
        $this->icon = '|';
    }

    public function get_fields()
    {
        $fields = array();

        return $fields;
    }

    public function render( $attrs, $content, $render_slug )
    {
        $post_id = get_the_ID();

        $property = new PH_Property($post_id);

        if ( !isset($property->id) ) {
            return;
        }

        ob_start();

        $label = __( 'Brochure', 'propertyhive' );

        if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        {
            $brochure_urls = $property->brochure_urls;
            if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

            if ( !empty($brochure_urls) )
            {
                foreach ( $brochure_urls as $brochure )
                {
                    echo '<a href="' . $brochure['url'] . '" target="_blank" rel="nofollow">' . $label . '</a>';
                }
            }
        }
        else
        {
            $brochure_attachment_ids = $property->get_brochure_attachment_ids();

            if ( !empty($brochure_attachment_ids) )
            {
                foreach ( $brochure_attachment_ids as $attachment_id )
                {
                    echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . $label . '</a>';
                }
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}