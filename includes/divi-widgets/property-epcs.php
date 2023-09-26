<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Epcs_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_epcs_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property EPCs', 'propertyhive' );
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

        if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        {
            $epc_urls = $property->_epc_urls;
            if ( is_array($epc_urls) && !empty( $epc_urls ) )
            {
                foreach ($epc_urls as $epc)
                {
                    echo '<a href="' . $epc['url'] . '" data-fancybox="epcs" rel="nofollow"><img src="' . $epc['url'] . '" alt=""></a>';
                }
            }
        }
        else
        {
            $epc_attachment_ids = $property->get_epc_attachment_ids();

            if ( !empty($epc_attachment_ids) )
            {
                echo '<div class="epcs">';

                    echo '<h4>' . __( 'EPCs', 'propertyhive' ) . '</h4>';

                    foreach ( $epc_attachment_ids as $attachment_id )
                    {
                        if ( wp_attachment_is_image($attachment_id) )
                        {
                            echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="epc" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
                        }
                        else
                        {
                            echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View EPC', 'propertyhive' ) . '</a>';
                        }
                    }

                echo '</div>';
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}