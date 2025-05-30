<?php
/**
 * Elementor Property Embedded Virtual Tours Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Embedded_Virtual_Tours_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-embedded-virtual-tours';
	}

	public function get_title() {
		return __( 'Embedded Virtual Tours', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-video-camera';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'virtual tour' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Virtual Tours', 'propertyhive' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'propertyhive' ),
				'label_off' => __( 'Hide', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Title Typography', 'propertyhive' ),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .embedded-virtual-tours h4',
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Colour', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
				    'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .embedded-virtual-tours h4' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'show_title' => 'yes'
		        ],
			]
		);

		$this->add_control(
			'oembed',
			[
				'label' => __( 'Use oEmbed', 'propertyhive' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'propertyhive' ),
				'label_off' => __( 'No', 'propertyhive' ),
				'return_value' => 'yes',
				'default' => 'no',
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

		if ( isset($settings['show_title']) && $settings['show_title'] != 'yes' )
		{
?>
<style type="text/css">
.embedded-virtual-tours h4 { display:none; }
</style>
<?php
		}

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

	}

}