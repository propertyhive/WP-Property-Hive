<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PropertyHive Admin.
 *
 * @class       PH_Admin 
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */
class PH_Admin {

    /**
     * Constructor
     */
    public function __construct() 
    {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'current_screen', array( $this, 'conditional_includes' ) );
        add_action( 'current_screen', array( $this, 'disable_propertyhive_meta_box_dragging' ) );
        add_action( 'current_screen', array( $this, 'remove_propertyhive_meta_boxes_from_screen_options' ) );
        add_action( 'admin_notices', array( $this, 'review_admin_notices') );
        add_action( 'admin_init', array( $this, 'prevent_access_to_admin' ) );
        add_action( 'admin_init', array( $this, 'view_email' ) );
        add_action( 'admin_init', array( $this, 'preview_emails' ) );
    }
    
    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        // Functions
        include_once( 'ph-admin-functions.php' );
        include_once( 'ph-meta-box-functions.php' );

        // Classes
        include_once( 'class-ph-admin-post-types.php' );
        //include_once( 'class-ph-admin-taxonomies.php' );

        // Classes we only need if the ajax is not-ajax
        if ( ! is_ajax() ) {
            include( 'class-ph-admin-menus.php' );
            include( 'class-ph-admin-assets.php' );
        }
    }

    /**
     * Include admin files conditionally.
     */
    public function conditional_includes() {
        if ( ! $screen = get_current_screen() ) {
            return;
        }

        switch ( $screen->id ) {
            case 'dashboard' :
                include( 'class-ph-admin-dashboard.php' );
            break;
        }
    }

    /**
     * Include admin files conditionally
     */
    public function disable_propertyhive_meta_box_dragging()
    {
        $screen = get_current_screen();
        
        if ( in_array( $screen->id, ph_get_screen_ids() ) ) 
        {
            //wp_deregister_script('postbox');
        }
    }
    
    /**
     * Remove PropertyHive meta boxes
     */
    public function remove_propertyhive_meta_boxes_from_screen_options()
    {
        global $wp_meta_boxes;
        
        $screen = get_current_screen();
        
        if ( in_array( $screen->id, array( 'property' ) ) ) 
        {
            //wp_deregister_script('postbox');
        }
    }

    public function review_admin_notices()
    {
        if ( current_user_can( 'manage_options' ) )
        {
            $propertyhive_review_prompt_due_timestamp = get_option( 'propertyhive_review_prompt_due_timestamp', 0 );
            if ( $propertyhive_review_prompt_due_timestamp != '' && $propertyhive_review_prompt_due_timestamp != 0 )
            {
                if ( $propertyhive_review_prompt_due_timestamp < time() )
                {
                    echo "<div class=\"notice notice-info is-dismissible\" id=\"ph_leave_review_admin_notice\">
                        <p>
                            " . __( '<strong>Finding Property Hive useful?</strong> Please take a minute to <a href="https://wordpress.org/support/plugin/propertyhive/reviews/?filter=5#new-post" target="_blank">leave us a ★★★★★ review</a>', 'propertyhive' ) . "
                        </p>
                        <p>
                            <a href=\"https://wordpress.org/support/plugin/propertyhive/reviews/?filter=5#new-post\" target=\"_blank\" class=\"button-primary\">Leave a Review</a>
                            <a href=\"\" class=\"button\">No Thanks</a>
                        </p>
                    </div>";

                    update_option( 'propertyhive_review_prompt_due_timestamp', 0 );
                }
            }
        }
    }

    public function prevent_access_to_admin()
    {
        global $current_user;

        $user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);

        // Check role, but also AJAX as request to admin-ajax.php will still need to be made
        if ( !defined( 'DOING_AJAX' ) && $user_role === 'property_hive_contact' )
        {
            exit( wp_redirect( home_url( '/' ) ) );
        }
    }

    /**
     * View previously sent email
     *
     * @return string
     */
    public function view_email() {

        global $wpdb;

        if ( isset( $_GET['view_propertyhive_email'] ) ) 
        {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'view-email' ) ) 
            {
                die( 'Security check' );
            }

            if ( isset( $_GET['email_id'] ) )
            {
                $email_log = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "ph_email_log WHERE email_id = '" . esc_sql( $_GET['email_id'] ) . "'" );
                if ( null !== $email_log ) 
                {
                    echo apply_filters( 'propertyhive_mail_content', PH()->email->style_inline( PH()->email->wrap_message( $email_log->body ) ) );
                    
                }
                else
                {
                    die("Email not found");
                }
            }
            
            exit;
        }
    }

    /**
     * Preview email template.
     *
     * @return string
     */
    public function preview_emails() {
        if ( isset( $_GET['preview_propertyhive_email'] ) ) 
        {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-matching-properties' ) ) 
            {
                die( 'Security check' );
            }

            // get the preview email content
            $email_property_ids = explode(",", $_POST['email_property_id']);

            $body = stripslashes($_POST['body']);

            $body = str_replace("[contact_name]", get_the_title($_GET['contact_id']), $body);
            $body = str_replace("[property_count]", count($email_property_ids) . ' propert' . ( ( count($email_property_ids) != 1 ) ? 'ies' : 'y' ), $body);

            if ( strpos($body, '[properties]') !== FALSE )
            {
                ob_start();
                
                if ( !empty($email_property_ids) )
                {
                    foreach ( $email_property_ids as $email_property_id )
                    {
                        $property = new PH_Property((int)$email_property_id);
                        ph_get_template( 'emails/applicant-match-property.php', array( 'property' => $property ) );
                    }
                }
                $body = str_replace("[properties]", ob_get_clean(), $body);
            }

            // create a new email
            $email = new PH_Emails();

            // wrap the content with the email template and then add styles
            $message = apply_filters( 'propertyhive_mail_content', $email->style_inline( $email->wrap_message( $body ) ) );

            // print the preview email
            echo $message;
            exit;
        }
    }
}

return new PH_Admin();