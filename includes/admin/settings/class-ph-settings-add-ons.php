<?php
/**
 * PropertyHive Add-Ons
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Add_Ons' ) ) :

/**
 * PH_Settings_Add_Ons
 */
class PH_Settings_Add_Ons extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'addons';
		$this->label = __( 'Add Ons', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_admin_field_addons', array( $this, 'addons_setting' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		
        global $hide_save_button;
            
        $hide_save_button = TRUE;
            
		return apply_filters( 'propertyhive_add_on_settings', array(

			array( 'title' => __( 'Add Ons', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'add_on_options' ),
            
            array(
                'type'      => 'addons',
            ),
            
			array( 'type' => 'sectionend', 'id' => 'add_on_options')

		) ); // End add on settings
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
     * Output list of offices
     *
     * @access public
     * @return void
     */
    public function addons_setting() {
        global $post;
        
        $add_ons = @file_get_contents('http://wp-property-hive.com/add-ons-json.php');
?>
<tr>
    <td class="add-ons">

        <style type="text/css">

            .add-ons ul { list-style-type:none; margin:0; padding:0; }
            .add-ons ul li { float:left; width:25%; padding:0 10px; margin-bottom:25px; box-sizing:border-box; }
            .add-ons ul li:nth-child(4n+1) { clear:left; }
            .add-ons ul li .padding { background:#FFF; padding:15px; box-sizing:border-box; border:1px solid #CCC; box-shadow:0px 0px 9px 0px rgba(0,0,0,0.2); -webkit-box-shadow:0px 0px 9px 0px rgba(0,0,0,0.2); }
            .add-ons ul li .thumbnail { text-align:center; }

            @media (max-width:1450px) {
                .add-ons ul li { width:33.3333%; }
                .add-ons ul li:nth-child(4n+1) { clear:none; }
                .add-ons ul li:nth-child(3n+1) { clear:left; }
            }

            @media (max-width:1024px) {
                .add-ons ul li { width:50%; }
                .add-ons ul li:nth-child(3n+1) { clear:none; }
                .add-ons ul li:nth-child(2n+1) { clear:left; }
            }

            @media (max-width:550px) {
                .add-ons ul li { width:100%; padding:0; }
                .add-ons ul li:nth-child(3n+1) { clear:none; }
                .add-ons ul li:nth-child(2n+1) { clear:left; }
            }

        </style>
        
        <?php
            if ($add_ons !== FALSE && $add_ons !== '')
            {
                $add_ons = json_decode($add_ons);
                
                if ($add_ons !== FALSE && !empty($add_ons))
                {
                    echo '<ul>';
                    
                    $i = 0;
                    foreach ($add_ons as $add_on)
                    {
                        echo '<li>
                            <div class="padding">
                            <div class="thumbnail">';
                        if (isset($add_on->image) && $add_on->image != '')
                        {
                            echo '<a href="' . $add_on->url . '" target="_blank"><img src="' . $add_on->image . '" alt="' . $add_on->name . '"></a>';
                        }
                        echo '</div>
                        <div class="details">
                            <h3><a href="' . $add_on->url . '" target="_blank">' . $add_on->name . '</a></h3>
                            <p>' . $add_on->description . '</p>
                            <br>
                            <a href="' . $add_on->url . '" target="_blank" class="button">'. __('View Add On', 'propertyhive') .'</a>
                        </div>
                        </div>
                        </li>';
                        
                        ++$i;
                    }
                    
                    echo '</ul>';
                }
                else
                {
                    echo '<p>'. __('No add ons are currently available for Property Hive. As add ons become available they will appear here. Please check back soon.', 'propertyhive') . '</p>';
                }
            }
            else
            {
                echo '<p>'. __('Unable to retrieve list of add-ons. Please visit the Property Hive add ons page to view a full list of add ons available.', 'propertyhive') . '</p>';
                
                echo '<br><p><a href="http://wp-property-hive.com/add-ons/" class="button button-primary">'. __('Browse All Add Ons', 'propertyhive') . '</a></p>';
            }
        ?>
        
    </td>
</tr>
<?php
    }
}

endif;

return new PH_Settings_Add_Ons();
