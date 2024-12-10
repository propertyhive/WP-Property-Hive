<?php
/**
 * PropertyHive Office Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Settings_Offices' ) ) :

/**
 * PH_Settings_Offices
 */
class PH_Settings_Offices extends PH_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'offices';
        $this->label = __( 'Offices', 'propertyhive' );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 10 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
        add_action( 'propertyhive_admin_field_offices', array( $this, 'offices_setting' ) );
    }
    
    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        
        return apply_filters( 'propertyhive_office_settings', array(

            array( 'title' => __( 'Offices', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'offices_options' ),
            
            array(
                'type'      => 'offices',
            ),
            
            array( 'type' => 'sectionend', 'id' => 'offices_options' ),
        ));
        
    }
    
    /**
     * Get add/edit office settings array
     *
     * @return array
     */
    public function get_office_settings() {
        
        global $current_section;
        
        $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];

        $args = array(

            array( 'title' => __( ( $current_section == 'add' ? 'Add New Office' : 'Edit Office Details' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'office_options' ),
            
            array(
                'title' => __( 'Office Name', 'propertyhive' ),
                'id'        => 'office_name',
                //'css'       => 'width:50px;',
                'default'   => get_the_title($current_id),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Address Line 1', 'propertyhive' ),
                'id'        => '_office_address_1',
                //'css'       => 'width:50px;',
                'default'   => get_post_meta($current_id, '_office_address_1', TRUE),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Address Line 2', 'propertyhive' ),
                'id'        => '_office_address_2',
                //'css'       => 'width:50px;',
                'default'   => get_post_meta($current_id, '_office_address_2', TRUE),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Address Line 3', 'propertyhive' ),
                'id'        => '_office_address_3',
                //'css'       => 'width:50px;',
                'default'   => get_post_meta($current_id, '_office_address_3', TRUE),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Address Line 4', 'propertyhive' ),
                'id'        => '_office_address_4',
                //'css'       => 'width:50px;',
                'default'   => get_post_meta($current_id, '_office_address_4', TRUE),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array(
                'title' => __( 'Postcode', 'propertyhive' ),
                'id'        => '_office_address_postcode',
                'css'       => 'width:85px;',
                'default'   => get_post_meta($current_id, '_office_address_postcode', TRUE),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),
            
            array( 'type' => 'sectionend', 'id' => 'office_options' ),
            
            array( 'title' => __( 'Contact Details', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'office_contact_options' ),
            
        );
        
        $departments = ph_get_departments();

        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $args[] = array(
                    'title' => sprintf( __( 'Telephone Number (%s)', 'propertyhive' ), $value ),
                    'id'        => '_office_telephone_number_' . str_replace("residential-", "", $key),
                    //'css'       => 'width:50px;',
                    'default'   => get_post_meta($current_id, '_office_telephone_number_' . str_replace("residential-", "", $key), TRUE),
                    'type'      => 'text',
                    'desc_tip'  =>  false,
                );
                
                $args[] = array(
                    'title' => sprintf( __( 'Email Address (%s)', 'propertyhive' ), $value ),
                    'id'        => '_office_email_address_' . str_replace("residential-", "", $key),
                    //'css'       => 'width:50px;',
                    'default'   => get_post_meta($current_id, '_office_email_address_' . str_replace("residential-", "", $key), TRUE),
                    'type'      => 'text',
                    'desc_tip'  =>  false,
                );
            }
        }
        
        $args[] = array( 'type' => 'sectionend', 'id' => 'office_contact_options' );

        $args[] = array( 'title' => __( 'Office Location', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'office_location_options' );

        $args[] = array(
            'title' => __( 'Latitude', 'propertyhive' ),
            'id'        => '_office_latitude',
            //'css'       => 'width:50px;',
            'default'   => get_post_meta($current_id, '_office_latitude', TRUE),
            'type'      => 'text',
            'desc_tip'  =>  false,
        );

        $args[] = array(
            'title' => __( 'Longitude', 'propertyhive' ),
            'id'        => '_office_longitude',
            //'css'       => 'width:50px;',
            'default'   => get_post_meta($current_id, '_office_longitude', TRUE),
            'type'      => 'text',
            'desc_tip'  =>  false,
        );

        $args[] = array( 'type' => 'sectionend', 'id' => 'office_location_options' );
        
        return apply_filters( 'propertyhive_office_details_settings', $args );
        
    }
    
    /**
     * Get delete office settings array
     *
     * @return array
     */
    public function get_office_delete() {
        
        global $save_button_text, $post;
        
        $save_button_text = __( 'Delete', 'propertyhive' );
        
        if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == 1 )
        {
            // A term has just been deleted
            global $hide_save_button, $show_cancel_button, $cancel_button_href;
            
            $hide_save_button = TRUE;
            $show_cancel_button = TRUE;
            $cancel_button_href = admin_url( 'admin.php?page=ph-settings&tab=offices' );
            
            $args = array();
                    
            $args[] = array( 'title' => __( 'Successfully Deleted Office', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'office_delete' );
            
            $args[] = array(
                'title'     => __( 'Office Deleted', 'propertyhive' ),
                'id'        => '',
                'html'      => __('Office deleted successfully', 'propertyhive' ) . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=offices' ) . '">' . __( 'Go Back', 'propertyhive' ) . '</a>',
                'type'      => 'html',
                'desc_tip'  =>  false,
            );
            
            $args[] = array( 'type' => 'sectionend', 'id' => 'office_delete' );
        }
        else
        {
            $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
            
            if ($current_id == '')
            {
                die("ID not passed");
            }
            else
            {
                $post_type = get_post_type( $current_id );
                
                if ( $post_type == 'office' )
                {                
                    $args = array();
                    
                    $args[] = array( 'title' => __( 'Delete Office', 'propertyhive' ) . ': ' . get_the_title( $current_id ), 'type' => 'title', 'desc' => '', 'id' => 'office_delete_options' );
                    
                    // Get number of properties assigned to this term
                    $query_args = array(
                        'post_type' => 'property',
                        'nopaging' => true,
                        'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                        'meta_query' => array(
                            array(
                                'key' => '_office_id',
                                'value' => $current_id,
                                'compare' => '='
                            )
                        )
                    );
                    $property_query = new WP_Query( $query_args );
                    
                    $num_properties = $property_query->found_posts;
                    
                    // Get number of applicants assigned to this term (future)
                    
                    if ($num_properties > 0)
                    {
                        $alternative_offices = array();
                        
                        $query_args = array(
                            'post_type' => 'office',
                            'nopaging' => true,
                            'post__not_in' => array( $current_id ),
                            'orderby' => 'title',
                            'order' => 'ASC'
                        );
                        $office_query = new WP_Query( $query_args );
                        
                        if ( $office_query->have_posts() )
                        {
                            while ( $office_query->have_posts() )
                            {
                                $office_query->the_post();
                                
                                $alternative_terms[$post->ID] = get_the_title();
                            }
                        } 
                        
                        // There are properties assigned to this term
                        $args[] = array(
                            'title' => __( 'Re-assign to', 'propertyhive' ),
                            'id'        => 'reassign_to',
                            'default'   => '',
                            'options'   => $alternative_terms,
                            'type'      => 'select',
                            'desc_tip'  =>  false,
                            'desc'      => __( 'There are properties that are assigned to the office being deleted. Which office should they be reassigned to?' , 'propertyhive' )
                        );
                    }
                    
                    $args[] = array(
                        'title' => __( 'Confirm removal?', 'propertyhive' ),
                        'id'        => 'confirm_removal',
                        'type'      => 'checkbox',
                        'desc_tip'  =>  false,
                    );
                    
                    $args[] = array( 'type' => 'sectionend', 'id' => 'office_delete_options' );
                }
                else
                {
                    die("Trying to delete a post that isn't an office.");
                }
            }
        }
        
        return apply_filters( 'propertyhive_office_delete_settings', $args );
        
    }
    
    /**
     * Output the settings
     */
    public function output() {
        global $current_section;
        
        if ( $current_section == 'add' ) {
            
            remove_action('propertyhive_admin_field_offices', array( $this, 'offices_setting' ));
            
            $settings = $this->get_office_settings();

            PH_Admin_Settings::output_fields( $settings );
            
            /*foreach ( $shipping_methods as $method ) {
                if ( strtolower( get_class( $method ) ) == strtolower( $current_section ) && $method->has_settings() ) {
                    $method->admin_options();
                    break;
                }
            }*/
        } elseif ( $current_section == 'edit' ) {            
            
            remove_action('propertyhive_admin_field_offices', array( $this, 'offices_setting' ));
            
            $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
            
            $settings = $this->get_office_settings();

            PH_Admin_Settings::output_fields( $settings );
       
        } elseif ( $current_section == 'delete' ) {            
            
            remove_action('propertyhive_admin_field_offices', array( $this, 'offices_setting' ));
            
            $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
            
            $settings = $this->get_office_delete();

            PH_Admin_Settings::output_fields( $settings );
       
        } else {
            $settings = $this->get_settings();

            PH_Admin_Settings::output_fields( $settings );
        }
    }
    
    /**
     * Output list of offices
     *
     * @access public
     * @return void
     */
    public function offices_setting() {
        global $post;
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=offices&section=add' ); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Office', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php echo esc_html(__( 'Offices', 'propertyhive' )); ?></th>
            <td class="forminp">
                <table class="ph_offices widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="primary"><?php echo esc_html(__( 'Primary', 'propertyhive' )); ?></th>
                            <th class="name"><?php echo esc_html(__( 'Name', 'propertyhive' )); ?></th>
                            <th class="address"><?php echo esc_html(__( 'Address', 'propertyhive' )); ?></th>
                            <th class="contact"><?php echo esc_html(__( 'Contact Details', 'propertyhive' )); ?></th>
                            <?php do_action( 'propertyhive_office_table_header_columns' ); ?>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $args = array(
                                'post_type' => 'office',
                                'nopaging' => true,
                                'orderby' => 'title',
                                'order' => 'ASC'
                            );
                            $office_query = new WP_Query($args);
                            
                            if ($office_query->have_posts())
                            {
                                $num_offices = $office_query->found_posts;
                                
                                while ($office_query->have_posts())
                                {
                                    $office_query->the_post();

                                    $address = '';
                                    $address_part = get_post_meta($post->ID, '_office_address_1', TRUE);
                                    $address .= $address_part . ', ';
                                    $address_part = get_post_meta($post->ID, '_office_address_2', TRUE);
                                    $address .= $address_part . ', ';
                                    $address_part = get_post_meta($post->ID, '_office_address_3', TRUE);
                                    $address .= $address_part . ', ';
                                    $address_part = get_post_meta($post->ID, '_office_address_4', TRUE);
                                    $address .= $address_part . '  ';
                                    $address_part = get_post_meta($post->ID, '_office_address_postcode', TRUE);
                                    $address .= $address_part;
                                    
                                    $contact_details = '';

                                    $departments = ph_get_departments();

                                    foreach ( $departments as $key => $value )
                                    {
                                        if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                                        {
                                            $contact_details .= 'T: ' . esc_html(get_post_meta($post->ID, '_office_telephone_number_' . str_replace("residential-", "", $key), TRUE)) . '<br>';
                                            $contact_details .= 'E: ' . esc_html(get_post_meta($post->ID, '_office_email_address_' . str_replace("residential-", "", $key), TRUE)) . '<br>';
                                        }
                                    }
                                    
                                    echo '<tr>
                                        <td width="1%" class="primary">
                                            <input type="radio" name="primary" value="' . esc_attr( $post->ID ) . '" ' . checked( get_post_meta($post->ID, 'primary', TRUE), '1', false ) . ' />
                                        </td>
                                        <td class="name">
                                            ' . esc_html(get_the_title()) . '
                                        </td>
                                        <td class="address">
                                            ' . esc_html($address) . '
                                        </td>
                                        <td class="contact">
                                            ' . $contact_details . '
                                        </td>';
                                    do_action( 'propertyhive_office_table_row_columns', get_the_ID() );
                                    echo '
                                        <td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=offices&section=edit&id=' . $post->ID ) . '">' . esc_html(__( 'Edit', 'propertyhive' )) . '</a>
                                            ';
                                    if ( $num_offices > 1 && get_post_meta($post->ID, 'primary', TRUE) != '1' )
                                    {
                                        echo '<a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=offices&section=delete&id=' . $post->ID ) . '">' . esc_html(__( 'Delete', 'propertyhive' )) . '</a>';    
                                    }
                                    echo '
                                    </td>
                                    </tr>';
                                }
                            }
                            wp_reset_postdata();
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=offices&section=add' ); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Office', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Save settings
     */
    public function save() {
        global $current_section, $post;

        if ( $current_section == 'add' ) {
            
            // TODO: Validate (check for blank fields, and that office name doest exist already)
            
            // Insert office
            $office_post = array(
              'post_title'    => ph_clean( $_POST['office_name'] ),
              'post_content'  => '',
              'post_status'   => 'publish',
              'post_type'     => 'office',
              'comment_status'    => 'closed',
              'ping_status'    => 'closed',
            );
            
            // Insert the post into the database
            $office_post_id = wp_insert_post( $office_post );
            
            // TODO: Check for errors returned from wp_insert_post()
            // TODO: Add meta information
            
            PH_Admin_Settings::add_message( __( 'Office added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=offices' ) . '">' . __( 'Return to offices', 'propertyhive' ) . '</a>' );
            
        } elseif ( $current_section == 'edit' ) {
            
            $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
       
            // TODO: Validate
            // TODO: Make sure this ID belongs to an office
            // TODO: Update slug?
       
            // Update office
            $office_post = array(
                'ID'           => $current_id,
                'post_title'   => ph_clean( $_POST['office_name'] )
            );
            
            wp_update_post( $office_post );
            
            $office_post_id = $current_id;
            
            // TODO: Check for errors returned from wp_update_post()
            // TODO: Update meta information
            
            PH_Admin_Settings::add_message( __( 'Office details updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=offices' ) . '">' . __( 'Return to offices', 'propertyhive' ) . '</a>' );
            
        } elseif ( $current_section == 'delete' ) {
            
            if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == '1' )
            {
                $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];
                
                // Get number of properties assigned to this term
                $query_args = array(
                    'post_type' => 'property',
                    'nopaging' => true,
                    'post_status' => array( 'pending', 'auto-draft', 'draft', 'private', 'publish', 'future', 'trash' ),
                    'meta_query' => array(
                        array(
                            'key' => '_office_id',
                            'value' => $current_id,
                            'compare' => '='
                        )
                    )
                );
                $property_query = new WP_Query( $query_args );
                
                if ( $property_query->have_posts() )
                {
                    if ( !isset($_POST['reassign_to']) || ( isset( $_POST['reassign_to'] ) && empty( $_POST['reassign_to'] ) ) )
                    {
                        die("Not assigning properties to new office. Please try again");
                    }
                    else
                    {
                        $post_type = get_post_type( (int)$_POST['reassign_to'] );
                
                        if ( $post_type != 'office' )
                        {
                            die("New office isn't of type office. It's of type: " . $post_type);
                        } 
                    }
                
                    while ( $property_query->have_posts() )
                    {
                        $property_query->the_post();
                        
                        update_post_meta( $post->ID, '_office_id', (int)$_POST['reassign_to'] );
                        
                        // TODO: Check for WP_ERROR
                    }
                }

                wp_delete_post( $current_id );
                
                // TODO: Check for WP_ERROR
            }
               
        }
        
        if ( $current_section != 'delete' )
        {
            if ( $current_section != 'add' && $current_section != 'edit' )
            {
                // Set all offices as not primary
                // Prevents multiple offices being set as primary
                $args = array(
                    'post_type' => 'office',
                    'nopaging' => true,
                    'orderby' => 'title',
                    'order' => 'ASC'
                );
                $office_query = new WP_Query($args);
                
                if ($office_query->have_posts())
                {
                    while ($office_query->have_posts())
                    {
                        $office_query->the_post();
                        
                        // Set selected office as primary
                        update_post_meta($post->ID, 'primary', '0');
                    }
                }
                
                wp_reset_postdata();
                
                // Set selected office as primary
                update_post_meta( (int)$_POST['primary'], 'primary', '1');
            }
            else
            {
                update_post_meta($office_post_id, '_office_address_1', ph_clean( $_POST['_office_address_1'] ));
                update_post_meta($office_post_id, '_office_address_2', ph_clean( $_POST['_office_address_2'] ));
                update_post_meta($office_post_id, '_office_address_3', ph_clean( $_POST['_office_address_3'] ));
                update_post_meta($office_post_id, '_office_address_4', ph_clean( $_POST['_office_address_4'] ));
                update_post_meta($office_post_id, '_office_address_postcode', ph_clean( $_POST['_office_address_postcode'] ));
                
                $departments = ph_get_departments();

                foreach ( $departments as $key => $value )
                {
                    if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                    {
                        update_post_meta($office_post_id, '_office_telephone_number_' . str_replace("residential-", "", $key), (isset($_POST['_office_telephone_number_' . str_replace("residential-", "", $key)])) ? ph_clean( $_POST['_office_telephone_number_' . str_replace("residential-", "", $key)] ) : '');
                        update_post_meta($office_post_id, '_office_email_address_' . str_replace("residential-", "", $key), (isset($_POST['_office_email_address_' . str_replace("residential-", "", $key)])) ? ph_clean( $_POST['_office_email_address_' . str_replace("residential-", "", $key)] ) : '');
                    }
                }
                
                update_post_meta($office_post_id, '_office_latitude', ph_clean( $_POST['_office_latitude'] ));
                update_post_meta($office_post_id, '_office_longitude', ph_clean( $_POST['_office_longitude'] ));

                do_action( 'propertyhive_save_office', $office_post_id );
            }
        }
    }
    
}

endif;

return new PH_Settings_Offices();
