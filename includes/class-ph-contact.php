<?php
/**
 * Contact
 *
 * The PropertyHive contact class handles contact data.
 *
 * @class       PH_Property
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      BIOSTALL
 */
class PH_Contact {

    /** @public int Contact (post) ID */
    public $id;

    /**
     * Get the contact if ID is passed, otherwise the contact is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_contact( $id );
        }
    }

    /**
     * Gets a contact from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_contact( $id = 0 ) {
        if ( ! $id ) {
            return false;
        }
        if ( $result = get_post( $id ) ) {
            $this->populate( $result );
            return true;
        }
        return false;
    }
    
    /**
     * __isset function.
     *
     * @access public
     * @param mixed $key
     * @return bool
     */
    public function __isset( $key ) {
        if ( ! $this->id ) {
            return false;
        }
        return metadata_exists( 'post', $this->id, '_' . $key );
    }

    /**
     * __get function.
     *
     * @access public
     * @param mixed $key
     * @return mixed
     */
    public function __get( $key ) {
        // Get values or default if not set
        $value = get_post_meta( $this->id, $key, true );

        return $value;
    }
    
    /**
     * Populates a contact from the loaded post data.
     *
     * @access public
     * @param mixed $result
     * @return void
     */
    public function populate( $result ) {
        // Standard post data
        $this->id                  = $result->ID;
        $this->post_title          = $result->post_title;
        $this->post_status         = $result->post_status;
    }
    
    /**
     * Adds a note (comment) to the contact
     *
     * @access public
     * @param string $note Note to add
     * @return id Comment ID
     */
    public function add_note( $note ) {

        if ( is_user_logged_in() ) 
        {
            $user = get_user_by( 'id', get_current_user_id() );
        
            $commentdata = array(
                'comment_post_ID' => $this->id,
                'comment_author' => $user->display_name,
                'comment_author_email' => $user->user_email,
                'comment_author_url' => '',
                'comment_content' => $note,
                'comment_type' => 'propertyhive_note',
                'comment_parent' => 0,
                'user_id' => $user->ID,
                'comment_agent' => 'PropertyHive',
                'comment_date' => current_time('mysql'),
                'comment_approved' => 1,
            );
            
            $comment_id = wp_insert_comment( $commentdata );
            
            return $comment_id;
        }
    }
}
