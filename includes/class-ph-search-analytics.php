<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PH_Search_Analytics
{
	private $recorded = false;

	public function __construct()
	{
		add_action(
			'template_redirect',
			array( $this, 'look_for_submitted_search' )
		);

		add_action(
			'propertyhive_property_search_performed',
			array( $this, 'record_property_search' )
		);

		add_action(
			'propertyhive_cleanup_search_analytics',
			array( $this, 'clear_old_searches' )
		);
	}

	public function look_for_submitted_search()
	{
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() )
		{
			return;
		}

		if ( current_user_can( 'manage_propertyhive' ) ) 
		{
		    return;
		}

		if ( 
			!is_post_type_archive( 'property' ) && 
			!is_page( ph_get_page_id( 'search_results' ) ) 
		)
		{
			return;
		}

		if ( apply_filters( 'propertyhive_enable_search_analytics', true ) === false )
		{
			return;
		}

		// Ignore page 2 onwards
		if ( is_paged() )
		{
			return;
		}

		do_action( 'propertyhive_property_search_performed' );
	}

	public function record_property_search()
	{
		global $wpdb;

		if ( $this->recorded )
		{
			return;
		}

		$this->recorded = true;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Append a UTC timestamp to the plugin's custom analytics table using wpdb field formats.
		$result = $wpdb->insert(
			$wpdb->prefix . 'ph_search_log',
			array(
				'searched_at' => current_time( 'mysql', true ),
			),
			array(
				'%s',
			)
		);

		if ( false === $result )
		{
			$this->recorded = false;
		}
	}

	public function clear_old_searches()
	{
		global $wpdb;

		$cutoff     = gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) );

		do
		{
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Retention deletes batches from the custom analytics table; its counts are read live and have no object-cache entries.
			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}ph_search_log
					WHERE searched_at < %s
					ORDER BY searched_at ASC
					LIMIT 500",
					$cutoff
				)
			);
		}
		while ( 500 === $deleted );
	}
}

new PH_Search_Analytics();