<?php
/**
 * Elementor Property Images Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Images_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-images';
	}

	public function get_title() {
		return __( 'Images', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-product-images';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'images', 'photos', 'gallery', 'slideshow' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Images', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'hide_thumbnails',
			[
				'label' => __( 'Hide Thumbnails', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'propertyhive' ),
				'label_off' => __( 'No', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);

		$this->add_control(
            'num_images',
            [
                'label' => __( 'Images To Show', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label' => __( 'Link Images To', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
				'options' => [
					'' => esc_html__( 'Image In Lightbox', 'propertyhive' ),
					'blank' => esc_html__( 'Image In New Window', 'propertyhive' ),
					'property'  => esc_html__( 'Property URL', 'propertyhive' ),
				],
            ]
        );

		$this->end_controls_section();

	}

	protected function render() {

		global $property;

		$settings = $this->get_settings_for_display();

		if ( !isset($property->id) ) {
			return;
		}

		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

		wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.7.2', true );
        wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
        wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css', array(), '2.7.2' );

		if ( 'yes' === $settings['hide_thumbnails'] ) 
		{
			remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );
		}

		if ( isset($settings['link_to']) )
		{
			switch ($settings['link_to'])
			{
				case "blank":
				{
					add_filter( 'propertyhive_single_property_image_html', array( $this, 'customise_property_images_html_blank' ), 10, 2 );
					break;
				}
				case "property":
				{
					add_filter( 'propertyhive_single_property_image_html', array( $this, 'customise_property_images_html_property' ), 10, 2 );
					break;
				}
				default:
				{
					wp_enqueue_script( 'propertyhive_fancybox' );
					wp_enqueue_style( 'propertyhive_fancybox_css' );
				}
			}
		}
		else
		{
			wp_enqueue_script( 'propertyhive_fancybox' );
			wp_enqueue_style( 'propertyhive_fancybox_css' );
		}

		propertyhive_show_property_images( ( isset($settings['num_images']) && !empty($settings['num_images']) && is_numeric($settings['num_images']) ) ? (int)$settings['num_images'] : '' );

		// On render widget from Editor - trigger the init manually.
		if ( wp_doing_ajax() ) {
			?>
			<script>
		        // The slider being synced must be initialized first
		        //ph_init_slideshow();
			</script>
			<?php
		}

	}

	public function customise_property_images_html_blank( $html, $post_id )
	{
		$html = str_replace("data-fancybox=\"gallery-" . (int)$post_id . "\"", "target=\"_blank\"", $html);
		return $html;
	}

	public function customise_property_images_html_property( $html, $post_id )
	{
		$html = str_replace("data-fancybox=\"gallery-" . (int)$post_id . "\"", "", $html);

		$html = preg_replace('/(href=")([^"]*)(")/', 'href="' . esc_url(get_permalink($post_id)) . '"', $html);

		return $html;
	}
}