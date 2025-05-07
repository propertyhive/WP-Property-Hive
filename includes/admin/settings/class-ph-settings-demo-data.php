<?php
/**
 * PropertyHive Demo Data
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Demo_Data' ) ) :

/**
 * PH_Settings_Demo_Data
 */
class PH_Settings_Demo_Data extends PH_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id    = 'demo_data';
        $this->label = __( 'Demo Data', 'propertyhive' );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_admin_field_demo_data', array( $this, 'demodata_setting' ) );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings()
    {
        global $hide_save_button;

        $hide_save_button = TRUE;

        return apply_filters( 'propertyhive_demo_data_settings', array(

            array(
                'type'      => 'demo_data',
            ),

            array( 'type' => 'sectionend', 'id' => 'demo_data_options')

        ) );
    }

    /**
     * Output the settings
     */
    public function output()
    {
        $settings = $this->get_settings();

        PH_Admin_Settings::output_fields( $settings );
    }

    /**
     * Output link to demo data
     *
     * @access public
     * @return void
     */
    public function demodata_setting()
    {
        ?>
        <style type="text/css">

            .demo-data .intro-text { width:81%; font-size:1.1em; }

        </style>
        <table class="form-table">
            <tr>
                <td class="demo-data">
                    <p class="intro-text">
                        To get an idea of how Property Hive works, if you're a new user you can quickly fill it with a set of demo data, including properties, applicants and more.
                        <br><br>
                        Simply activate our free Demo Data feature below to get started:
                    </p>
                    <br>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=features&profilter=free')); ?>" class="button button-primary">Activate Demo Data Feature</a>
                        &nbsp;
                        <a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=demo_data&hidetab=1')); ?>">Hide This Page</a>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }
}

endif;

return new PH_Settings_Demo_Data();
