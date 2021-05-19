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

        <h1><?php echo __('Merge Duplicate Contacts', 'propertyhive'); ?></h1>

        <p>When you click Save, all records will be moved onto the contact selected as the primary contact, and all other contacts will be moved to trash.</p>

        <?php

        if ( isset( $_GET['merge_ids'] )  && $_GET['merge_ids'] != '' )
        {
            $ids_to_merge = explode( '|', $_GET['merge_ids'] );
        }

        if ( isset( $ids_to_merge ) && count( $ids_to_merge ) > 1 )
        {
            foreach ( $ids_to_merge as $i => $contact_id )
            {
                echo "<div>";
                $contact = new PH_Contact( $contact_id );

                // Initialise array to hold information about each contact
                $contact_parts = array( '<b>' . ( trim($contact->post_title) != '' ? $contact->post_title : '(' . __('Unnamed Contact', 'propertyhive' ) . ')' ) . '</b>' ) ;

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
                <input type="radio" id="merge_contact_<?php echo $contact_id; ?>" name="primary_merge_contact" value="<?php echo $contact_id; ?>"<?php if ( $i == 0 ) { echo ' checked'; } ?>>
                <label for="merge_contact_<?php echo $contact_id; ?>"><?php echo __( 'Primary Contact', 'propertyhive' ); ?></label><br>
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
                        var data = {
                            action:             'propertyhive_merge_contact_records',
                            contact_ids :       '<?php echo $_GET['merge_ids']; ?>',
                            primary_contact_id: selected_primary,
                        };

                        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                            if (response.error)
                            {
                                alert(response.error);
                            }
                            if (response.success)
                            {
                                // Redirect to referrer, adding message in admin_notices
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
            $contact_parts[] = 'Third Party Contact: ' . implode( ', ', $third_party_categories);
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
}

endif;