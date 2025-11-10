<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Enquiry_Form_Link_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_enquiry_form_link_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Enquiry Form Link', 'propertyhive' );
        $this->icon = '1';
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
?>
    <a data-fancybox data-src="#makeEnquiry<?php echo (int)$property->id; ?>" href="javascript:;"><?php echo esc_html(__( 'Make Enquiry', 'propertyhive' )); ?></a>

    <!-- LIGHTBOX FORM -->
    <div id="makeEnquiry<?php echo (int)$property->id; ?>" style="display:none;">
        
        <h2><?php echo esc_html(__( 'Make Enquiry', 'propertyhive' )); ?></h2>
        
        <p><?php _e( 'Please complete the form below and a member of staff will be in touch shortly.', 'propertyhive' ); ?></p>
        
        <?php propertyhive_enquiry_form(); ?>
        
    </div>
    <!-- END LIGHTBOX FORM -->
<?php

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}