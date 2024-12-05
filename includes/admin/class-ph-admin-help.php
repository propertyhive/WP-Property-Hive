<?php
/**
 * Add some content to the help tab
 *
 * @package     PropertyHive\Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PH_Admin_Help' ) ) :

/**
 * PH_Admin_Help Class.
 */
class PH_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs.
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, ph_get_screen_ids() ) ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'propertyhive_support_tab',
				'title'   => __( 'Help &amp; Support', 'propertyhive' ),
				'content' =>
					'<h2>' . __( 'Help &amp; Support', 'propertyhive' ) . '</h2>' .
					'<p>' . sprintf(
						__( 'Should you need help understanding, using, or extending Property Hive, <a href="%s" target="_blank">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.', 'propertyhive' ),
						'https://docs.wp-property-hive.com'
					) . '</p>' .
					'<p>' . __( 'Before asking for help, we recommend checking the documentation. Otherwise, please get in touch and a member of our team will be happy to assist.', 'propertyhive' ) . '</p>' .
					'<p><a href="https://docs.wp-property-hive.com" class="button button-primary">' . __( 'View documentation', 'propertyhive' ) . '</a> <a href="https://wp-property-hive.com/support/?src=wordpress-help-tab" class="button" target="_blank">' . __( 'Get support', 'propertyhive' ) . '</a></p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'propertyhive' ) . '</strong></p>' .
			'<p><a href="https://wp-property-hive.com?src=wordpress-help-tab" target="_blank">' . __( 'Property Hive website', 'propertyhive' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/plugins/propertyhive/" target="_blank">' . __( 'WordPress.org project', 'propertyhive' ) . '</a></p>' .
			'<p><a href="https://github.com/propertyhive/WP-Property-Hive" target="_blank">' . __( 'GitHub project', 'propertyhive' ) . '</a></p>' .
			'<p><a href="https://wp-property-hive.com/add-ons/?src=wordpress-help-tab" target="_blank">' . __( 'Add ons', 'propertyhive' ) . '</a></p>'
		);
	}
}

endif;

return new PH_Admin_Help();