<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Comments
 *
 * Handle comments.
 *
 * @class    PH_Comments
 * @version  1.0.0
 * @package  PropertyHive/Classes/Products
 * @category Class
 * @author   PropertyHive
 */
class PH_Comments {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		// Secure propertyhive notes
		add_filter( 'comments_clauses', array( __CLASS__, 'exclude_note_comments' ), 10, 1 );

		add_filter( 'comments_clauses', array( __CLASS__, 'related_to_or_post_id' ), 20, 1 );

		// Count comments
		add_filter( 'wp_count_comments', array( __CLASS__, 'wp_count_comments' ), 99, 2 );

		// Delete comments count cache whenever there is a new comment or a comment status changes
		add_action( 'wp_insert_comment', array( __CLASS__, 'delete_comments_count_cache' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'delete_comments_count_cache' ) );

		add_action( 'add_post_meta', array( __CLASS__, 'check_on_market_add' ), 10, 3 );
		add_action( 'update_post_meta', array( __CLASS__, 'check_on_market_update' ), 10, 4 );

		add_action( 'update_post_meta', array( __CLASS__, 'check_price_change' ), 10, 4 );

		add_action( 'set_object_terms', array( __CLASS__, 'check_property_status_update' ), 10, 6 );
	}

	public function check_property_status_update( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids )
    {
        if ( $taxonomy == 'availability' && apply_filters( 'propertyhive_add_property_availability_change_note', true ) === true )
        {
            if (
                get_post_type($object_id) == 'property' &&
                get_post_status($object_id) == 'publish' &&
                $tt_ids != $old_tt_ids
            )
            {
                $all_availability_terms = get_terms( 'availability', array( 'hide_empty' => 0 ) );

                $old_availability_id = '';
                $old_availability_name = '';
                if ( is_array($old_tt_ids) && !empty($old_tt_ids) )
                {
                    $old_availability_id = (int)$old_tt_ids[0];

                    foreach( $all_availability_terms as $term )
                    {
                        $tt_id = (int)$term->term_taxonomy_id;
                        if( $tt_id == $old_availability_id )
                        {
                            $old_availability_name = $term->name;
                        }
                    }
                }

                $new_availability_id = '';
                $new_availability_name = '';
                if ( is_array($tt_ids) && !empty($tt_ids) )
                {
                    $new_availability_id = (int)$tt_ids[0];

                    foreach( $all_availability_terms as $term )
                    {
                        $tt_id = (int)$term->term_taxonomy_id;
                        if( $tt_id == $new_availability_id )
                        {
                            $new_availability_name = $term->name;
                        }
                    }
                }

                $current_user = wp_get_current_user();

	            // Add note/comment to property
	            $comment = array(
	                'note_type' => 'action',
	                'action' => 'property_availability_change',
	                'original_value' => $old_availability_name,
	                'new_value' => $new_availability_name
	            );

	            $data = array(
	                'comment_post_ID'      => (int)$object_id,
	                'comment_author'       => $current_user->display_name,
	                'comment_author_email' => 'propertyhive@noreply.com',
	                'comment_author_url'   => '',
	                'comment_date'         => date("Y-m-d H:i:s"),
	                'comment_content'      => serialize($comment),
	                'comment_approved'     => 1,
	                'comment_type'         => 'propertyhive_note',
	            );
	            $comment_id = wp_insert_comment( $data );

	            update_post_meta( $object_id, '_availability_change_date', date("Y-m-d H:i:s") );
            }
        }
    }

	public static function related_to_or_post_id( $clauses )
	{
		global $wpdb, $post;

		if ( strpos($clauses['where'], 'related_to') !== FALSE )
		{
			$clauses['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $clauses['join'] );

			// Remove main post ID constraint as it's handled by OR below
			$strpos_post_id = strpos($clauses['where'], 'comment_post_ID');
			if ( $strpos_post_id !== FALSE )
			{
				$strpos_next_and = strpos($clauses['where'], 'AND', $strpos_post_id);
				if ( $strpos_next_and !== FALSE )
				{
					$clauses['where'] = substr_replace($clauses['where'], ' 1=1 ', $strpos_post_id, $strpos_next_and - $strpos_post_id );
				}
			}

			// we're searching for related_to so should check where main post is comment_post_ID
			$clauses['where'] = str_replace( 
				$wpdb->prefix . 'commentmeta.meta_key', 
				'( ' . $wpdb->prefix . 'commentmeta.meta_key', 
				$clauses['where'] 
			);
			$clauses['where'] .= ' OR comment_post_ID = "' . $post->ID . '" ) ';
		}

		return $clauses;
	}

	public static function insert_note( $post_id, $comment )
	{
		$current_user = wp_get_current_user();

		$post_type = get_post_type( $post_id );

		$related_to = array( $post_id );

		switch ( $post_type )
		{
			case "property": {
				// get property owner
				$owner_contact_ids = get_post_meta( $post_id, '_owner_contact_id', TRUE );
				if ( !empty($owner_contact_ids) )
				{
					if ( !is_array($owner_contact_ids) )
					{
						$owner_contact_ids = array( $owner_contact_ids );
					}
					foreach ( $owner_contact_ids as $owner_contact_id )
					{
						$related_to[] = $owner_contact_id;
					}
				}
				break;
			}
			case "contact": {
				// check contact type, then add to property if owner
				$contact_types = get_post_meta( $post_id, '_contact_types', TRUE );
				if ( in_array('owner', $contact_types) )
				{
					// this contact is an owner
					// get properties
					$args = array(
						'post_type' => 'property',
						'nopaging' => true,
						'fields' => 'ids',
						'meta_query' => array(
							'relation' => 'OR',
							array(
								'key' => '_owner_contact_id',
								'value' => $post_id,
								'compare' => '=',
							),
							array(
								'key' => '_owner_contact_id',
								'value' => '"' . $post_id . '"',
								'compare' => 'LIKE',
							),
						)
					);

					$property_query = new WP_Query( $args );

					if ( $property_query->have_posts() )
					{
						while ( $property_query->have_posts() )
						{
							$property_query->the_post();

							$related_to[] = get_the_ID();
						}
					}
					wp_reset_postdata();
				}
				break;
			}
			case "appraisal": {
				// get potential owner
				$property_owner_contact_id = get_post_meta( $post_id, '_property_owner_contact_id', TRUE );
				if ( $property_owner_contact_id != '' )
				{
					$related_to[] = $property_owner_contact_id;
				}
				break;
			}
			case "viewing":
			case "offer":
			case "sale": {
				// get property
				$property_id = get_post_meta( $post_id, '_property_id', TRUE );
				if ( $property_id != '' )
				{
					$related_to[] = $property_id;

					// get property owner
					$owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
					if ( !empty($owner_contact_ids) )
					{
						if ( !is_array($owner_contact_ids) )
						{
							$owner_contact_ids = array( $owner_contact_ids );
						}
						foreach ( $owner_contact_ids as $owner_contact_id )
						{
							$related_to[] = $owner_contact_id;
						}
					}
				}

				// get applicant
				$applicant_ids = get_post_meta( $post_id, '_applicant_contact_id' );
				if ( !empty($applicant_ids) )
				{
					if ( !is_array($applicant_ids) )
					{
						$applicant_ids = array( $applicant_ids );
					}
					foreach ( $applicant_ids as $applicant_id )
					{
						$related_to[] = $applicant_id;
					}
				}
				break;
			}
		}

		$related_to = apply_filters( 'property_insert_note_related_to', $related_to, $post_id );

		$related_to = array_filter( $related_to );

		// Ensure they all go in as strings to allow LIKE query to work when querying related_to
		$new_related_to = array();
		foreach ( $related_to as $related_to_value )
		{
			$new_related_to[] = (string)$related_to_value;
		}

        $data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $current_user->display_name,
            'comment_author_email' => 'propertyhive@noreply.com',
            'comment_author_url'   => '',
            'comment_date'         => date("Y-m-d H:i:s"),
            'comment_content'      => serialize($comment),
            'comment_approved'     => 1,
            'comment_type'         => 'propertyhive_note',
            'comment_meta'		   => array(
            	'related_to' => $new_related_to,
            ),
        );
        $comment_id = wp_insert_comment( $data );

        return $comment_id;
	}

	public static function check_price_change( $meta_id, $object_id, $meta_key, $meta_value )
	{
		if ( get_post_type($object_id) == 'property' && ( $meta_key == '_price' || $meta_key == '_rent' ) && apply_filters( 'propertyhive_add_property_price_change_note', true ) === true )
		{
			$original_value = get_post_meta( $object_id, $meta_key, TRUE );

			if ( $original_value != $meta_value )
			{
				$current_user = wp_get_current_user();

	            // Add note/comment to property
	            $comment = array(
	                'note_type' => 'action',
	                'action' => 'property_price_change',
	                'original_value' => $original_value,
	                'new_value' => $meta_value
	            );

	            $data = array(
	                'comment_post_ID'      => (int)$object_id,
	                'comment_author'       => $current_user->display_name,
	                'comment_author_email' => 'propertyhive@noreply.com',
	                'comment_author_url'   => '',
	                'comment_date'         => date("Y-m-d H:i:s"),
	                'comment_content'      => serialize($comment),
	                'comment_approved'     => 1,
	                'comment_type'         => 'propertyhive_note',
	            );
	            $comment_id = wp_insert_comment( $data );

	            update_post_meta( $object_id, '_price_change_date', date("Y-m-d H:i:s") );
			}
		}
	}

	public static function check_on_market_add( $object_id, $meta_key, $meta_value )
	{
		if ( get_post_type($object_id) == 'property' && $meta_key == '_on_market' && apply_filters( 'propertyhive_add_property_on_market_change_note', true ) === true )
		{
			if ( $meta_value == 'yes' )
			{
				$note_action = 'property_on_market';

				$current_user = wp_get_current_user();

	            // Add note/comment to property
	            $comment = array(
	                'note_type' => 'action',
	                'action' => $note_action,
	            );

	            $data = array(
	                'comment_post_ID'      => (int)$object_id,
	                'comment_author'       => $current_user->display_name,
	                'comment_author_email' => 'propertyhive@noreply.com',
	                'comment_author_url'   => '',
	                'comment_date'         => date("Y-m-d H:i:s"),
	                'comment_content'      => serialize($comment),
	                'comment_approved'     => 1,
	                'comment_type'         => 'propertyhive_note',
	            );
	            $comment_id = wp_insert_comment( $data );

	            update_post_meta( $object_id, '_on_market_change_date', date("Y-m-d H:i:s") );
			}
		}
	}

	public static function check_on_market_update( $meta_id, $object_id, $meta_key, $meta_value )
	{
		if ( get_post_type($object_id) == 'property' && $meta_key == '_on_market' && apply_filters( 'propertyhive_add_property_on_market_change_note', true ) === true  )
		{
			$original_value = get_post_meta( $object_id, $meta_key, TRUE );

			if ( $original_value != $meta_value )
			{
				$note_action = 'property_off_market';
				if ($meta_value == 'yes')
				{
					$note_action = 'property_on_market';
				}

				$current_user = wp_get_current_user();

	            // Add note/comment to property
	            $comment = array(
	                'note_type' => 'action',
	                'action' => $note_action,
	            );

	            $data = array(
	                'comment_post_ID'      => (int)$object_id,
	                'comment_author'       => $current_user->display_name,
	                'comment_author_email' => 'propertyhive@noreply.com',
	                'comment_author_url'   => '',
	                'comment_date'         => date("Y-m-d H:i:s"),
	                'comment_content'      => serialize($comment),
	                'comment_approved'     => 1,
	                'comment_type'         => 'propertyhive_note',
	            );
	            $comment_id = wp_insert_comment( $data );

	            update_post_meta( $object_id, '_on_market_change_date', date("Y-m-d H:i:s") );
			}
		}
	}

	/**
	 * Exclude propertyhive notes from queries and RSS.
	 *
	 * This code should exclude propertyhive_note comments from queries. Some queries (like the recent comments widget on the dashboard) are hardcoded.
	 * @param  array $clauses
	 * @return array
	 */
	public static function exclude_note_comments( $clauses ) {
		//global $wpdb, $typenow;

		if ( is_admin() && function_exists( 'get_current_screen' ) )
		{
			$screen = get_current_screen();

			if ( 
				( isset($screen->id) && in_array( $screen->id, apply_filters( 'propertyhive_post_types_with_notes', array( 'property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy' ) ) ) )
				||
				( wp_doing_ajax() && isset($_POST['action']) && ($_POST['action'] == 'propertyhive_get_notes_grid' || $_POST['action'] == 'propertyhive_merge_contact_records') )
				||
				( wp_doing_ajax() && isset($_GET['action']) && strpos($_GET['action'], 'propertyhive_') !== FALSE && strpos($_GET['action'], '_lightbox') !== FALSE )
				||
				( isset($_GET['page']) && substr($_GET['page'], 0, 3) == 'ph-' )
			)
			{
				return $clauses; // Don't hide when viewing Property Hive record
			}
		}

		$clauses['where'] .= ' AND comment_type != "propertyhive_note"';   

		return $clauses;
	}

	/**
	 * Delete comments count cache whenever there is
	 * new comment or the status of a comment changes. Cache
	 * will be regenerated next time PH_Comments::wp_count_comments()
	 * is called.
	 *
	 * @return void
	 */
	public static function delete_comments_count_cache() {
		delete_transient( 'ph_count_comments' );
	}
	/**
	 * Remove propertyhive notes from wp_count_comments().
	 * @since  1.0.0
	 * @param  object $stats
	 * @param  int $post_id
	 * @return object
	 */
	public static function wp_count_comments( $stats, $post_id ) {
		global $wpdb;
		if ( 0 === $post_id ) {
			$stats = get_transient( 'ph_count_comments' );
			if ( ! $stats ) {
				$stats = array();
				$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} WHERE comment_type != 'propertyhive_note' GROUP BY comment_approved", ARRAY_A );
				$total = 0;
				$approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );
				foreach ( (array) $count as $row ) {
					// Don't count post-trashed toward totals
					if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
						$total += $row['num_comments'];
					}
					if ( isset( $approved[ $row['comment_approved'] ] ) ) {
						$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
					}
				}
				$stats['total_comments'] = $total;
				$stats['all'] = $total;
				foreach ( $approved as $key ) {
					if ( empty( $stats[ $key ] ) ) {
						$stats[ $key ] = 0;
					}
				}
				$stats = (object) $stats;
				set_transient( 'ph_count_comments', $stats );
			}
		}
		return $stats;
	}
}

PH_Comments::init();