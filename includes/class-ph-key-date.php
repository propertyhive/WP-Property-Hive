<?php

if ( ! defined( 'ABSPATH' ) )
{
	// Exit if accessed directly
	exit;
}

class PH_Key_Date {

	/** @var int */
	public $id;

	/** @var string */
	private $description;

	public function __construct( WP_Post $post ) {
		$this->id = $post->ID;
		$this->description = $post->post_title;
	}

	public function description() {
		return $this->description;
	}

	public function notes() {
		return $this->_key_date_notes;
	}

	public function property() {
		return new PH_Property(get_post($this->_property_id));
	}

	public function tenancy() {
		return new PH_Tenancy($this->_tenancy_id);
	}

	public function date_due() {
		return new DateTime($this->_date_due);
	}

	public function key_date_type_id() {
		return $this->_key_date_type_id;
	}

	public function status() {

		switch ($this->_key_date_status)
		{
			case 'pending':

				$overdue_threshold = ( date_format($this->date_due(), 'H:i') == '00:00' ) ? 'Today' : 'Now';
				switch(true)
				{
					case $this->date_due() < new DateTime($overdue_threshold):
						return 'overdue';
					case $this->date_due() <= new DateTime('+ ' . apply_filters( 'propertyhive_key_date_upcoming_days', 7 ) . ' DAYS'):
						return 'upcoming';
					default:
						return 'pending';
				}
			case 'booked':
			case 'complete':
			default:
				return $this->_key_date_status;
		}
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
		if ($value == '')
		{
			$value = get_post_meta( $this->id, '_' . $key, true );
		}
		return $value;
	}
}
