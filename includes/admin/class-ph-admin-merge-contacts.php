    <?php
/**
 * PropertyHive Admin Merge Duplicate Contacts Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Merge_Contacts' ) ) :

/**
 * PH_Admin_Merge_Contacts
 */
class PH_Admin_Merge_Contacts {

    /**
     * Handles the display of contacts to merge, to allow the user to choose a primary contact
     *
     * @access public
     * @return void
     */
    public function output()
    {
        ?>
        <div class="wrap propertyhive">

        <h1><?php echo __('Merge Contacts', 'propertyhive'); ?></h1>

        <p>When you click 'Merge Contacts', all associated records will be moved onto the contact selected as the primary contact, and all other contacts will be deleted.</p>

        <p><strong>Note:</strong> This action is irreversible.</p>

        <?php

        if ( isset( $_GET['merge_ids'] )  && $_GET['merge_ids'] != '' )
        {
            $ids_to_merge = explode( '|', $_GET['merge_ids'] );
        }

        if ( isset( $ids_to_merge ) && count( $ids_to_merge ) > 1 )
        {
            foreach ( $ids_to_merge as $i => $contact_id )
            {
                echo '<div style="border:1px solid #CCC; padding:25px; background:#FFF; position:relative">';
                $contact = new PH_Contact( $contact_id );

                // Initialise array to hold information about each contact
                $contact_parts = array( '<h3 style="margin:0">' . ( trim($contact->post_title) != '' ? $contact->post_title : '(' . __('Unnamed Contact', 'propertyhive' ) . ')' ) . '</h3>' ) ;

                $date_posted = get_the_date( '', $contact->id );
                $contact_parts[] = 'Added on ' . $date_posted;

                // Add contact address to contact parts
                if ( $contact->get_formatted_full_address() != '' )
                {
                    $contact_parts[] = $contact->get_formatted_full_address();
                }

                // Add email address and number to contact parts, if set
                $email_address = $contact->_email_address;
                $telephone_number = $contact->_telephone_number;
                if ( !empty( $email_address ) || !empty( $telephone_number ) )
                {
                    $contact_details_array = array_filter(array(
                        $contact->_email_address,
                        $contact->_telephone_number
                    ));
                    $contact_parts[] = implode( ', ', $contact_details_array );
                }

                // Display all selected contact types. This may be redundant when all profiles are displayed
                $contact_types = $contact->_contact_types;
                if ( !empty( $contact_types ) )
                {
                    $contact_types_lookup = array(
                        'owner' => __( 'Owner', 'propertyhive' ),
                        'potentialowner' => __( 'Potential Owner', 'propertyhive' ),
                        'applicant' => __( 'Applicant', 'propertyhive' ),
                        'hotapplicant' => __( 'Hot Applicant', 'propertyhive' ),
                        'thirdparty' => __( 'Third Party Contact', 'propertyhive' ),
                    );

                    $contact_types_array = array();

                    foreach( $contact_types as $contact_type )
                    {
                        if ( isset( $contact_types_lookup[$contact_type] ) )
                        {
                            $contact_types_array[] = $contact_types_lookup[$contact_type];
                        }
                        else
                        {
                            $contact_types_array[] = ucfirst($contact_type);
                        }
                    }

                    $contact_parts[] = implode( ', ', $contact_types_array );
                }

                $contact_parts = $this->get_property_owner_records( $contact, $contact_parts );

                $contact_parts = $this->get_potential_owner_records( $contact, $contact_parts );

                $contact_parts = $this->get_third_party_records( $contact, $contact_parts );

                $contact_parts = $this->get_applicant_records( $contact, $contact_parts );

                $contact_parts = $this->get_enquiry_records( $contact, $contact_parts );

                $contact_parts = $this->get_viewing_records( $contact, $contact_parts );

                $contact_parts = $this->get_offer_records( $contact, $contact_parts );

                $contact_parts = $this->get_sale_records( $contact, $contact_parts );

                $contact_parts = $this->get_tenancy_records( $contact, $contact_parts );

                $contact_parts = $this->get_note_records( $contact, $contact_parts );

                $contact_parts = apply_filters( 'propertyhive_merge_contact_parts', $contact_parts );

                echo implode( '<br>', $contact_parts );
            ?>
            <label style="position:absolute; right:25px; top:25px;">
                <?php echo __( 'Use as Primary Contact', 'propertyhive' ); ?>
                <input type="radio" name="primary_merge_contact" value="<?php echo $contact_id; ?>"<?php if ( $i == 0 ) { echo ' checked'; } ?>>
            </label>
                <?php

                echo "</div><br>";
            }
            ?>
            <p class="form-field">
                <input type="button" value="<?php echo __( 'Merge Contacts', 'propertyhive' ); ?>" class="button-primary" id="merge_contacts_button">
                <a href="<?php echo wp_get_raw_referer(); ?>" class="button" id="cancel_merge_button"><?php echo __( 'Cancel', 'propertyhive' ); ?></a>
            </p>

            <script>

            jQuery( function(jQuery) {

                jQuery('#merge_contacts_button').click(function(e)
                {
                    var selected_primary = jQuery('input[name="primary_merge_contact"]:checked').val();
                    if ( typeof(selected_primary) == 'undefined' )
                    {
                        alert('A primary contact must be selected');
                        return false;
                    }

                    var confirmBox = confirm('All records will now be merged onto the primary contact. Do you want to continue?');

                    if (confirmBox)
                    {
                        jQuery('#merge_contacts_button').val('Merging Contacts...');
                        jQuery('#merge_contacts_button').attr('disabled', 'disabled');

                        var data = {
                            action:             'propertyhive_merge_contact_records',
                            contact_ids :       '<?php echo $_GET['merge_ids']; ?>',
                            primary_contact_id: selected_primary,
                        };

                        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                            if (response.error)
                            {
                                alert(response.error);

                                jQuery('#merge_contacts_button').val('Merge Contacts');
                                jQuery('#merge_contacts_button').attr('disabled', false);
                            }
                            if (response.success)
                            {
                                // Redirect to referrer, adding message in admin_notices
                                window.location.href = '<?php echo admin_url('edit.php?post_type=contact&propertyhive_contacts_merged=1') ?>';
                            }
                        });
                    }
                });
            });

