<?php
/**
 * PropertyHive Get Involved Settings
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Get_Involved' ) ) :

/**
 * PH_Settings_Get_Involved
 */
class PH_Settings_Get_Involved extends PH_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id    = 'getinvolved';
        $this->label = __( 'Get Involved', 'propertyhive' );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_admin_field_getinvolved', array( $this, 'getinvolved_setting' ) );
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

        return apply_filters( 'propertyhive_get_involved_settings', array(

            array(
                'type'      => 'getinvolved',
            ),

            array( 'type' => 'sectionend', 'id' => 'get_involved_options')

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

    private function draw_get_involved_details($heading, $description)
    {
        ?>
        <div class="details">
            <h1><?php echo $heading; ?></h1>
            <p><?php echo $description; ?></p>
        </div>
        <?php
    }

    /**
     * Output boxes showing ways to get involved
     *
     * @access public
     * @return void
     */
    public function getinvolved_setting()
    {
        ?>
            <style type="text/css">

                .get-involved ul { list-style-type:none; margin:0; padding:0; }
                .get-involved ul li { float:left; width:40%; padding:0 10px; margin-bottom:25px; box-sizing:border-box; }
                .get-involved ul li:nth-child(4n+1) { clear:left; }
                .get-involved ul li .padding { background:#FFF; padding:15px; box-sizing:border-box; border:1px solid #CCC; box-shadow:0px 0px 9px 0px rgba(0,0,0,0.2); -webkit-box-shadow:0px 0px 9px 0px rgba(0,0,0,0.2); }
                .get-involved ul li .thumbnail { text-align:center; padding-top:8px; }


                .get-involved a { text-decoration:none; color: black; }
                .get-involved h1 { padding-top:0; font-weight:bold; }
                .get-involved .padding .details p { font-size:1.2em; }
                .get-involved .intro-text { width:81%; font-size:1.2em; }
                .get-involved img { max-width:100%; }

                @media (max-width:1450px) {
                    .get-involved ul li { width:33.3333%; }
                    .get-involved ul li:nth-child(4n+1) { clear:none; }
                    .get-involved ul li:nth-child(3n+1) { clear:left; }
                }

                @media (max-width:1024px) {
                    .get-involved ul li { width:50%; }
                    .get-involved ul li:nth-child(3n+1) { clear:none; }
                    .get-involved ul li:nth-child(2n+1) { clear:left; }
                }

                @media (max-width:550px) {
                    .get-involved ul li { width:100%; padding:0; }
                    .get-involved ul li:nth-child(3n+1) { clear:none; }
                    .get-involved ul li:nth-child(2n+1) { clear:left; }
                }

            </style>
            <table class="form-table">
            <tr>
                <td class="get-involved">
                    <p class="intro-text">
                        Our objective since launching Property Hive back in 2015 has always been to make it easy for developers and agents alike to get involved and contribute to allow us to make an estate agency WordPress plugin that everyone can benefit from.<br><br>
                        Below you'll find just a few ways in which you can get involved, regardless of your technical background.
                    </p>
                </td>
            </tr>
            <tr>
                <td class="get-involved">
                    <ul>
                        <li>
                            <a href="https://trello.com/b/jb7bjB6j/property-hive-roadmap" target="_blank">
                                <div class="padding">
                                    <?php $this->draw_get_involved_details('Public Feature Roadmap', 'See what features are coming up and currently in progress. Vote on features and submit your own ideas.'); ?>
                                    <div class="thumbnail">
                                        <img src="../wp-content/uploads/2020/07/trello.png" alt="Public Feature Roadmap">
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="https://github.com/propertyhive/WP-Property-Hive" target="_blank">
                                <div class="padding">
                                    <?php $this->draw_get_involved_details('Open Source Codebase', 'Being open source, you can develop your own Property Hive features and push them back for everybody to benefit from.'); ?>
                                    <div class="thumbnail">
                                        <img src="../wp-content/uploads/2020/07/github.png" alt="Open Source Codebase">
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="https://wp-property-hive.com/extend-property-hive-sample-add-on/" target="_blank">
                                <div class="padding">
                                    <?php $this->draw_get_involved_details('Build Your Own Add On', 'Get your creative juices flowing and build your own add on. We\'ve even got a skeleton add on available to get you started.'); ?>
                                    <div class="thumbnail">
                                        <img src="../wp-content/uploads/2020/07/add-ons.png" alt="Build Your Own Add On">
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="padding">
                                <?php $this->draw_get_involved_details('Sign Up To Our Newsletter', 'Ensure you\'re always in the know and get notified of new releases by signing up to our newsletter.'); ?>
                                <br>
                                <!-- Begin Mailchimp Signup Form -->
                                <link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
                                <style type="text/css">
                                    #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
                                    /* Add your own Mailchimp form style overrides in your site stylesheet or in this style block.
                                    We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
                                </style>
                                <div id="mc_embed_signup">
                                <form action="https://wp-property-hive.us9.list-manage.com/subscribe/post?u=92cc489fc12939370f2b630b4&amp;id=58f44dd68b" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                                <div id="mc_embed_signup_scroll">
                                <div class="mc-field-group">
                                    <label for="mce-EMAIL">Email Address</label>
                                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                                </div>
                                    <div id="mce-responses" class="clear">
                                        <div class="response" id="mce-error-response" style="display:none"></div>
                                        <div class="response" id="mce-success-response" style="display:none"></div>
                                    </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_92cc489fc12939370f2b630b4_58f44dd68b" tabindex="-1" value=""></div>
                                    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                                    </div>
                                </form>
                                </div>
                                <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                                <!--End mc_embed_signup-->
                            </div>
                        </li>
                    </ul>
                </td>
            </tr>
        <?php
    }
}

endif;

return new PH_Settings_Get_Involved();
