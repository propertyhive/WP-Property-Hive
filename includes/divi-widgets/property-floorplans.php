<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Floorplans_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_floorplans_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Floorplans', 'propertyhive' );
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
            $floorplan_urls = $property->_floorplan_urls;
            if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
            {
                echo '<div class="floorplans">';

                    echo '<h4>' . esc_html(__( 'Floorplans', 'propertyhive' )) . '</h4>';

                    foreach ($floorplan_urls as $floorplan)
                    {
                        echo '<a href="' . esc_url($floorplan['url']) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . esc_url($floorplan['url']) . '" alt=""></a>';
                    }

                echo '</div>';
            }
        }
        else
        {
            $floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

            if ( !empty($floorplan_attachment_ids) )
            {
                echo '<div class="floorplans">';

                    echo '<h4>' . esc_html(__( 'Floorplans', 'propertyhive' )) . '</h4>';

                    foreach ( $floorplan_attachment_ids as $attachment_id )
                    {
                        if ( wp_attachment_is_image($attachment_id) )
                        {
                            echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . esc_url(wp_get_attachment_url($attachment_id)) . '" alt=""></a>';
                        }
                        else
                        {
                            echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html(__( 'View Floorplan', 'propertyhive' )) . '</a>';
                        }
                    }

                echo '</div>';
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}