            </script>
            <?php
        }
        else
        {
            ?>
            <p>Could not find multiple contacts to merge. Please <a href="<?php echo wp_get_raw_referer(); ?>">return to the contact list</a> and try again.</p>
            <?php
        }

        ?>
        </div>
        <?php
    }

    /**
     * Adds details of any associated properties to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_property_owner_records( $contact, $contact_parts )
    {
        // get properties where this is the owner
        $args = array(
            'post_type' => 'property',
            'nopaging' => true,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_owner_contact_id',
                    'value' => $contact->id,
                    'compare' => '='
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':"' . $contact->id . '"',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':' . $contact->id . ';',
                    'compare' => 'LIKE'
                )
            )
        );
        $property_query = new WP_Query($args);

        if ($property_query->have_posts())
        {
            while ($property_query->have_posts())
            {
                $property_query->the_post();
                
                $property = new PH_Property( get_the_ID() );

                $property_department = ucwords( str_replace( '-', ' ', $property->_department) );

                $contact_parts[] = $property_department . ' Owner: ' . $property->get_formatted_full_address();
            }
        }
        wp_reset_postdata();
        return $contact_parts;
    }

    /**
     * Adds details of any potential owner records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_potential_owner_records( $contact, $contact_parts )
    {
        // get appraisals where this is the owner and where not instructed
        $args = array(
            'post_type' => 'appraisal',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_property_owner_contact_id',
                    'value' => $contact->id,
                    'compare' => '='
                ),
            )
        );

        $appraisal_query = new WP_Query($args);

        if ($appraisal_query->have_posts())
        {
            while ($appraisal_query->have_posts())
            {
                $appraisal_query->the_post();

                $appraisal = new PH_Appraisal( get_the_ID() );
                
                $property_department = ucwords( str_replace( '-', ' ', $appraisal->_department) );

                $contact_parts[] = $property_department . ' Appraisal Owner: ' . $appraisal->get_formatted_full_address();
            }
        }
        wp_reset_postdata();
        return $contact_parts;
    }

    /**
     * Adds details of any third party contact records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_third_party_records( $contact, $contact_parts )
    {
        $third_party_categories = get_post_meta( $contact->id, '_third_party_categories', TRUE );
        if ( is_array($third_party_categories) && !empty($third_party_categories) )
        {
            $third_party_category_names = array();

            $ph_third_party_contacts = new PH_Third_Party_Contacts();

            foreach ( $third_party_categories as $third_party_category )
            {
                if ( $third_party_category != '' && $third_party_category != 0 )
                {
                    $category_name = $ph_third_party_contacts->get_category( $third_party_category );
                    if ( $category_name !== false )
                    {
                        $third_party_category_names[] = $category_name;
                    }
                }
                else
                {
                    $third_party_category_names[] = 'General';
                }
            }

            $contact_parts[] = 'Third Party Contact: ' . implode( ', ', $third_party_category_names);
        }
        return $contact_parts;
    }

    /**
     * Adds details of any applicant records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_applicant_records( $contact, $contact_parts )
    {
        $num_applicant_profiles = get_post_meta( $contact->id, '_applicant_profiles', TRUE );
        if ( !empty( $num_applicant_profiles ) )
        {
            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                $applicant_profile = get_post_meta( $contact->id, '_applicant_profile_' . $i, TRUE );

                $applicant_department = isset( $applicant_profile['department'] ) ? $applicant_profile['department'] : 'Empty';

                $applicant_profile_parts = array();

                if ( $applicant_department == 'residential-sales' || ph_get_custom_department_based_on($applicant_department) == 'residential-sales' )
                {
                    if ( isset( $applicant_profile['max_price'] ) && !empty( $applicant_profile['max_price'] ) )
                    {
                        $applicant_profile_parts[] = '£' . ph_display_price_field( $applicant_profile['max_price'] );
                    }

                    if ( isset( $applicant_profile['min_beds'] ) && !empty( $applicant_profile['min_beds'] ) )
                    {
                        $applicant_profile_parts[] = $applicant_profile['min_beds'] . ' bedrooms';
                    }
                }

                if ( $applicant_department == 'residential-lettings' || ph_get_custom_department_based_on($applicant_department) == 'residential-lettings' )
                {
                    if ( isset( $applicant_profile['max_rent'] ) && !empty( $applicant_profile['max_rent'] ) )
                    {
                        $maximum_rent = '£' . ph_display_price_field( $applicant_profile['max_rent'] );
                        if ( isset( $applicant_profile['rent_frequency'] ) && !empty( $applicant_profile['rent_frequency'] ) )
                        {
                            $maximum_rent .= ' ' . $applicant_profile['rent_frequency'];
                        }
                        else
                        {
                            $maximum_rent .= 'pcm';
                        }
                        $applicant_profile_parts[] = $maximum_rent;
                    }

                    if ( isset( $applicant_profile['min_beds'] ) && !empty( $applicant_profile['min_beds'] ) )
                    {
                        $applicant_profile_parts[] = $applicant_profile['min_beds'] . ' bedrooms';
                    }
                }

                if ( $applicant_department == 'commercial' || ph_get_custom_department_based_on($applicant_department) == 'commercial' )
                {
                    if ( isset( $applicant_profile['available_as'] ) && !empty( $applicant_profile['available_as'] ) )
                    {
                        $available_as = implode( '/', $applicant_profile['available_as'] );
                        $applicant_profile_parts[] = 'Available as ' . $available_as;
                    }
                }
                $applicant_profile_parts = array_filter( $applicant_profile_parts );

                $applicant_profile_text = ucwords( str_replace( '-', ' ', $applicant_department ) ) . ' Applicant';
                if ( count($applicant_profile_parts) > 0 )
                {
                    $applicant_profile_text .= ': ' . implode( ', ', $applicant_profile_parts );
                }

                $contact_parts[] = $applicant_profile_text;
            }
        }
        return $contact_parts;
    }

    /**
     * Adds count of any enquiry records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_enquiry_records( $contact, $contact_parts )
    {
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => '_contact_id',
                'value' => $contact->id,
            ),
        );

        // Not including email address lookup in enquiry count as they won't be moved or removed by the merge
        $contact_email_address = $contact->_email_address;
        if ( !empty($contact_email_address) )
        {
            $meta_query[] = array(
                'key' => 'email',
                'value' => $contact_email_address,
            );

            $meta_query[] = array(
                'key' => 'email_address',
                'value' => $contact_email_address,
            );
        }

        $args = array(
            'post_type' => 'enquiry',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => $meta_query,
        );
        $enquiries_query = new WP_Query( $args );
        $enquiries_count = $enquiries_query->found_posts;
        wp_reset_postdata();

        if ( $enquiries_count > 0 )
        {
            $contact_parts[] = $enquiries_count . ' enquir' . ( $enquiries_count != 1 ? 'ies' : 'y' );
        }
        return $contact_parts;
    }

    /**
     * Adds count of any viewing records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_viewing_records( $contact, $contact_parts )
    {
        $args = array(
            'post_type' => 'viewing',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contact->id,
                )
            )
        );
        $viewings_query = new WP_Query( $args );
        $viewings_count = $viewings_query->found_posts;
        wp_reset_postdata();

        if ( $viewings_count > 0 )
        {
            $contact_parts[] = $viewings_count . ' viewing' . ( $viewings_count != 1 ? 's' : '' );
        }
        return $contact_parts;
    }

    /**
     * Adds count of any offer records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_offer_records( $contact, $contact_parts )
    {
        $args = array(
            'post_type' => 'offer',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contact->id,
                )
            )
        );
        $offers_query = new WP_Query( $args );
        $offers_count = $offers_query->found_posts;
        wp_reset_postdata();

        if ( $offers_count > 0 )
        {
            $contact_parts[] = $offers_count . ' offer' . ( $offers_count != 1 ? 's' : '' );
        }
        return $contact_parts;
    }

    /**
     * Adds count of any sale records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_sale_records( $contact, $contact_parts )
    {
        $args = array(
            'post_type' => 'sale',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contact->id,
                )
            )
        );
        $sales_query = new WP_Query( $args );
        $sales_count = $sales_query->found_posts;
        wp_reset_postdata();

        if ( $sales_count > 0 )
        {
            $contact_parts[] = $sales_count . ' sale' . ( $sales_count != 1 ? 's' : '' );
        }
        return $contact_parts;
    }

    /**
     * Adds count of any tenancy records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_tenancy_records( $contact, $contact_parts )
    {
        $args = array(
            'post_type' => 'tenancy',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contact->id
                )
            )
        );
        $tenancies_query = new WP_Query( $args );
        $tenancies_count = $tenancies_query->found_posts;
        wp_reset_postdata();

        if ( $tenancies_count > 0 )
        {
            $contact_parts[] = $tenancies_count . ' tenanc' . ( $tenancies_count != 1 ? 'ies' : 'y' );
        }
        return $contact_parts;
    }

    /**
     * Adds count of any note records to the list of contact parts
     *
     * @access private
     * @return array
     */
    private function get_note_records( $contact, $contact_parts )
    {
        global $post;

        $post = get_post((int)$contact->id);

        $args = array(
            'post_id' => (int)$contact->id,
            'type'      => 'propertyhive_note',
            'meta_query' => array(
                array(
                    'key' => 'related_to',
                    'value' => '"' . (int)$contact->id . '"',
                    'compare' => 'LIKE',
                ),
            )
        );
        $notes = get_comments( $args );

        $notes_count = count($notes);

        if ( $notes_count > 0 )
        {
            $contact_parts[] = $notes_count . ' note' . ( $notes_count != 1 ? 's' : '' );
        }

        return $contact_parts;
    }

    public function do_merge( $primary_contact_id, $contacts_to_merge )
    {
        global $wpdb, $post;

        // Merge contact_types into primary
        $primary_contact_types = get_post_meta( $primary_contact_id, '_contact_types', true );
        if ( $primary_contact_types == '' || !is_array($primary_contact_types) )
        {
            $primary_contact_types = array();
        }

        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $child_contact_types = get_post_meta( $child_contact_id, '_contact_types', true );
            if ( is_array($child_contact_types) )
            {
                $primary_contact_types = array_merge( $primary_contact_types, $child_contact_types );
            }
        }

        update_post_meta( $primary_contact_id, '_contact_types', array_unique( $primary_contact_types ) );

        // Merge third party categories into primary
        $primary_third_party_categories = get_post_meta( $primary_contact_id, '_third_party_categories', true );
        if ( $primary_third_party_categories == '' || !is_array($primary_third_party_categories) )
        {
            $primary_third_party_categories = array();
        }

        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $child_third_party_categories = get_post_meta( $child_contact_id, '_third_party_categories', true );
            if ( is_array($child_third_party_categories) )
            {
                $primary_third_party_categories = array_merge( $primary_third_party_categories, $child_third_party_categories );
            }
        }

        if ( count($primary_third_party_categories) > 0 )
        {
            update_post_meta( $primary_contact_id, '_third_party_categories', array_unique( $primary_third_party_categories ) );
        }

        // Move applicant profiles to primary
        $primary_applicant_profiles = get_post_meta( $primary_contact_id, '_applicant_profiles', true );
        if ( empty($primary_applicant_profiles) )
        {
            $primary_applicant_profiles = 0;
        }

        $hot_applicant = '';
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $child_applicant_profiles = get_post_meta( $child_contact_id, '_applicant_profiles', true );
            if ( !empty($child_applicant_profiles) )
            {
                for ( $i = 0; $i < $child_applicant_profiles; ++$i )
                {
                    $child_applicant_profile = get_post_meta( $child_contact_id, '_applicant_profile_' . $i, true );
                    if ( !empty($child_applicant_profile) )
                    {
                        update_post_meta( $primary_contact_id, '_applicant_profile_' . $primary_applicant_profiles, $child_applicant_profile );
                        
                        $child_applicant_profile_match_history = get_post_meta( $child_contact_id, '_applicant_profile_' . $i . '_match_history', true );
                        if ( !empty($child_applicant_profile_match_history) )
                        {
                            update_post_meta( $primary_contact_id, '_applicant_profile_' . $primary_applicant_profiles . '_match_history', $child_applicant_profile_match_history );
                        }

                        if ( isset($child_applicant_profile['grading']) && ph_clean($child_applicant_profile['grading']) == 'hot' )
                        {
                            $hot_applicant = 'yes';
                        }

                        ++$primary_applicant_profiles;
                    }
                }
            }
        }

        if ( $hot_applicant == 'yes' )
        {
            update_post_meta( $primary_contact_id, '_hot_applicant', $hot_applicant );
        }

        if ( $primary_applicant_profiles > 0 )
        {
            update_post_meta( $primary_contact_id, '_applicant_profiles', $primary_applicant_profiles );
        }

        // Merge dismissed properties
        $primary_dismissed_properties = get_post_meta( $primary_contact_id, '_dismissed_properties', true );
        if ( empty($primary_dismissed_properties) )
        {
            $primary_dismissed_properties = array();
        }
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $child_dismissed_properties = get_post_meta( $child_contact_id, '_dismissed_properties', true );
            if ( is_array($child_dismissed_properties) && !empty($child_dismissed_properties) )
            {
                $primary_dismissed_properties = array_merge($primary_dismissed_properties, $child_dismissed_properties);
            }
        }
        if ( !empty($primary_dismissed_properties) )
        {
            $primary_dismissed_properties = array_unique($primary_dismissed_properties);
            update_post_meta( $primary_contact_id, '_dismissed_properties', $primary_dismissed_properties );
        }

        // Move appraisal records to primary
        $args = array(
            'post_type' => 'appraisal',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_property_owner_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            )
        );

        $appraisal_query = new WP_Query($args);

        if ($appraisal_query->have_posts())
        {
            while ($appraisal_query->have_posts())
            {
                $appraisal_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_property_owner_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        foreach ( $contacts_to_merge as $child_contact_id )
        {
            // Move property owner records to primary
            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_owner_contact_id',
                        'value' => $child_contact_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':"' . $child_contact_id . '"',
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':' . $child_contact_id . ';',
                        'compare' => 'LIKE'
                    )
                )
            );
            $property_query = new WP_Query($args);

            if ($property_query->have_posts())
            {
                while ($property_query->have_posts())
                {
                    $property_query->the_post();

                    $property_owner_ids = get_post_meta( get_the_id(), '_owner_contact_id', true );

                    if ( !is_array( $property_owner_ids ) )
                    {
                        update_post_meta( get_the_id(), '_owner_contact_id', $primary_contact_id, $child_contact_id );
                    }
                    else
                    {
                        unset($property_owner_ids[array_search($child_contact_id, $property_owner_ids)]);
                        $property_owner_ids[] = $primary_contact_id;
                        update_post_meta( get_the_id(), '_owner_contact_id', $property_owner_ids );
                    }
                }
            }
            wp_reset_postdata();
        }

        // Move enquiries to primary
        $args = array(
            'post_type' => 'enquiry',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            ),
        );
        $enquiries_query = new WP_Query( $args );

        if ($enquiries_query->have_posts())
        {
            while ($enquiries_query->have_posts())
            {
                $enquiries_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        // Move viewings to primary
        $args = array(
            'post_type' => 'viewing',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            ),
        );
        $viewings_query = new WP_Query( $args );

        if ($viewings_query->have_posts())
        {
            while ($viewings_query->have_posts())
            {
                $viewings_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_applicant_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        // Move offers to primary
        $args = array(
            'post_type' => 'offer',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            ),
        );
        $offers_query = new WP_Query( $args );

        if ($offers_query->have_posts())
        {
            while ($offers_query->have_posts())
            {
                $offers_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_applicant_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        // Move sales to primary
        $args = array(
            'post_type' => 'sale',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            ),
        );
        $sales_query = new WP_Query( $args );

        if ($sales_query->have_posts())
        {
            while ($sales_query->have_posts())
            {
                $sales_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_applicant_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        // Move tenancies to primary
        $args = array(
            'post_type' => 'tenancy',
            'nopaging'    => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contacts_to_merge,
                    'compare' => 'IN'
                ),
            ),
        );
        $tenancies_query = new WP_Query( $args );

        if ($tenancies_query->have_posts())
        {
            while ($tenancies_query->have_posts())
            {
                $tenancies_query->the_post();
                foreach ( $contacts_to_merge as $child_contact_id )
                {
                    update_post_meta( get_the_id(), '_applicant_contact_id', $primary_contact_id, $child_contact_id );
                }
            }
        }
        wp_reset_postdata();

        // Copy notes to primary
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $post = get_post((int)$child_contact_id);

            $args = array(
                'post_id' => (int)$child_contact_id,
                'type'      => 'propertyhive_note',
                'meta_query' => array(
                    array(
                        'key' => 'related_to',
                        'value' => '"' . (int)$child_contact_id . '"',
                        'compare' => 'LIKE',
                    ),
                )
            );
            $notes = get_comments( $args );

            foreach ( $notes as $note )
            {
                // Want to ignore note if it's an existing 'contact_merged' note. This would be confusing
                $comment_content = unserialize($note->comment_content);
                if ( 
                    isset($comment_content['note_type']) && $comment_content['note_type'] == 'action' &&
                    isset($comment_content['action']) && $comment_content['action'] == 'contact_merged'
                )
                {
                    continue;
                }

                // This note could've been assigned to this contact, or this contact could just be related to it
                // Need to check both
                if ( $note->comment_post_ID == $child_contact_id )
                {
                    $data = array(
                        'comment_ID' => $note->comment_ID,
                        'comment_post_ID' => $primary_contact_id
                    );
                    $updated = wp_update_comment( $data );
                }

                $related_tos = get_comment_meta( $note->comment_ID, 'related_to', true );
                if ( !empty($related_tos) )
                {
                    $new_related_to = array();
                    foreach ( $related_tos as $related_to )
                    {
                        if ( $related_to == $child_contact_id )
                        {
                            $new_related_to[] = (string)$primary_contact_id; // convert to string to LIKE lookups work
                        }
                        else
                        {
                            $new_related_to[] = (string)$related_to; // convert to string to LIKE lookups work
                        }
                    }
                    $new_related_to = array_unique($new_related_to);
                    update_comment_meta( $note->comment_ID, 'related_to', $new_related_to );
                }
            }
        }

        // Write a note to all contacts highlighting what has happened
        $current_user = wp_get_current_user();
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $comment = array(
                'note_type' => 'action',
                'action' => 'contact_merged',
                'merged_into' => (int)$primary_contact_id,
            );

            $data = array(
                'comment_post_ID'      => (int)$child_contact_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );

            // Move contact being merged to the bin
            wp_trash_post( (int)$child_contact_id );
        }

        // Update email log
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            $wpdb->query("
                UPDATE " . $wpdb->prefix . "ph_email_log
                SET 
                    contact_id = '" . (int)$primary_contact_id . "'
                WHERE 
                    contact_id = '" . (int)$child_contact_id . "'
            ");
        }

        do_action( 'propertyhive_contacts_merged', $primary_contact_id, $contacts_to_merge );
    }
}

endif;