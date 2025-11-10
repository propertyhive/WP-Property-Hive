<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Floorplans_Link_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_floorplans_link_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Floorplans Link', 'propertyhive' );
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

        if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        {
            $floorplan_urls = $property->floorplan_urls;
            if ( !is_array($floorplan_urls) ) { $floorplan_urls = array(); }

            if ( !empty($floorplan_urls) )
            {
                $i = 0;
                foreach ( $floorplan_urls as $floorplan )
                {
                    echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url($floorplan['url']) . '" data-fancybox="floorplans" rel="nofollow">' . esc_html(( count($floorplan_urls) > 1 ? __( 'Floorplans', 'propertyhive' ) : __( 'Floorplan', 'propertyhive' ) )) . '</a>';
                    ++$i;
                }
            }
        }
        else
        {
            $floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

            if ( !empty($floorplan_attachment_ids) )
            {
                $i = 0;
                foreach ( $floorplan_attachment_ids as $attachment_id )
                {
                    echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="floorplans" rel="nofollow">' . esc_html(( count($floorplan_attachment_ids) > 1 ? __( 'Floorplans', 'propertyhive' ) : __( 'Floorplan', 'propertyhive' ) )) . '</a>';
                    ++$i;
                }
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}