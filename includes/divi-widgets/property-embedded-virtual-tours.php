<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Embedded_Virtual_Tours_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_embedded_virtual_tours_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Embedded Virtual Tours', 'propertyhive' );
        $this->icon = 'i';
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

        $virtual_tours = $property->get_virtual_tours();

        if ( !empty($virtual_tours) )
        {
            echo '<div class="embedded-virtual-tours">';

                echo '<h4>' . esc_html(__( 'Virtual Tours', 'propertyhive' )) . '</h4>';

                foreach ( $virtual_tours as $virtual_tour )
                {
                    if ( isset($settings['oembed']) && $settings['oembed'] == 'yes' )
                    {
                        $embed_code = wp_oembed_get($virtual_tour['url']);
                        echo $embed_code;
                    }
                    else
                    {
                        $virtual_tour['url'] = preg_replace(
                            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                            "//www.youtube.com/embed/$2",
                            $virtual_tour['url']
                        );


                        $virtual_tour['url'] = preg_replace(
                            '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/i',
                            "//player.vimeo.com/video/$6",
                            $virtual_tour['url']
                        );

                        echo '<iframe src="' . esc_url($virtual_tour['url']) . '" height="500" width="100%" allowfullscreen frameborder="0" allow="fullscreen"></iframe>';
                    }
                }

            echo '</div>';
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}