<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Map_Link_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_map_link_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Map Link', 'propertyhive' );
        $this->icon = 'Y';
    }

    public function get_fields()
    {
        $fields = array(
            'map_link_type' => array(
                'label' => esc_html__( 'Link Type', 'propertyhive' ),
                'type' => 'select',
                'options' => [
                    '_blank' => 'Open map in new window',
                    'embedded' => 'Open embedded map in lightbox',
                    'iframe' => 'Open iframe map in lightbox',
                ],
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

        if ( $property->latitude == '' || $property->longitude == '' || $property->latitude == '0' || $property->longitude == '0' )
        {
            return;
        }

        ob_start();

        $link_type = ( isset($this->props['map_link_type']) && !empty($this->props['map_link_type']) ) ? $this->props['map_link_type'] : '_blank';

        switch ($link_type)
        {
            case "_blank":
            {
                echo '<a href="https://www.google.com/maps/?q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '" target="_blank">' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
                break;
            }
            case "embedded":
            {
                echo '<a href="#map_lightbox" data-fancybox>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
        
                echo '<div id="map_lightbox" style="display:none; width:90%; max-width:800px;">';
                    echo do_shortcode('[property_map]');
                echo '</div>';
                break;
            }
            case "iframe":
            {
                echo '<a 
                    href="#" 
                    data-fancybox 
                    data-type="iframe" 
                    data-src="https://maps.google.com/?output=embed&amp;f=q&amp;q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;layer=t&amp;hq=&amp;t=m&amp;z=15"
                >' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
                break;
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}