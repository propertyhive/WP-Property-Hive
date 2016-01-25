<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PropertyHive third party contacts
 *
 * The PropertyHive third party contacts class stores data regarding third party contacts.
 *
 * @class       PH_Third_Party_Contacts
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Third_Party_Contacts {

	private $categories;

	public function __construct() {
		$this->categories = array(
			'1' => __( 'Accountant', 'propertyhive' ),
			'2' => __( 'Architect', 'propertyhive' ),
			'3' => __( 'Board Contractor', 'propertyhive' ),
			'4' => __( 'Builder', 'propertyhive' ),
			'5' => __( 'Cleaner', 'propertyhive' ),
			'6' => __( 'Decorator', 'propertyhive' ),
			'7' => __( 'Electrician', 'propertyhive' ),
			'8' => __( 'Gas Engineer', 'propertyhive' ),
			'9' => __( 'Plumbers', 'propertyhive' ),
			'10' => __( 'Removals', 'propertyhive' ),
			'11' => __( 'Roofer', 'propertyhive' ),
			'12' => __( 'Solictor', 'propertyhive' ),
			'13' => __( 'Surveyor', 'propertyhive' ),
			'14' => __( 'Other', 'propertyhive' ),
		);
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param  mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( 'categories' == $key ) {
			return $this->get_categories();
		}
	}

	public function get_categories() {
		return $this->categories;
	}

	public function get_category( $category_id ) {
		if ( isset( $this->categories[$category_id] ) )
		{
			return $this->categories[$category_id];
		}
		return false;
	}

}