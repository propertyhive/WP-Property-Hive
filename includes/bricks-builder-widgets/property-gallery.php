<?php
/**
 * Bricks Builder Property Gallery Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Bricks_Builder_Property_Gallery_Widget extends \Bricks\Element {

	// Element properties
	public $category     = 'propertyhive'; // Use predefined element category 'general'
  	public $name         = 'bricks-builder-property-gallery'; // Make sure to prefix your elements
  	public $icon         = 'fas fa-images'; // icon font class

	public function get_label() 
	{
	    return esc_html__( 'Gallery', 'propertyhive' );
	}

	public function set_control_groups() 
	{
		/*$this->control_groups['form'] = [
	      	'title' => esc_html__( 'Form', 'propertyhive' ),
	      	'tab' => 'content', // content / style
	    ];*/
	}

	public function set_controls() 
	{
		$this->controls['gallery_layout'] = [
	      	'tab' => 'content',
	      	//'group' => 'settings',
	      	'label' => esc_html__( 'Layout', 'propertyhive' ),
	      	'type' => 'select',
	      	'options' => [
	        	'grid' => __( 'Six Images', 'propertyhive' ),
                'one_large_four_small' => __( 'One Large Image, Four Small', 'propertyhive' ),
	      	],
	      	//'inline' => true,
	      	//'clearable' => false,
	      	//'pasteStyles' => false,
	      	'default' => 'grid',
	    ];

	    $this->controls['start_at_image'] = [ // Unique control identifier (lowercase, no spaces)
      		'tab' => 'content', // Control tab: content/style
      		//'group' => 'form', // Show under control group
      		'label' => esc_html__( 'Start at Image #', 'propertyhive' ), // Control label
      		'type' => 'text', // Control type 
      		'default' => '1'
    	];
	}

	public function render()
	{
		global $property;

        $settings = $this->settings;

        if ( !isset($property->id) ) {
            return;
        }

		$root_classes[] = $this->name;

	    // Add 'class' attribute to element root tag
	    $this->set_attribute( '_root', 'class', $root_classes );

		echo "<div {$this->render_attributes( '_root' )}>";
	    	
		$start_at_image = ( isset($settings['start_at_image']) && !empty($settings['start_at_image']) && is_numeric($settings['start_at_image']) ) ? ($settings['start_at_image'] - 1) : 0;
        
        $images = array();
        $images_hidden = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }

            if ( $start_at_image > 0 )
            {
                $photo_urls_hidden = array_slice($photo_urls, 0, $start_at_image);

                foreach ( $photo_urls_hidden as $photo )
                {
                    $images_hidden[] = array(
                        'title' => isset($photo['title']) ? $photo['title'] : '',
                        'url'  => isset($photo['url']) ? $photo['url'] : '',
                        'image' => '<img src="' . ( isset($photo['url']) ? esc_url($photo['url']) : '' ) . '" alt="' . ( isset($photo['title']) ? esc_attr($photo['title']) : '' ) . '">',
                    );
                }
            }
            $photo_urls = array_slice($photo_urls, $start_at_image);

            foreach ( $photo_urls as $photo )
            {
                $images[] = array(
                    'title' => isset($photo['title']) ? $photo['title'] : '',
                    'url'  => isset($photo['url']) ? $photo['url'] : '',
                    'image' => '<img src="' . ( isset($photo['url']) ? esc_url($photo['url']) : '' ) . '" alt="' . ( isset($photo['title']) ? esc_attr($photo['title']) : '' ) . '">',
                );
            }
        }
        else
        {
            $gallery_attachments = $property->get_gallery_attachment_ids();

            if ( !empty($gallery_attachments) )
            {
                if ( $start_at_image > 0 )
                {
                    $gallery_attachments_hidden = array_slice($gallery_attachments, 0, $start_at_image);

                    foreach ($gallery_attachments_hidden as $gallery_attachment)
                    {
                        $images_hidden[] = array(
                            'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                            'url'  => wp_get_attachment_url( $gallery_attachment ),
                            'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'large' ) ),
                            'attachment_id' => $gallery_attachment,
                        );
                    }
                }

                $gallery_attachments = array_slice($gallery_attachments, $start_at_image);

                foreach ($gallery_attachments as $gallery_attachment)
                {
                    $images[] = array(
                        'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                        'url'  => wp_get_attachment_url( $gallery_attachment ),
                        'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'large' ) ),
                        'attachment_id' => $gallery_attachment,
                    );
                }
            }
        }

        if ( isset($images) && is_array($images) && !empty($images) ) 
        {
            $settings['padding'] = 0; // hardcode to 0 for now. Make an option going forward.
?>
        <style type="text/css">
            
            /* Clear floats after image containers */
            .ph-elementor-gallery::after {
                content: "";
                clear: both;
                display: table;
            }

            <?php $max_images = 6; if ( $settings['gallery_layout'] == 'grid' ) { ?>
            .gallery-column {
                position: relative;
                float: left;
                width: 33.33%;
                box-sizing: border-box;
                padding: <?php echo (int)$settings['padding']; ?>px;
            }
            @media (max-width: 1023px) {
                .gallery-column {
                    width: 50%;
                }
            }
            <?php }elseif ( $settings['gallery_layout'] == 'one_large_four_small' ) { $max_images = 5; ?>
            .gallery-column {
                position: relative;
                float: left;
                width: 25%;
                box-sizing: border-box;
                padding: <?php echo (int)$settings['padding']; ?>px;
            }
            .gallery-column:nth-child(1) {
                width: 50%;
            }
            @media (max-width: 1023px) {
                .gallery-column {
                    width: 50%;
                }
                .gallery-column:nth-child(1) {
                    width: 100%;
                }
            }
            <?php } ?>
            
            .gallery-column > a { display:block; height:100%; padding-top:75%; background:center center no-repeat; background-size:cover; }

            .more-images-container {
                position: absolute;
                top:<?php echo (int)$settings['padding']; ?>px;;
                left:<?php echo (int)$settings['padding']; ?>px;
                right:<?php echo (int)$settings['padding']; ?>px;
                bottom:<?php echo (int)$settings['padding']; ?>px;
            }
            .more-images-container.mobile {
                display:none;
            }
            .more-images {
                display: table;
                background: rgba(0, 0, 0, 0.5); /* Black see-through */
                height: 100%;
                width: 100%;
                opacity:1;
                font-size: 18px;
                text-align: center;
            }

            .more-images a {
                color: #f1f1f1;
                display: table-cell;
                vertical-align: middle;
                text-align:center;
                height: 100%;
            }

            @media (max-width: 767px) {
                .gallery-column {
                    width: 100%;
                }
                .gallery-column:nth-child(3),
                .gallery-column:nth-child(4),
                .gallery-column:nth-child(5),
                .gallery-column:nth-child(6) { display:none }

                .more-images-container.desktop {
                    display:none;
                }
                .more-images-container.mobile {
                    display:block;
                }
            }

        </style>

        <script>
            function openGallery()
            {
                if ( jQuery(window).width() <= 767 )
                {
                    jQuery('a#more-images-link-mobile').trigger('click');
                }
                else
                {
                    jQuery('a#more-images-link').trigger('click');
                }
                return false;
            }
        </script>

        <?php
            
            foreach ( $images_hidden as $image_hidden ) 
            {
                echo '<a href="' . esc_url($image_hidden['url']) . '" data-fancybox="elementor-gallery"></a>';
                ++$image_number;
            }
        ?>

        <div class="ph-elementor-gallery">
        <?php
            
            $image_number = 0;

            for ( $j = 0; $j < $max_images; $j++ ) 
            {
                echo '<div class="gallery-column">';

                if ( isset($images[$image_number]) )
                {
                    $id_text = $image_number == ($max_images - 1) ? 'id="more-images-link"' : '';
                    $id_text_mobile = $image_number == 1 ? 'id="more-images-link-mobile"' : '';

                    echo '<a ' . $id_text . ' ' . $id_text_mobile . ' href="' . esc_url($images[$image_number]['url']) . '" data-fancybox="elementor-gallery" style="background-image:url(' . esc_url($images[$image_number]['url']) . ')"></a>';

                    if ( $image_number == 1 )
                    {
                        echo '<div class="more-images-container mobile"><div class="more-images"><a href="javascript:;" onclick="openGallery();">';
                        printf( __( 'See all %d images', 'propertyhive' ), count($images) + count($images_hidden) );
                        echo '</a></div></div>';
                    }
                    if ( $image_number == ($max_images - 1) )
                    {
                        echo '<div class="more-images-container desktop"><div class="more-images"><a href="javascript:;" onclick="openGallery();">';
                        printf( __( 'See all %d images', 'propertyhive' ), count($images) + count($images_hidden) );
                        echo '</a></div></div>';
                    }
                }

                echo '</div>';

                ++$image_number;
            }

            while ( count($images) > ($image_number) )
            {
                echo '<a href="' . esc_url($images[$image_number]['url']) . '" data-fancybox="elementor-gallery"></a>';
                ++$image_number;
            }

            // Code second layout, for one main photo
        }
        ?>
        </div>
        <?php

	    echo '</div>';
	}
}