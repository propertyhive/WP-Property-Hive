<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle users as contacts and vice versa
 *
 * @class 		PH_User_Contacts
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_User_Contacts {

	/**
	 * Hook in methods
	 */
	public static function init() {
		//add_action( 'user_register', array( __CLASS__, 'user_register' ), 10, 1 );
		//add_action( 'profile_update', array( __CLASS__, 'profile_update' ), 10, 2 );
		//add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

		// Hide users with role 'property_hive_contact' by excluding them from user queries
		//add_action( 'pre_user_query', array( __CLASS__, 'pre_user_query' ) );

		add_action( 'init', array( __CLASS__, 'listen_for_logout' ) );

		add_action( 'template_redirect', array( __CLASS__, 'redirect_to_my_account_if_logged_in' ) );
	}

	/**
	 * Return to my account page if accessing login or register page and already logged in
	 * @return void
	 */
	public static function redirect_to_my_account_if_logged_in() {

		$login_page_id = get_option( 'propertyhive_applicant_login_page_id', '' );
		$register_page_id = get_option( 'propertyhive_applicant_registration_page_id', '' );

		if ( 
			is_user_logged_in() && 
			!empty(get_queried_object_id()) &&
			( 
				( !empty($login_page_id) && get_queried_object_id() == $login_page_id ) || 
				( !empty($register_page_id) && get_queried_object_id() == $register_page_id )
			) 
		)
		{
			$my_account_page_id = get_option( 'propertyhive_my_account_page_id', '' );
			if ( !empty($my_account_page_id) )
			{
				wp_redirect( get_permalink($my_account_page_id) );
				exit();
			}
		}
	}

	/**
	 * Listen for logout parameter
	 * @return void
	 */
	public static function listen_for_logout( $user_id ) {

		if ( isset($_GET['logout']) && $_GET['logout'] == 1 )
		{
			wp_logout();

			// For now redirect to homepage with filter
			wp_redirect( apply_filters( 'property_logout_redirect_url', home_url( '/' ) ) );
			exit;
		}
	}

	/**
	 * When user is registered ensure they're also entered as a contact
	 * @return array
	 */
	public static function user_register( $user_id ) {

		remove_action( 'save_post', array( __CLASS__, 'save_post' ), 10 );

		// Shouldn't ever come through this route

		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

	}

	/**
	 * When user profiles are updated, ensure the contact record is kept up-to-date also
	 * @return array
	 */
	public static function profile_update( $user_id, $old_user_data ) {

		remove_action( 'save_post', array( __CLASS__, 'save_post' ), 10 );

		// Shouldn't ever come through this route

		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

	}

	/**
	 * When saving contacts in Property Hive, make sure user is updated also
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 *
	 * @return array
	 */
	public static function save_post( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		    return;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
		    return;

		if ( ! current_user_can( 'edit_post', $post_id ) )
		    return;

		if ( false !== wp_is_post_revision( $post_id ) )
		    return;

		if ( get_post_type($post_id) != 'contact' )
			return;

		if ( get_post_status($post_id) != 'publish' )
			return;

		// Check if associated user exists
		$contact = new PH_Contact( $post_id );

		if ( $contact->email_address != '' )
		{
			$display_name = get_the_title( $post_id );

			// No associated user. Need to create one
			$userdata = array(
			    'user_login'  	=> $contact->email_address,
			    'user_email'  	=> $contact->email_address,
			    'display_name' 	=> $display_name,
			);

			if ( !empty($display_name) )
			{
				$name_parts = explode( ' ', $display_name );

				if ( count($name_parts) > 1 )
				{
					$userdata['last_name'] = array_pop($name_parts);
					$userdata['first_name'] = implode(' ', $name_parts);
				}
				else
				{
					$userdata['last_name'] = $display_name;
				}
			}

			if ( $contact->user_id == '' )
			{
				$userdata['role'] = 'property_hive_contact';
		    	$userdata['user_pass'] = NULL;  // When creating a user, `user_pass` is expected.
			}
			else
			{
				// contact already associated with a user. Update user
				$userdata['ID'] = $contact->user_id;
			}

			$user_id = wp_insert_user( $userdata );

			if ( ! is_wp_error( $user_id ) ) 
			{
			    update_post_meta( $post_id, '_user_id', $user_id );
			}
			else
			{
				// Something went wrong when inserting the user
				var_dump($user_id);
				die();
			}
		}
		else
		{
			// Contact doesn't have an email address
			
		}
	}

	public static function pre_user_query( $user_search )
	{
		global $wpdb;

		$user_search->query_where .= " AND NOT EXISTS (
            SELECT 
            	{$wpdb->usermeta}.user_id 
            FROM 
            	$wpdb->usermeta 
            WHERE 
            	{$wpdb->usermeta}.meta_key = 'wp_capabilities' 
            AND 
            	{$wpdb->usermeta}.meta_value LIKE '%property_hive_contact%' 
            AND 
            	$wpdb->usermeta.user_id = {$wpdb->users}.ID
           ) ";

        return $user_search;
	}
}

PH_User_Contacts::init();