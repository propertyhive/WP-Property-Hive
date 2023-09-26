<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Epcs_Link_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_epcs_link_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property EPCs Link', 'propertyhive' );
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
            $epc_urls = $property->epc_urls;
            if ( !is_array($epc_urls) ) { $epc_urls = array(); }

            if ( !empty($epc_urls) )
            {
                $i = 0;
                foreach ( $epc_urls as $epc )
                {
                    $image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'bmp' );
                    $image = false;
                    foreach ( $image_extensions as $image_extension )
                    {
                        if ( strpos(strtolower($epc['url']), '.' . $image_extension) )
                        {
                            $image = true;
                        }
                    }
                    if ( $image )
                    {
                        echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . $epc['url'] . '" data-fancybox="epcs" rel="nofollow">' . ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) . '</a>';
                        ++$i;
                    }
                    else
                    {
                        echo '<a href="' . $epc['url'] . '" rel="nofollow" target="_blank">' . ( count($epc_urls) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) . '</a>';
                    }
                }
            }
        }
        else
        {
            $epc_attachment_ids = $property->get_epc_attachment_ids();

            if ( !empty($epc_attachment_ids) )
            {
                $i = 0;
                foreach ( $epc_attachment_ids as $attachment_id )
                {
                    echo '<a' . ( $i > 0 ? ' style="display:none"' : '' ) . ' href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="epc" rel="nofollow">' . ( count($epc_attachment_ids) > 1 ? __( 'EPCs', 'propertyhive' ) : __( 'EPC', 'propertyhive' ) ) . '</a>';
                    ++$i;
                }
            }
        }

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}