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

		// Admin notices
		add_action( 'admin_notices', array( $this, 'viewing_admin_notices') );

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-viewing_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_viewing_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-viewing_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-viewing', array( $this, 'remove_bulk_actions') );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );*/

		add_action( 'add_post_meta', array( $this, 'check_viewing_feedback_add' ), 10, 3 );
		add_action( 'update_post_meta', array( $this, 'check_viewing_feedback_update' ), 10, 4 );

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Output admin notices relating to viewing
	 */
	public function viewing_admin_notices()
	{
		global $post;

		$screen = get_current_screen();

		if ( $screen->id == 'viewing' && isset($_GET['post']) && get_post_type($_GET['post']) == 'viewing' )
		{
			$viewing = new PH_Viewing((int)$_GET['post']);
			$related_viewings = $viewing->_related_viewings;

			// There is either a previous or next viewing for this applicant/property combination
			if (
				!in_array( $viewing->_status, array('cancelled', 'no_show') )
				&&
				is_array($related_viewings)
				&&
				isset($related_viewings['previous']) && isset($related_viewings['next'])
				&&
				( count($related_viewings['previous']) > 0 || count($related_viewings['next']) > 0 )
			)
			{
				$message = sprintf( 
				    __( 'This is the %s viewing for this applicant at this property.', 'propertyhive' ), 
				    strtolower(ph_ordinal_suffix(count($related_viewings['previous']) + 1))
				) . '<br>';

				if ( count($related_viewings['previous']) > 0 )
				{
					$previous_viewing_id = end($related_viewings['previous']);
					$message .= '<a href="' . esc_url(get_edit_post_link( $previous_viewing_id, '' )) . '"><< '. esc_html(__( 'Go to previous', 'propertyhive' )) . '</a>';
				}

				if ( count($related_viewings['next']) > 0 )
				{
					$next_viewing_id = $related_viewings['next'][0];

					// If there are links to next and previous, show a divider
					if ( isset($previous_viewing_id) )
					{
						$message .= ' | ';
					}

					$message .= '<a href="' . esc_url(get_edit_post_link( $next_viewing_id, '' )) . '">'. esc_html(__( 'Go to next', 'propertyhive' )) . ' >></a>';
				}

				echo "<div class=\"notice notice-info\"> <p>$message</p></div>";
			}
		}
	}

	/**
	 * Check if we're editing or adding a viewing
	 * @return boolean
	 */
	private function is_editing_viewing() {
		if ( ! empty( $_GET['post_type'] ) && 'viewing' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'viewing' == get_post_type( (int)$_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'viewing' == get_post_type( (int)$_REQUEST['post_id'] ) ) {
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
        $columns['applicant'] = __( 'Applicant(s)', 'propertyhive' );
        $columns['status'] = __( 'Status', 'propertyhive' );
        $columns['negotiators'] = __( 'Attending Negotiators', 'propertyhive' );

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

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . esc_html($title) . '</a></strong>';
			 break;
            case 'property' :
                
                if ( $the_viewing->property_id != '' ) 
                {
	                $property = new PH_Property((int)$the_viewing->property_id);
	                echo esc_html($property->get_formatted_full_address());
                }
                else
                {
                	echo '-';
                }
                break;
            case 'applicant' :
                $applicant_contact_ids = get_post_meta( $post->ID, '_applicant_contact_id' );
                if ( is_array($applicant_contact_ids) && !empty($applicant_contact_ids) )
                {
                    $applicants = array();
                    foreach ( $applicant_contact_ids as $applicant_contact_id )
                    {
                        $applicants[] = esc_html(get_the_title($applicant_contact_id));
                    }
                    echo implode("<br>", $applicants);
                }
                else
                {
                    echo '-';
                }

                break;
            case 'status' :
                
                echo esc_html(__( ucwords(str_replace("_", " ", $the_viewing->status)), 'propertyhive' ));
                if ( $the_viewing->status == 'pending' )
                {
                	echo '<br>';
                	// confirmation status
                	if ( $the_viewing->all_confirmed == 'yes' )
                	{
                		echo esc_html(__( 'All Parties Confirmed', 'propertyhive' ));
                	}
                	else
                	{
                		echo esc_html(__( 'Awaiting Confirmation', 'propertyhive' ));
                	}
                }
                if ( $the_viewing->status == 'carried_out' )
                {
                    echo '<br>';
                    switch ( $the_viewing->feedback_status )
                    {
                        case "interested": { echo esc_html(__( 'Applicant Interested', 'propertyhive' )); break; }
                        case "not_interested": { echo esc_html(__( 'Applicant Not Interested', 'propertyhive' )); break; }
                        case "not_required": { echo esc_html(__( 'Feedback Not Required', 'propertyhive' )); break; }
                        default: { echo esc_html(__( 'Awaiting Feedback', 'propertyhive' )); }
                    }

                    if ( $the_viewing->feedback_status == 'interested' || $the_viewing->feedback_status == 'not_interested' )
                    {
                    	echo '<br>' . esc_html( $the_viewing->feedback_passed_on == 'yes' ? __( 'Feedback Passed On', 'propertyhive' ) : __( 'Feedback Not Passed On', 'propertyhive' ) );
                    }
				}

				// Add text if this a second, third etc viewing
				$related_viewings = get_post_meta( $post->ID, '_related_viewings', TRUE );
				if ( isset($related_viewings['previous']) && count($related_viewings['previous']) > 0 )
				{
					echo '<br>' . ph_ordinal_suffix(count($related_viewings['previous'])+1) . ' Viewing' ;
				}
                
                break;
            case 'negotiators' :
            	$negotiator_ids = get_post_meta( $post->ID, '_negotiator_id' );
            	if ( is_array($negotiator_ids) && !empty($negotiator_ids) )
            	{
            		$negotiators = array();
            		foreach ( $negotiator_ids as $negotiator_id )
            		{
            			$user_info = get_userdata($negotiator_id);
            			if ( $user_info === false )
            			{
            				$negotiators[] = __( 'Unknown negotiator', 'propertyhive' );
            			}
            			else
            			{
	            			$negotiators[] = $user_info->display_name;
            			}
            		}
            		echo implode(", ", $negotiators);
            	}
            	else
            	{
            		echo '<em>- ' . esc_html(__( 'Unaccompanied', 'propertyhive' )) . ' -</em>';
            	}
            	break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
        
        if ( 'edit-viewing' === $screen ) 
        {
            $default = 'start_date_time';
        }

        return $default;
    }

    public function remove_actions($actions, $post) {

        if ( $post->post_type !== 'viewing' )
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

	public static function check_viewing_feedback_add( $object_id, $meta_key, $meta_value )
	{
		if ( get_post_type($object_id) == 'viewing' && $meta_key == '_feedback_status' && in_array($meta_value, array( 'interested', 'not_interested' )) )
		{
			update_post_meta( (int)$object_id, '_feedback_received_date', date("Y-m-d H:i:s") );
		}
	}

	public static function check_viewing_feedback_update( $meta_id, $object_id, $meta_key, $meta_value )
	{
		if ( get_post_type($object_id) == 'viewing' && $meta_key == '_feedback_status' && in_array($meta_value, array( 'interested', 'not_interested' )) )
		{
			$original_value = get_post_meta( $object_id, '_feedback_status', TRUE );
			if ( in_array($original_value, array( '', 'not_required' )) )
			{
				update_post_meta( (int)$object_id, '_feedback_received_date', date("Y-m-d H:i:s") );
			}
		}
	}
}

endif;

return new PH_Admin_CPT_Viewing();
