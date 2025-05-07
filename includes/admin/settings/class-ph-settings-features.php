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

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
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
        $categories = array(
            '' => 'All',
            'import' => 'Property Import',
            'export' => 'Portal Feeds',
            'website' => 'Website Tools',
            'crm' => 'CRM Tools',
            'data-bridges' => 'Data Bridges',
            'free' => 'Free',
        );

        $selected_category = '';
        if ( isset($_GET['profilter']) && array_key_exists(sanitize_text_field($_GET['profilter']), $categories) )
        {
            $selected_category = sanitize_text_field($_GET['profilter']);
        }

        echo '<div class="pro-filters">';
            echo '<ul>';
            $i = 0;
            foreach ( $categories as $key => $value )
            {
                echo '<li ' . ( $selected_category == $key ? ' class="active"' : '' ) . '><a href="" data-filter="' . esc_attr($key) . '">' . esc_html($value) . '</a></li>';
                ++$i;
            }
        echo '</ul>';
        echo '</div>';

        // list of features
        echo '<div class="pro-features">';
        echo '<ul>';
        foreach ( $features as $feature )
        {
            $slug = explode("/", $feature['wordpress_plugin_file']);
            $slug = $slug[0];

            $feature_status = false;

            if ( is_dir( WP_PLUGIN_DIR . '/' . $slug ) )
            {
                $feature_status = 'installed';
            }
            if ( is_plugin_active( $feature['wordpress_plugin_file'] ) ) 
            {
                $feature_status = 'active';
            }

            echo '<li style="visibility:hidden" class="';
            if ( isset($feature['categories']) && is_array($feature['categories']) && !empty($feature['categories']) )
            {
                echo esc_html(implode(" ", $feature['categories']));
            }

            $pro = false;
            $plans = (isset($feature['plans']) & is_array($feature['plans'])) ? $feature['plans'] : array();
            if ( !in_array('free', $plans) )
            {
                $pro = true;
            }

            $can_use = true;
            if ( $pro && $feature_status == 'active' && apply_filters( 'propertyhive_add_on_can_be_used', true, $slug ) === FALSE )
            {
                $can_use = false;
            }

            echo '">
                <div class="inner"' . ( !$can_use ? ' style="border:1px solid #900"' : '' ) . '>
                    <h3>' . ( ( isset($feature['dashicon']) && !empty($feature['dashicon']) ) ? '<span class="dashicons ' . esc_attr($feature['dashicon']) . '"></span> ' : '' ) . esc_html($feature['name']) . '</h3>' . 
                    ( $pro ? '<span class="pro"><span><a href="https://wp-property-hive.com/pricing/?src=plugin-feature-settings" target="_blank">PRO</a></span></span>' : '' ) . 
                    ( !$pro ? '<span class="free"><span>FREE</span></span>' : '' ) . '
                ';

                $links = array();
                if ( isset($feature['description']) && !empty($feature['description']) )
                {
                    $links[] = '<span style="color:#999;" class="help_tip" data-tip="' . esc_attr($feature['description']) . '"><span class="dashicons dashicons-info-outline"></span></span>';
                }
                if ( isset($feature['url']) && !empty($feature['url']) )
                {
                    $links[] = '<a href="' . esc_url($feature['url']) . '" target="_blank" style="text-decoration:none">' . esc_html(__( 'Details', 'propertyhive' )) . '</a>';
                }
                if ( isset($feature['docs_url']) && !empty($feature['docs_url']) )
                {
                    $links[] = '<a href="' . esc_url($feature['docs_url']) . '" target="_blank" style="text-decoration:none">' . esc_html(__( 'Docs', 'propertyhive' )) . '</a>';
                }
                if ( $feature_status == 'active' )
                {
                    $transient = get_site_transient( 'update_plugins' );
                    if ( isset($transient->response) && is_array($transient->response) && isset($transient->response[$feature['wordpress_plugin_file']]) && isset($transient->response[$feature['wordpress_plugin_file']]->new_version) )
                    {
                        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $feature['wordpress_plugin_file'] );

                        if ( version_compare($plugin_data['Version'], $transient->response[$feature['wordpress_plugin_file']]->new_version, '<') )
                        {
                            $links[] = '<a href="' . esc_url(admin_url('plugins.php?plugin_status=upgrade')) . '" style="text-decoration:none"><span class="dashicons dashicons-update"></span> ' . esc_html(__( 'Update', 'propertyhive' )) . '</a>';
                        }
                    }
                }

                echo '<div style="float:right; padding-top:6px;">';
                echo implode("&nbsp;&nbsp;|&nbsp;&nbsp;", $links);
                echo '</div>';

                echo '<label class="switch">
                  <input type="checkbox" name="active_plugins[]" value="' . esc_attr($slug) . '"' . ( $feature_status == 'active' ? ' checked' : '' ) . '>
                  <span class="slider round"></span>
                </label>';

                echo '<div class="loading"><img src="' . esc_url(PH()->plugin_url() . '/assets/images/admin/loading.gif') . '" alt=""></div>';

                if ( !$can_use )
                {
                    echo '<div style="color:#900; font-size:0.9; margin-top:10px;">Disabled due to <a href="' . esc_url(admin_url('admin.php?page=ph-settings&tab=licensekey')) . '" style="color:inherit">invalid license</a></div>';
                }

                echo '</div>';
            echo '</li>';
        }
        echo '</ul><div style="clear:both"></div></div>';

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
