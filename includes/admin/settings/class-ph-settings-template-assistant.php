<?php
/**
 * PropertyHive General Settings
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Template_Assistant' ) ) :

/**
 * PH_Settings_Template_Assistant
 */
class PH_Settings_Template_Assistant extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'template-assistant';
		$this->label = __( 'Template Assistant', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 17 );
		//add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
	}

	
	/**
	 * Get general settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		
        global $hide_save_button;
        
        $hide_save_button = true;

		$settings = array(

			array( 'title' => __( '', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'template_assistant_moved_options' ),
        
        );

        $html = '
        <style>
            .ph-ta-merge-notice { margin-bottom:25px; display:flex; gap:40px; background:#f0f6fc; border:1px solid #9ec2e6; padding:30px; align-items:center }
            .ph-ta-merge-notice > div:first-child { flex:0 0 210px; }
            .ph-ta-merge-notice > div:last-child { flex:1; min-width:0; }

            .ph-ta-merge-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:22px; }
            .ph-ta-merge-grid > div { display:flex; gap:20px; background:#FFF; padding:30px; border:1px solid #AAA }
            .ph-ta-merge-grid > div img { flex:0 0 50px; max-width:50px; height:50px; }
            .ph-ta-merge-grid > div .feature-card-content { flex:1; min-width:0; }
            .ph-ta-merge-grid > div h3 { margin-top:0; margin-bottom:0.8em }
        </style>

        <div class="ph-ta-merge-notice">

            <div><img alt="" style="max-width:100%" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbEAAACDCAYAAAADFSWjAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAydpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDEwLjAtYzAwMCA3OS5kMjBlNDY2MzAsIDIwMjUvMTIvMDktMDI6MTE6MjMgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCAyNy40IChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpGNEM5MjkwOTI0RjQxMUYxOTJCQkZFOEREQzA2QjhBNCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpGNEM5MjkwQTI0RjQxMUYxOTJCQkZFOEREQzA2QjhBNCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkY0QzkyOTA3MjRGNDExRjE5MkJCRkU4RERDMDZCOEE0IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkY0QzkyOTA4MjRGNDExRjE5MkJCRkU4RERDMDZCOEE0Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+NSJ5OQAAHd5JREFUeNrs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" /></div>
            
            <div>
                <h2 style="margin-top:0; margin-bottom:0.8em">Template Assistant functionality has moved</h2>
                <p>
                    We\'re simplifying things a little by moving the Template Assistant functionality to new locations within the core plugin.
                    <br><br>
                    <a href="https://wp-property-hive.com/template-assistant-is-now-part-of-property-hive-core-plugin" target="_blank" class="button button-primary">' . __('Read more', 'propertyhive' ) . '</a>
                </p>
            </div>

        </div>

        <div class="ph-ta-merge-grid">

            <div>
                <img alt="Search Results" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAADz0lEQVR4Aeybt67UQBSGB4RoCAXQUMETQEUQvACpBZEkUkmDBFSEglABEs0tSRJJ0JJeAESo4AmgogEKQoOQ4P+GOb42PrtXK6oz69X8nrHPWel8v2e9ttc7P035azBgyidAGmbAiBmwXtvvSu+l79LvoKJ2GGCBSRjd5s0AEl8pbY+0SlokRW3UDgMsMMHWYfEMONbKeKPxM+lhUD1V3TCoy63Nljd4BmzKkZR4I45t1fquoNqmumGARcNkbIyzPANW5EhKn0pfQ/e5QBhbWU3utwCfGxI4gNDXoG8FwtjKqm9AE5yGgfcRmAbuhtEz4FeJLix9DZ2xGFvD5BlgB7/eAaN5V7yBsRhbQ+AZwJkTCZu1OCUdknYG1WHVDQMsGiZjS/byDJixoPoL0nXpQVBdU90wqMttJi9bC8+A24qfl+yrQ8PwDRaYYOvAeAaQcFaLldJGaYcU9UyQ2mGABSahdNsoA8j6oQUXEI/VR70WoHYYYBFGv40zoJ9d4ZbBgAp36kRIwwyYyK4Kk4cZMGan7lXsknRTinomSO0wwCKMfvNmwFKlvZbuSCekA1LUawFqhwEWmGATzmzzDDiu8DqptgYTbB0uz4DdJYP7aGs1xrV56iOK2mGARQjJ2BhneQbYtfNbZbyTuJBQF7JROwywAGBsjLM8A5blSErmWlkN3RmLsTUwngFNsIbBXAyDAXM5VHvcmwFfCvTy0tfQGYuxNUyeAXbnlK+PNcpcIkVt1A4DLDAYG+Msz4D7OZISrvH18VXrUZ8PoHYYYBFGMjbGWZ4BVxSxX1M1rKbBBFsHyDMA1/hJeZ8yL0u3pKj3BKkdBlhggk04s80zwKI8VnJSKwelqHeFqR0GWITRb+MM6GdXuGUwoMKdOhHSMAMmsqvC5GEGjNmpPFC0QfHtUtR7gtQOAyzC6LdRM+CcUj9KL6VHUpi7wv/USu0wwAKTwt3mGbBfKWckLiTUVdFggQm2DpBnwNFWxmmNj0hRzwR5RAYGIeTWZssbPANW50hKz9VflHhEJuq1wA3VDwMsGiZjS/byDLA7p71rZ3tTwN5YjK1B8AxYUKI/S19DZyzG1jB5BjTBaRgMBjh72R4o4qvDCYfctLhUbWxlNbmPy9sBw+6jNcmBB3bwM7YGxfsIvChRfk3lJ+UnWo96JshfZmCARRjJ2BhneQZczZG/C97IX2aiXgtsEQYM6nJrs+UNngE4xgXEPWV8kHqfG22L0qgdBlhggq1Tu2cACSTyWAlnThxA5mljRFE7DLDAJIxuG2VAN6vitcGA6Dv3f+v/AwAA///P0hEFAAAABklEQVQDAGPzRp/u4JhcAAAAAElFTkSuQmCC" />
                <div class="feature-card-content">
                    <h3>Search Results</h3>
                    <p>Manage various aspects of the search results layout.</p>
                    <p>' . __( 'Now found in \'Property Hive > Settings > \'', 'propertyhive') . '</p>
                    <p style="margin-top:12px;"><a href="#" class="button-primary">Take me there</a></p>
                </div>
            </div>

            <div>
                <img alt="" src="data:image/png;base64,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" />
                <div class="feature-card-content">
                    <h3>Search Forms</h3>
                    <p>Control the fields that appear in search forms.</p>
                    <p>' . __( 'Now found in \'Property Hive > Settings > \'', 'propertyhive') . '</p>
                    <p style="margin-top:12px;"><a href="#" class="button-primary">Take me there</a></p>
                </div>
            </div>

            <div>
                <img alt="" src="data:image/png;base64,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" />
                <div class="feature-card-content">
                    <h3>Flags</h3>
                    <p>Display availability and marketing flags over thumbnails.</p>
                    <p>' . __( 'Now found in \'Property Hive > Settings > \'', 'propertyhive') . '</p>
                    <p style="margin-top:12px;"><a href="#" class="button-primary">Take me there</a></p>
                </div>
            </div>

            <div>
                <img alt="" src="data:image/png;base64,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" />
                <div class="feature-card-content">
                    <h3>Additional Fields</h3>
                    <p>Add extra fields to records to store additional information.</p>
                    <p>' . __( 'Now found in \'Property Hive > Settings > \'', 'propertyhive') . '</p>
                    <p style="margin-top:12px;"><a href="#" class="button-primary">Take me there</a></p>
                </div>
            </div>

            <div>
                <img alt="" src="data:image/png;base64,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" />
                <div class="feature-card-content">
                    <h3>Text Substitution</h3>
                    <p>Swap text or labels through the frontend and backend.</p>
                    <p>' . __( 'Now found in \'Property Hive > Settings > \'', 'propertyhive') . '</p>
                    <p style="margin-top:12px;"><a href="#" class="button-primary">Take me there</a></p>
                </div>
            </div>

        </div>
        <br>
        <p style="text-align:center"><em>This page and settings tab will be removed completely in a forthcoming version.</em></p>';

        $settings[] = array(
            'type'          => 'html',
            'title'         => '',
            'html'          => $html,
            'full_width'    => true
        );
            
		$settings[] = array( 'type' => 'sectionend', 'id' => 'template_assistant_moved_options');

        return $settings;
	}

	/**
     * Output the settings
     */
    public function output() {
        $settings = $this->get_settings();
        PH_Admin_Settings::output_fields( $settings );
    }

}

endif;

return new PH_Settings_Template_Assistant();
