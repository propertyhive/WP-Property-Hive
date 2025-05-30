<?php
/**
 * Admin functions for the offer post type
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Post Types
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_CPT' ) ) {
	include( 'class-ph-admin-cpt.php' );
}

if ( ! class_exists( 'PH_Admin_CPT_Offer' ) ) :

/**
 * PH_Admin_CPT_Offer Class
 */
class PH_Admin_CPT_Offer extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'offer';

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-offer_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_offer_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-offer_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-offer', array( $this, 'remove_bulk_actions') );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );*/

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Check if we're editing or adding a offer
	 * @return boolean
	 */
	private function is_editing_offer() {
		if ( ! empty( $_GET['post_type'] ) && 'offer' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'offer' == get_post_type( (int)$_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'offer' == get_post_type( (int)$_REQUEST['post_id'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param int $post_id
	 */
	public function pre_post_update( $post_id ) {

	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function wp_insert_post_data( $data ) {
		

		return $data;
	}

	/**
	 * Change the columns shown in admin.
	 */
	public function edit_columns( $existing_columns ) {

		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';
		$columns['offer_date_time'] = __( 'Offer Date / Time', 'propertyhive' );
        $columns['property'] = __( 'Property', 'propertyhive' );
        $columns['property_owner'] = __( 'Property Owner', 'propertyhive' );
        $columns['applicant'] = __( 'Applicant(s)', 'propertyhive' );
        $columns['amount'] = __( 'Amount', 'propertyhive' );
        $columns['status'] = __( 'Status', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_offer;

		if ( empty( $the_offer ) || $the_offer->ID != $post->ID ) 
		{
			$the_offer = new PH_Offer( $post->ID );
		}

		switch ( $column ) {
			case 'offer_date_time' :
				
				$edit_link        = get_edit_post_link( $post->ID );
				//$title            = _draft_or_post_title();
                $title            = date("H:i jS F Y", strtotime($the_offer->offer_date_time));
                
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . esc_html($title) . '</a></strong>';
			 break;
            case 'property' :
                
                if ( $the_offer->property_id != '' )
                {
	                $property = new PH_Property((int)$the_offer->property_id);
	                echo esc_html($property->get_formatted_full_address());
	            }
            	else
            	{
            		echo '-';
            	}
                
                break;
            case 'property_owner' :
                
                $the_property = new PH_Property((int)$the_offer->property_id);
                $owner_contact_ids = $the_property->_owner_contact_id;
                if ( 
                	( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                	||
                	( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
                )
                {
                	if ( !is_array($owner_contact_ids) )
                	{
                		$owner_contact_ids = array($owner_contact_ids);
                	}

                	foreach ( $owner_contact_ids as $owner_contact_id )
                	{
	                	echo esc_html(get_the_title($owner_contact_id)) . '<br>';
	                	if ( count($owner_contact_ids) == 1 )
		                {
			                echo '<div class="row-actions">';
			                echo 'T: ' . esc_html(get_post_meta($owner_contact_id, '_telephone_number', TRUE)) . '<br>';
			                echo 'E: ' . esc_html(get_post_meta($owner_contact_id, '_email_address', TRUE));
			                echo '</div>';
			            }
			        }
                }
                else
                {
                	echo '-';
                }
                
                break;
            case 'applicant' :
                
                echo $the_offer->get_applicants( false, true, false );

                break;
            case 'amount' :
                
                echo $the_offer->get_formatted_amount();
                
                break;
            case 'status' :
                echo esc_html(__( ucwords(str_replace("_", " ", $the_offer->status)), 'propertyhive' ));
                break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
        
        if ( 'edit-offer' === $screen ) 
        {
            $default = 'offer_date_time';
        }

        return $default;
    }

    public function remove_actions($actions, $post) {

        if ( $post->post_type !== 'offer' )
        {
            return $actions;
        }

        // Remove 'Quick Edit' link
        if ( isset($actions['inline hide-if-no-js']) ) 
        {
            unset($actions['inline hide-if-no-js']);
        }

        if ( isset($actions['trash']) ) 
        {
            unset($actions['trash']);
        }

        return $actions;
    }

	/**
	 * Make offer columns sortable
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public function custom_columns_sort( $columns ) {
		$custom = array(
			'offer_date_time' => '_offer_date_time',
			'status' => '_status',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Offer column orderby
	 *
	 * @access public
	 * @param mixed $vars
	 * @return array
	 */
	public function custom_columns_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) ) {
			if ( '_offer_date_time' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_offer_date_time',
					'orderby' 	=> 'meta_value'
				) );
			}
			elseif ( '_status' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_status',
					'orderby' 	=> 'meta_value'
				) );
			}
		}

		return $vars;
	}

	/**
	 * Remove bulk edit option
	 * @param  array $actions
	 */
	public function remove_bulk_actions( $actions ) {
        unset( $actions['edit'] );
        return $actions;
    }

	/**
	 * Show a status filter box
	 */
	public function propertyhive_filters() {
		global $typenow, $wp_query;

		if ( 'offer' != $typenow ) {
			return;
		}

		echo apply_filters( 'propertyhive_offer_filters', $output );
	}

	/**
	 * Filter the offers in admin based on options
	 *
	 * @param mixed $query
	 */
	public function offer_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'offer' == $typenow ) {

		}
	}
}

endif;

return new PH_Admin_CPT_Offer();
