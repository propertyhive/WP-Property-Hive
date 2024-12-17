<?php
/**
 * Elementor Property Floorplans Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Floorplans_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'property-floorplans';
	}

	public function get_title() {
		return __( 'Floorplans', 'propertyhive' );
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	public function get_categories() {
		return [ 'property-hive' ];
	}

	public function get_keywords() {
		return [ 'property hive', 'propertyhive', 'property', 'floorplan', 'floorplans', 'floor plan', 'floor plans' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Floorplans', 'propertyhive' ),
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
				'selector' => '{{WRAPPER}} .floorplans h4',
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
					'{{WRAPPER}} .floorplans h4' => 'color: {{VALUE}}',
				],
				'condition' => [
		            'show_title' => 'yes'
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

		if ( isset($settings['show_title']) && $settings['show_title'] != 'yes' )
		{
?>
<style type="text/css">
.floorplans h4 { display:none; }
</style>
<?php
		}

		if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        {
            $floorplan_urls = $property->_floorplan_urls;
            if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
            {
            	echo '<div class="floorplans">';

                    echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>';

	                foreach ($floorplan_urls as $floorplan)
	                {
	                	echo '<a href="' . $floorplan['url'] . '" data-fancybox="floorplans" rel="nofollow"><img src="' . $floorplan['url'] . '" alt=""></a>';
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

					echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>';

					foreach ( $floorplan_attachment_ids as $attachment_id )
					{
						if ( wp_attachment_is_image($attachment_id) )
	                    {
							echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="floorplans" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
						}
						else
						{
							echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View Floorplan', 'propertyhive' ) . '</a>';
						}
					}

				echo '</div>';
			}
		}

	}

}