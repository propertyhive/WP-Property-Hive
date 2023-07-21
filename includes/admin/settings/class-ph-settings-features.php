<?php
/**
 * PropertyHive Features Settings
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Features' ) ) :

/**
 * PH_Settings_Features
 */
class PH_Settings_Features extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'features';
		$this->label = __( 'Features', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 5 );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_admin_field_pro_features', array( $this, 'pro_features_setting' ) );
	}

    public function get_settings() {

        $settings = array(

            array( 'title' => __( 'Active Features', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'pro_active_features_settings' )

        );

        $settings[] = array(
            'type' => 'pro_features',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'pro_active_features_settings');

        return $settings;
    }

    public function pro_features_setting()
    {
        $features = get_ph_pro_features();

        echo '<div class="pro-feature-settings">';

        // filters

        // list of features
        echo '<ul>';
        foreach ( $features as $feature )
        {
            $feature_status = false;

            if ( is_dir( WP_PLUGIN_DIR . '/' . $feature['slug'] ) )
            {
                $feature_status = 'installed';
            }
            if ( is_plugin_active( $feature['plugin'] ) ) 
            {
                $feature_status = 'active';
            }

            echo '<li>
                <div class="inner">
                    <h3>' . ( ( isset($feature['icon']) && !empty($feature['icon']) ) ? '<span class="dashicons ' . $feature['icon'] . '"></span> ' : '' ) . $feature['name'] . '</h3>' . 
                    ( ( isset($feature['pro']) && $feature['pro'] === true ) ? '<span class="pro">PRO</span>' : '' ) . 
                    ( ( isset($feature['description']) && !empty($feature['description']) ) ? '<p>' . $feature['description'] . '</p>' : '' ) . '
                    <p>
                    ' . ( ( isset($feature['url']) && !empty($feature['url']) ) ? '<a href="' . $feature['url'] . '" target="_blank">' . __( 'Read More', 'propertyhive' ) . '</a>' : '' ) . 
                    ( ( isset($feature['docs_url']) && !empty($feature['docs_url']) ) ? '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . $feature['docs_url'] . '" target="_blank">' . __( 'Docs', 'propertyhive' ) . '</a>' : '' ) . 
                    '</p>';

                
                if ( $feature_status == 'active' )
                {
                    $transient = get_site_transient( 'update_plugins' );
                    if ( isset($transient->response) && is_array($transient->response) && isset($transient->response[$feature['plugin']]) && isset($transient->response[$feature['plugin']]->new_version) )
                    {
                        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $feature['plugin'] );

                        if ( version_compare($plugin_data['Version'], $transient->response[$feature['plugin']]->new_version, '<') )
                        {
                            echo '<div style="float:right"><a href="' . admin_url('update-core.php') . '" style="text-decoration:none"><span class="dashicons dashicons-update"></span> Update available</a></div>';
                        }
                    }
                }

                echo '<label class="switch">
                  <input type="checkbox" name="active_plugins[]" value="' . $feature['slug'] . '"' . ( $feature_status == 'active' ? ' checked' : '' ) . '>
                  <span class="slider round"></span>
                </label>';

                echo '<div class="loading"><img src="' . PH()->plugin_url() . '/assets/images/admin/loading.gif" alt=""></div>';

                echo '</div>';
            echo '</li>';
        }
        echo '</ul><div style="clear:both"></div>';

        echo '</div>';
    }

	/**
     * Output the settings
     */
    public function output() {
    	global $current_section, $hide_save_button;

        $hide_save_button = true;
        $settings = $this->get_settings(); 

        PH_Admin_Settings::output_fields( $settings );
    }

}

endif;

return new PH_Settings_Features();
