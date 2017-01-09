<?php
/**
 * Admin functions for the viewing post type
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

if ( ! class_exists( 'PH_Admin_CPT_Viewing' ) ) :

/**
 * PH_Admin_CPT_Viewing Class
 */
class PH_Admin_CPT_Viewing extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'viewing';

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-viewing_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_viewing_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'manage_edit-viewing_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-viewing', array( $this, 'remove_bulk_actions') );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );*/

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Check if we're editing or adding a viewing
	 * @return boolean
	 */
	private function is_editing_viewing() {
		if ( ! empty( $_GET['post_type'] ) && 'viewing' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'viewing' == get_post_type( $_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'viewing' == get_post_type( $_REQUEST['post_id'] ) ) {
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
		$columns['start_date_time'] = __( 'Viewing Date / Time', 'propertyhive' );
        $columns['property'] = __( 'Property', 'propertyhive' );
        $columns['applicant'] = __( 'Applicant', 'propertyhive' );
        $columns['status'] = __( 'Status', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_viewing;

		if ( empty( $the_viewing ) || $the_viewing->ID != $post->ID ) 
		{
			$the_viewing = new PH_Viewing( $post->ID );
		}

		switch ( $column ) {
			case 'start_date_time' :
				
				$edit_link        = get_edit_post_link( $post->ID );
				//$title            = _draft_or_post_title();
                $title            = date("H:i jS F Y", strtotime($the_viewing->start_date_time));
                
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a></strong>';

				// Get actions
				$actions = array();
                
				if ( $can_edit_post && 'trash' != $post->post_status ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'propertyhive' ) ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>';
				}
				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
					if ( 'trash' == $post->post_status ) {
						$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'propertyhive' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'propertyhive' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						//$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'propertyhive' ) . '</a>';
					}

					if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'propertyhive' ) . '</a>';
					}
				}
				if ( $post_type_object->public ) {
					if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
						if ( $can_edit_post )
							$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'propertyhive' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'propertyhive' ) . '</a>';
					} elseif ( 'trash' != $post->post_status ) {
						$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'propertyhive' ), $title ) ) . '" rel="permalink">' . __( 'View', 'propertyhive' ) . '</a>';
					}
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof($actions);

				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo '<span class="' . $action . '">' . $link . $sep . '</span>';
				}
				echo '</div>';

			 break;
            case 'property' :
                
                $property = new PH_Property((int)$the_viewing->property_id);
                echo $property->get_formatted_full_address();
                
                break;
            case 'applicant' :
                
                echo get_the_title($the_viewing->applicant_contact_id);
                
                break;
            case 'status' :
                
                echo ucwords(str_replace("_", " ", $the_viewing->status));
                if ( $the_viewing->status == 'carried_out' )
                {
                    echo '<br>';
                    switch ( $the_viewing->feedback_status )
                    {
                        case "interested": { echo 'Applicant Interested'; break; }
                        case "not_interested": { echo 'Applicant Not Interested'; break; }
                        case "not_required": { echo 'Feedback Not Required'; break; }
                        default: { echo 'Awaiting Feedback'; }
                    }

                    if ( $the_viewing->feedback_status == 'interested' || $the_viewing->feedback_status == 'not_interested' )
                    {
                    	echo '<br>' . ( ($the_viewing->feedback_passed_on == 'yes') ? 'Feedback Passed On' : 'Feedback Not Passed On' );
                    }
                }
                
                break;
			default :
				break;
		}
	}

	/**
	 * Make viewing columns sortable
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public function custom_columns_sort( $columns ) {
		$custom = array(
			'start_date_time' => '_start_date_time',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Viewing column orderby
	 *
	 * @access public
	 * @param mixed $vars
	 * @return array
	 */
	public function custom_columns_orderby( $vars ) {
		if ( is_admin() && $vars['post_type'] == 'viewing' )
		{
			$vars = array_merge( $vars, array(
				'meta_key' 	=> '_start_date_time',
				'orderby' 	=> 'meta_value'
			) );
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

		if ( 'viewing' != $typenow ) {
			return;
		}

		echo apply_filters( 'propertyhive_viewing_filters', $output );
	}

	/**
	 * Filter the viewings in admin based on options
	 *
	 * @param mixed $query
	 */
	public function viewing_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'viewing' == $typenow ) {

		}
	}
}

endif;

return new PH_Admin_CPT_Viewing();
