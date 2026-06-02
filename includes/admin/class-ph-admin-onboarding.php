<?php
/**
 * Property Hive first-run onboarding.
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PH_Admin_Onboarding' ) ) :

/**
 * PH_Admin_Onboarding class.
 */
class PH_Admin_Onboarding {

	const OPTION_NAME = 'propertyhive_onboarding';

	const NONCE_ACTION = 'propertyhive-onboarding';

	const RESTART_NONCE_ACTION = 'propertyhive-restart-onboarding';

	/**
	 * Wizard step ids.
	 *
	 * @var array
	 */
	private $steps = array( 'intro', 'departments', 'country', 'office', 'usage', 'demo-data', 'complete' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_dashboard_pages' ) );
		add_action( 'admin_head', array( $this, 'hide_dashboard_page_menu_item' ) );
		add_action( 'admin_init', array( $this, 'maybe_restart_onboarding' ) );
		add_action( 'load-dashboard_page_ph-onboarding', array( $this, 'remove_admin_toolbar_html_class' ) );
		add_filter( 'propertyhive_screen_ids', array( $this, 'add_screen_id' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_action( 'propertyhive_settings_start', array( $this, 'output_restart_onboarding_notice' ) );

		add_action( 'wp_ajax_propertyhive_onboarding_save_step', array( $this, 'ajax_save_step' ) );
		add_action( 'wp_ajax_propertyhive_onboarding_skip', array( $this, 'ajax_skip' ) );
		add_action( 'wp_ajax_propertyhive_onboarding_track', array( $this, 'ajax_track' ) );
	}

	/**
	 * Register the hidden onboarding page.
	 */
	public function admin_dashboard_pages() {
		add_dashboard_page(
			__( 'Property Hive Setup', 'propertyhive' ),
			__( 'Property Hive Setup', 'propertyhive' ),
			'manage_options',
			'ph-onboarding',
			array( $this, 'output' )
		);
	}

	/**
	 * Hide the dashboard submenu item.
	 */
	public function hide_dashboard_page_menu_item() {
		remove_submenu_page( 'index.php', 'ph-onboarding' );
	}

	/**
	 * Prepare the HTML element classes for the full-screen onboarding page.
	 */
	public function remove_admin_toolbar_html_class() {
		ob_start( array( $this, 'filter_admin_html_class' ) );
	}

	/**
	 * Remove wp-toolbar from the admin HTML tag and add an onboarding class.
	 *
	 * @param string $buffer Page output.
	 * @return string
	 */
	public function filter_admin_html_class( $buffer ) {
		return preg_replace_callback(
			'/<html\s+class=(["\'])([^"\']*)\1([^>]*)>/i',
			array( $this, 'remove_wp_toolbar_from_html_tag' ),
			$buffer,
			1
		);
	}

	/**
	 * Strip wp-toolbar and add the onboarding class while preserving attributes.
	 *
	 * @param array $matches Regex matches.
	 * @return string
	 */
	private function remove_wp_toolbar_from_html_tag( $matches ) {
		$quote   = $matches[1];
		$classes = preg_split( '/\s+/', trim( $matches[2] ) );
		$classes = array_filter(
			$classes,
			function( $class ) {
				return 'wp-toolbar' !== $class;
			}
		);

		$classes[] = 'propertyhive-onboarding-html';
		$classes   = array_unique( $classes );

		return '<html class=' . $quote . esc_attr( implode( ' ', $classes ) ) . $quote . $matches[3] . '>';
	}

	/**
	 * Add onboarding screen to Property Hive admin screens.
	 *
	 * @param array $screen_ids Screen ids.
	 * @return array
	 */
	public function add_screen_id( $screen_ids ) {
		$screen_ids[] = 'dashboard_page_ph-onboarding';
		return $screen_ids;
	}

	/**
	 * Add body class on the full-screen onboarding page.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		$screen = get_current_screen();
		if ( $screen && 'dashboard_page_ph-onboarding' === $screen->id ) {
			$classes .= ' propertyhive-onboarding-screen';
		}
		return $classes;
	}

	/**
	 * Get onboarding state.
	 *
	 * @return array
	 */
	public static function get_state() {
		$state = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $state ) ) {
			$state = array();
		}

		return wp_parse_args(
			$state,
			array(
				'status'             => 'not_started',
				'last_step'          => '',
				'departments'        => array(),
				'country'            => '',
				'office'             => array(),
				'office_id'          => 0,
				'usage'              => array(),
				'usage_tracking'     => true,
				'demo_data_imported' => false,
				'events'             => array(),
			)
		);
	}

	/**
	 * Save onboarding state.
	 *
	 * @param array $updates State updates.
	 * @return array
	 */
	private static function update_state( $updates ) {
		$state = self::get_state();
		$state = array_merge( $state, $updates );

		update_option( self::OPTION_NAME, $state, false );

		return $state;
	}

	/**
	 * Add a local event, and optionally send it remotely.
	 *
	 * @param string $event Event name.
	 * @param array  $data  Event data.
	 */
	public static function track_event( $event, $data = array() ) {
		$event = sanitize_key( $event );
		if ( '' === $event ) {
			return;
		}

		$state  = self::get_state();
		$events = isset( $state['events'] ) && is_array( $state['events'] ) ? $state['events'] : array();

		$events[] = array(
			'event'      => $event,
			'data'       => self::sanitize_event_data( $data ),
			'created_at' => time(),
		);

		if ( count( $events ) > 50 ) {
			$events = array_slice( $events, -50 );
		}

		self::update_state( array( 'events' => $events ) );

		$endpoint = apply_filters( 'propertyhive_onboarding_tracking_endpoint', '' );
		if ( 'yes' !== get_option( 'propertyhive_data_sharing', 'no' ) || empty( $endpoint ) ) {
			return;
		}

		wp_remote_post(
			esc_url_raw( $endpoint ),
			array(
				'timeout' => 5,
				'body'    => array(
					'event'      => $event,
					'data'       => wp_json_encode( self::sanitize_event_data( $data ) ),
					'site_url'   => home_url(),
					'ph_version' => defined( 'PH_VERSION' ) ? PH_VERSION : '',
				),
			)
		);
	}

	/**
	 * Sanitize arbitrary event data.
	 *
	 * @param array $data Event data.
	 * @return array
	 */
	private static function sanitize_event_data( $data ) {
		if ( ! is_array( $data ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $data as $key => $value ) {
			$key = sanitize_key( $key );
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
			} else {
				$sanitized[ $key ] = sanitize_text_field( wp_unslash( $value ) );
			}
		}

		return $sanitized;
	}

	/**
	 * Save a wizard step via AJAX.
	 */
	public function ajax_save_step() {
		$this->check_ajax_permissions();

		$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		if ( ! in_array( $step, $this->steps, true ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid setup step.', 'propertyhive' ) ),
				400
			);
		}

		switch ( $step ) {
			case 'intro':
				$state = $this->save_intro_step();
				break;
			case 'departments':
				$state = $this->save_departments_step();
				break;
			case 'country':
				$state = $this->save_country_step();
				break;
			case 'office':
				$state = $this->save_office_step();
				break;
			case 'usage':
				$state = $this->save_usage_step();
				break;
			case 'demo-data':
				$state = $this->save_demo_data_step();
				break;
			case 'complete':
				$state = $this->save_complete_step();
				break;
		}

		self::track_event( 'step_saved', array( 'step' => $step ) );

		wp_send_json_success( $state );
	}

	/**
	 * Skip the wizard.
	 */
	public function ajax_skip() {
		$this->check_ajax_permissions();

		$step  = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$state = self::update_state(
			array(
				'status'     => 'skipped',
				'last_step'  => $step,
				'skipped_at' => time(),
			)
		);

		self::track_event( 'skipped', array( 'step' => $step ) );

		wp_send_json_success(
			array(
				'state'        => $state,
				'redirect_url' => admin_url( 'admin.php?page=ph-settings' ),
			)
		);
	}

	/**
	 * Track a view-only event.
	 */
	public function ajax_track() {
		$this->check_ajax_permissions();

		$event = isset( $_POST['event'] ) ? sanitize_key( wp_unslash( $_POST['event'] ) ) : '';
		$step  = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';

		if ( '' !== $event ) {
			self::track_event( $event, array( 'step' => $step ) );
		}

		wp_send_json_success();
	}

	/**
	 * Check whether the onboarding restart tool should be available.
	 *
	 * @return bool
	 */
	public static function can_restart_onboarding() {
		return in_array( self::get_environment_type(), array( 'local', 'development', 'staging' ), true );
	}

	/**
	 * Get the current WordPress environment type.
	 *
	 * @return string
	 */
	private static function get_environment_type() {
		if ( function_exists( 'wp_get_environment_type' ) ) {
			return wp_get_environment_type();
		}

		return 'production';
	}

	/**
	 * Restart the onboarding wizard from a gated admin URL.
	 */
	public function maybe_restart_onboarding() {
		if ( empty( $_GET['propertyhive_restart_onboarding'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to restart setup.', 'propertyhive' ) );
		}

		check_admin_referer( self::RESTART_NONCE_ACTION );

		if ( ! self::can_restart_onboarding() ) {
			wp_die( esc_html__( 'The setup wizard can only be restarted on local, development or staging sites.', 'propertyhive' ) );
		}

		delete_option( self::OPTION_NAME );
		self::track_event( 'restarted', array( 'environment_type' => self::get_environment_type() ) );

		wp_safe_redirect( admin_url( 'index.php?page=ph-onboarding' ) );
		exit;
	}

	/**
	 * Output a development-only setup restart prompt on the settings page.
	 */
	public function output_restart_onboarding_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! self::can_restart_onboarding() ) {
			return;
		}

		?>
		<div class="notice notice-info inline">
			<p>
				<strong><?php esc_html_e( 'Development setup tools', 'propertyhive' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: WordPress environment type. */
					esc_html__( 'This site is marked as %s, so administrators can restart the Property Hive setup wizard for testing.', 'propertyhive' ),
					'<code>' . esc_html( self::get_environment_type() ) . '</code>'
				);
				?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( $this->get_restart_onboarding_url() ); ?>"><?php esc_html_e( 'Restart setup wizard', 'propertyhive' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Get the onboarding restart URL.
	 *
	 * @return string
	 */
	private function get_restart_onboarding_url() {
		return wp_nonce_url(
			add_query_arg( 'propertyhive_restart_onboarding', '1', admin_url( 'admin.php?page=ph-settings' ) ),
			self::RESTART_NONCE_ACTION
		);
	}

	/**
	 * Validate AJAX permissions.
	 */
	private function check_ajax_permissions() {
		check_ajax_referer( self::NONCE_ACTION, 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to run setup.', 'propertyhive' ) ),
				403
			);
		}
	}

	/**
	 * Return a validation error response.
	 *
	 * @param string $message Error summary.
	 * @param array  $errors  Field errors.
	 */
	private function send_validation_error( $message, $errors = array() ) {
		wp_send_json_error(
			array(
				'message' => $message,
				'errors'  => $errors,
			),
			400
		);
	}

	/**
	 * Get the first validation error message.
	 *
	 * @param array  $errors   Field errors.
	 * @param string $fallback Fallback message.
	 * @return string
	 */
	private function get_first_validation_error_message( $errors, $fallback ) {
		foreach ( $errors as $message ) {
			if ( is_string( $message ) && '' !== $message ) {
				return $message;
			}
		}

		return $fallback;
	}

	/**
	 * Save intro step.
	 *
	 * @return array
	 */
	private function save_intro_step() {
		$usage_tracking = isset( $_POST['usage_tracking'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['usage_tracking'] ) );

		update_option( 'propertyhive_data_sharing', $usage_tracking ? 'yes' : 'no' );

		return self::update_state(
			array(
				'status'         => 'in_progress',
				'last_step'      => 'intro',
				'started_at'     => $this->get_started_at(),
				'usage_tracking' => $usage_tracking,
			)
		);
	}

	/**
	 * Save departments.
	 *
	 * @return array
	 */
	private function save_departments_step() {
		$departments = isset( $_POST['departments'] ) && is_array( $_POST['departments'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['departments'] ) ) : array();
		$allowed     = array( 'residential-sales', 'residential-lettings', 'commercial' );
		$departments = array_values( array_intersect( $allowed, $departments ) );

		if ( empty( $departments ) ) {
			$this->send_validation_error(
				__( 'Please choose at least one property sector.', 'propertyhive' ),
				array(
					'departments' => __( 'Please choose at least one property sector.', 'propertyhive' ),
				)
			);
		}

		update_option( 'propertyhive_active_departments_sales', in_array( 'residential-sales', $departments, true ) ? 'yes' : 'no' );
		update_option( 'propertyhive_active_departments_lettings', in_array( 'residential-lettings', $departments, true ) ? 'yes' : 'no' );
		update_option( 'propertyhive_active_departments_commercial', in_array( 'commercial', $departments, true ) ? 'yes' : 'no' );
		update_option( 'propertyhive_primary_department', reset( $departments ) );

		return self::update_state(
			array(
				'status'      => 'in_progress',
				'last_step'   => 'departments',
				'departments' => $departments,
				'started_at'  => $this->get_started_at(),
			)
		);
	}

	/**
	 * Save country.
	 *
	 * @return array
	 */
	private function save_country_step() {
		$country = isset( $_POST['country'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['country'] ) ) ) : '';

		$countries = $this->get_countries();
		if ( ! isset( $countries[ $country ] ) ) {
			$this->send_validation_error(
				__( 'Please choose a valid country.', 'propertyhive' ),
				array(
					'country' => __( 'Please choose a valid country.', 'propertyhive' ),
				)
			);
		}

		update_option( 'propertyhive_default_country', $country );
		update_option( 'propertyhive_countries', array( $country ) );

		if ( ! empty( $countries[ $country ]['currency_code'] ) ) {
			update_option( 'propertyhive_search_form_currency', sanitize_text_field( $countries[ $country ]['currency_code'] ) );
		}

		return self::update_state(
			array(
				'status'    => 'in_progress',
				'last_step' => 'country',
				'country'   => $country,
			)
		);
	}

	/**
	 * Save primary office details.
	 *
	 * @return array
	 */
	private function save_office_step() {
		$office = $this->sanitize_office_payload();
		$errors = $this->validate_office_payload( $office );

		if ( ! empty( $errors ) ) {
			$this->send_validation_error(
				$this->get_first_validation_error_message( $errors, __( 'Please check your office details.', 'propertyhive' ) ),
				$errors
			);
		}

		$office['email_address'] = sanitize_email( $office['email_address'] );

		$office_id = $this->get_primary_office_id();
		if ( ! $office_id ) {
			$office_id = wp_insert_post(
				array(
					'post_title'   => '' !== $office['name'] ? $office['name'] : __( 'My Office', 'propertyhive' ),
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => 'office',
				),
				true
			);

			if ( is_wp_error( $office_id ) || ! $office_id ) {
				wp_send_json_error(
					array( 'message' => __( 'Office details could not be saved.', 'propertyhive' ) ),
					500
				);
			}
		}

		update_post_meta( $office_id, 'primary', '1' );

		if ( '' !== $office['name'] && get_the_title( $office_id ) !== $office['name'] ) {
			wp_update_post(
				array(
					'ID'         => $office_id,
					'post_title' => $office['name'],
				)
			);
		}

		update_post_meta( $office_id, '_office_address_1', $office['address_1'] );
		update_post_meta( $office_id, '_office_address_2', $office['address_2'] );
		update_post_meta( $office_id, '_office_address_3', $office['address_3'] );
		update_post_meta( $office_id, '_office_address_4', $office['address_4'] );
		update_post_meta( $office_id, '_office_address_postcode', $office['postcode'] );

		foreach ( $this->get_office_contact_departments() as $department ) {
			$department_key = str_replace( 'residential-', '', $department );
			update_post_meta( $office_id, '_office_telephone_number_' . $department_key, $office['telephone_number'] );
			update_post_meta( $office_id, '_office_email_address_' . $department_key, $office['email_address'] );
		}

		return self::update_state(
			array(
				'status'    => 'in_progress',
				'last_step' => 'office',
				'office'    => $office,
				'office_id' => absint( $office_id ),
			)
		);
	}

	/**
	 * Save intended usage.
	 *
	 * @return array
	 */
	private function save_usage_step() {
		$usage = isset( $_POST['usage'] ) && is_array( $_POST['usage'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['usage'] ) ) : array();
		$usage = array_values( array_intersect( array( 'import_properties', 'crm', 'portal_uploads', 'not_sure' ), $usage ) );

		if ( empty( $usage ) ) {
			$this->send_validation_error(
				__( 'Please choose how you will use Property Hive.', 'propertyhive' ),
				array(
					'usage' => __( 'Please choose how you will use Property Hive.', 'propertyhive' ),
				)
			);
		}

		$crm_enabled = in_array( 'crm', $usage, true );
		$disabled    = $crm_enabled ? 'no' : 'yes';

		update_option( 'propertyhive_module_disabled_contacts', $disabled );
		update_option( 'propertyhive_module_disabled_appraisals', $disabled );
		update_option( 'propertyhive_module_disabled_viewings', $disabled );
		update_option( 'propertyhive_module_disabled_offers_sales', $disabled );
		update_option( 'propertyhive_module_disabled_tenancies', $disabled );

		return self::update_state(
			array(
				'status'    => 'in_progress',
				'last_step' => 'usage',
				'usage'     => $usage,
			)
		);
	}

	/**
	 * Save demo-data result.
	 *
	 * @return array
	 */
	private function save_demo_data_step() {
		$demo_choice = isset( $_POST['demo_data_choice'] ) ? sanitize_key( wp_unslash( $_POST['demo_data_choice'] ) ) : '';
		$imported = isset( $_POST['demo_data_imported'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['demo_data_imported'] ) );

		if ( ! in_array( $demo_choice, array( 'yes', 'no' ), true ) ) {
			$this->send_validation_error(
				__( 'Please choose whether to import demo data.', 'propertyhive' ),
				array(
					'demo_data_choice' => __( 'Please choose whether to import demo data.', 'propertyhive' ),
				)
			);
		}

		if ( 'yes' === $demo_choice && ! $imported ) {
			$this->send_validation_error(
				__( 'Demo data could not be imported. Please try again or choose No.', 'propertyhive' ),
				array(
					'demo_data_choice' => __( 'Demo data could not be imported. Please try again or choose No.', 'propertyhive' ),
				)
			);
		}

		self::track_event( $imported ? 'demo_data_completed' : 'demo_data_skipped' );

		return self::update_state(
			array(
				'status'             => 'in_progress',
				'last_step'          => 'demo-data',
				'demo_data_imported' => $imported,
			)
		);
	}

	/**
	 * Complete the wizard.
	 *
	 * @return array
	 */
	private function save_complete_step() {
		$state = self::update_state(
			array(
				'status'       => 'completed',
				'last_step'    => 'complete',
				'completed_at' => time(),
			)
		);

		self::track_event( 'completed' );

		return $state;
	}

	/**
	 * Get existing start time or current time.
	 *
	 * @return int
	 */
	private function get_started_at() {
		$state = self::get_state();
		if ( ! empty( $state['started_at'] ) ) {
			return absint( $state['started_at'] );
		}

		self::track_event( 'started' );

		return time();
	}

	/**
	 * Output onboarding page.
	 */
	public function output() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to run setup.', 'propertyhive' ) );
		}

		$state           = self::get_state();
		$countries       = $this->get_countries();
		$default_country = $this->get_default_country();
		$office          = $this->get_office_defaults( $state );
		$demo_active     = class_exists( 'PH_Demo_Data' );
		$video_url       = apply_filters( 'propertyhive_onboarding_video_url', '' );
		$departments     = ! empty( $state['departments'] ) && is_array( $state['departments'] ) ? $state['departments'] : array( 'residential-sales', 'residential-lettings' );
		$usage           = ! empty( $state['usage'] ) && is_array( $state['usage'] ) ? $state['usage'] : array( 'crm' );
		$usage_tracking  = 'yes' === get_option( 'propertyhive_data_sharing', 'yes' );
		$demo_choice     = ! empty( $state['demo_data_imported'] ) ? 'yes' : 'no';

		$current_step = $this->get_current_step( $state );

		self::track_event( 'step_viewed', array( 'step' => $current_step ) );
		$this->localize_script( $demo_active );
		?>
		<div class="ph-onboarding" data-current-step="<?php echo esc_attr( $current_step ); ?>" data-demo-imported="<?php echo ! empty( $state['demo_data_imported'] ) ? 'yes' : 'no'; ?>">
			<div class="ph-onboarding__chrome">
				<div class="ph-onboarding__brand" aria-label="<?php esc_attr_e( 'Property Hive', 'propertyhive' ); ?>">
					<img src="<?php echo esc_url( PH()->plugin_url() . '/assets/images/admin/propertyhive-logo-onboarding.png' ); ?>" alt="<?php esc_attr_e( 'Property Hive', 'propertyhive' ); ?>">
				</div>
				<button type="button" class="ph-onboarding__skip" data-ph-onboarding-skip><?php esc_html_e( 'Skip setup', 'propertyhive' ); ?></button>
			</div>

			<div class="ph-onboarding__shell">
				<main class="ph-onboarding__content">
					<div class="ph-onboarding__progress" aria-hidden="true">
						<span data-progress-bar></span>
					</div>

					<section class="ph-onboarding__panel ph-onboarding__panel--intro is-active" data-step="intro">
						<header class="ph-onboarding__intro">
							<h1><?php esc_html_e( 'Answer a few quick questions so Property Hive starts with the right departments, region and tools for your agency.', 'propertyhive' ); ?></h1>
						</header>
					</section>

					<section class="ph-onboarding__panel" data-step="departments">
						<h2><?php esc_html_e( 'Which property sectors do you deal in?', 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'Choose every department you want active in Property Hive.', 'propertyhive' ); ?></p>
						<div class="ph-onboarding__cards" data-input-group="departments" data-validation-field="departments">
							<?php $this->output_choice_card( 'departments', 'residential-sales', __( 'Residential sales', 'propertyhive' ), __( 'Market and manage sales properties.', 'propertyhive' ), in_array( 'residential-sales', $departments, true ) ); ?>
							<?php $this->output_choice_card( 'departments', 'residential-lettings', __( 'Residential lettings', 'propertyhive' ), __( 'Handle lettings, tenancies and landlords.', 'propertyhive' ), in_array( 'residential-lettings', $departments, true ) ); ?>
							<?php $this->output_choice_card( 'departments', 'commercial', __( 'Commercial', 'propertyhive' ), __( 'Work with commercial property records.', 'propertyhive' ), in_array( 'commercial', $departments, true ) ); ?>
						</div>
						<?php $this->output_field_error( 'departments' ); ?>
						<?php $this->output_settings_note( __( 'General', 'propertyhive' ) ); ?>
					</section>

					<section class="ph-onboarding__panel" data-step="country">
						<h2><?php esc_html_e( 'Where does your agency operate?', 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'This sets your default country and currency defaults for Property Hive.', 'propertyhive' ); ?></p>
						<label class="ph-onboarding__field" data-validation-field="country">
							<span><?php esc_html_e( 'Country', 'propertyhive' ); ?></span>
							<select name="country" data-country-select required aria-describedby="ph-onboarding-error-country">
								<?php foreach ( $countries as $code => $country ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $default_country, $code ); ?>><?php echo esc_html( $country['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php $this->output_field_error( 'country' ); ?>
						</label>
						<?php $this->output_settings_note( __( 'General > International', 'propertyhive' ) ); ?>
					</section>

					<section class="ph-onboarding__panel" data-step="office">
						<h2><?php esc_html_e( 'What are your office details?', 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'These details will be used for your primary office in Property Hive.', 'propertyhive' ); ?></p>
						<div class="ph-onboarding__form-grid ph-onboarding__form-grid--office">
							<label class="ph-onboarding__field" data-validation-field="office_name">
								<span><?php esc_html_e( 'Office name', 'propertyhive' ); ?></span>
								<input type="text" name="office_name" value="<?php echo esc_attr( $office['name'] ); ?>" data-office-field="office_name" maxlength="120" aria-describedby="ph-onboarding-error-office_name">
								<?php $this->output_field_error( 'office_name' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_address_1">
								<span><?php esc_html_e( 'Address Line 1', 'propertyhive' ); ?></span>
								<input type="text" name="office_address_1" value="<?php echo esc_attr( $office['address_1'] ); ?>" data-office-field="office_address_1" maxlength="120" aria-describedby="ph-onboarding-error-office_address_1">
								<?php $this->output_field_error( 'office_address_1' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_address_2">
								<span><?php esc_html_e( 'Address Line 2', 'propertyhive' ); ?></span>
								<input type="text" name="office_address_2" value="<?php echo esc_attr( $office['address_2'] ); ?>" data-office-field="office_address_2" maxlength="120" aria-describedby="ph-onboarding-error-office_address_2">
								<?php $this->output_field_error( 'office_address_2' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_address_3">
								<span><?php esc_html_e( 'Address Line 3', 'propertyhive' ); ?></span>
								<input type="text" name="office_address_3" value="<?php echo esc_attr( $office['address_3'] ); ?>" data-office-field="office_address_3" maxlength="120" aria-describedby="ph-onboarding-error-office_address_3">
								<?php $this->output_field_error( 'office_address_3' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_address_4">
								<span><?php esc_html_e( 'Address Line 4', 'propertyhive' ); ?></span>
								<input type="text" name="office_address_4" value="<?php echo esc_attr( $office['address_4'] ); ?>" data-office-field="office_address_4" maxlength="120" aria-describedby="ph-onboarding-error-office_address_4">
								<?php $this->output_field_error( 'office_address_4' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_postcode">
								<span><?php esc_html_e( 'Postcode', 'propertyhive' ); ?></span>
								<input type="text" name="office_postcode" value="<?php echo esc_attr( $office['postcode'] ); ?>" data-office-field="office_postcode" maxlength="20" aria-describedby="ph-onboarding-error-office_postcode">
								<?php $this->output_field_error( 'office_postcode' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_telephone_number">
								<span><?php esc_html_e( 'Phone number', 'propertyhive' ); ?></span>
								<input type="tel" name="office_telephone_number" value="<?php echo esc_attr( $office['telephone_number'] ); ?>" data-office-field="office_telephone_number" maxlength="30" inputmode="tel" aria-describedby="ph-onboarding-error-office_telephone_number">
								<?php $this->output_field_error( 'office_telephone_number' ); ?>
							</label>
							<label class="ph-onboarding__field" data-validation-field="office_email_address">
								<span><?php esc_html_e( 'Email address', 'propertyhive' ); ?></span>
								<input type="email" name="office_email_address" value="<?php echo esc_attr( $office['email_address'] ); ?>" data-office-field="office_email_address" maxlength="100" aria-describedby="ph-onboarding-error-office_email_address">
								<?php $this->output_field_error( 'office_email_address' ); ?>
							</label>
						</div>
						<?php $this->output_settings_note( __( 'Offices', 'propertyhive' ) ); ?>
					</section>

					<section class="ph-onboarding__panel" data-step="usage">
						<h2><?php esc_html_e( 'How will you use Property Hive?', 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'Pick every option that applies. This helps keep the admin area focused.', 'propertyhive' ); ?></p>
						<div class="ph-onboarding__cards ph-onboarding__cards--usage" data-input-group="usage" data-validation-field="usage">
							<?php $this->output_choice_card( 'usage', 'import_properties', __( 'Import properties', 'propertyhive' ), __( 'Bring listings in from another CRM or feed.', 'propertyhive' ), in_array( 'import_properties', $usage, true ) ); ?>
							<?php $this->output_choice_card( 'usage', 'crm', __( 'Use it as a CRM', 'propertyhive' ), __( 'Manage contacts, appraisals, viewings, offers and tenancies.', 'propertyhive' ), in_array( 'crm', $usage, true ) ); ?>
							<?php $this->output_choice_card( 'usage', 'portal_uploads', __( 'Upload to portals', 'propertyhive' ), __( 'Send property data to third-party portals.', 'propertyhive' ), in_array( 'portal_uploads', $usage, true ) ); ?>
							<?php $this->output_choice_card( 'usage', 'not_sure', __( 'Not sure yet', 'propertyhive' ), __( 'Keep exploring before deciding.', 'propertyhive' ), in_array( 'not_sure', $usage, true ) ); ?>
						</div>
						<?php $this->output_field_error( 'usage' ); ?>
						<div class="ph-onboarding__links" data-usage-links>
							<a href="<?php echo esc_url( $this->get_import_url() ); ?>" target="_blank" rel="noopener noreferrer" data-usage-link="import_properties"><?php esc_html_e( 'Read about importing properties', 'propertyhive' ); ?></a>
							<a href="<?php echo esc_url( $this->get_export_url() ); ?>" target="_blank" rel="noopener noreferrer" data-usage-link="portal_uploads"><?php esc_html_e( 'Read about portal exports', 'propertyhive' ); ?></a>
						</div>
						<?php $this->output_settings_note( __( 'General > Modules', 'propertyhive' ) ); ?>
					</section>

					<section class="ph-onboarding__panel" data-step="demo-data">
						<h2><?php esc_html_e( 'Would you like to import demo data?', 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'Demo data gives you sample properties and related records so you can see how Property Hive works before adding real listings.', 'propertyhive' ); ?></p>

						<div class="ph-onboarding__cards ph-onboarding__cards--radio" data-input-group="demo-data-choice" data-validation-field="demo_data_choice">
							<?php $this->output_radio_card( 'demo_data_choice', 'no', __( 'No', 'propertyhive' ), __( 'I will add real properties and contacts myself.', 'propertyhive' ), 'no' === $demo_choice, false ); ?>
							<?php $this->output_radio_card( 'demo_data_choice', 'yes', __( 'Yes', 'propertyhive' ), __( 'Add example records so I can explore the product first.', 'propertyhive' ), 'yes' === $demo_choice, ! $demo_active ); ?>
						</div>
						<?php $this->output_field_error( 'demo_data_choice' ); ?>

						<div class="ph-onboarding__demo-box" data-demo-progress-box>
							<?php if ( $demo_active ) : ?>
								<div class="ph-onboarding__demo-status" data-demo-status><?php esc_html_e( 'Demo data will import when you continue.', 'propertyhive' ); ?></div>
								<div class="ph-onboarding__demo-progress" aria-hidden="true"><span data-demo-progress-bar></span></div>
								<div class="ph-onboarding__demo-results" data-demo-results></div>
							<?php else : ?>
								<p><?php esc_html_e( 'The Demo Data feature is not active on this site yet.', 'propertyhive' ); ?></p>
								<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ph-settings&tab=features&profilter=free' ) ); ?>"><?php esc_html_e( 'Activate Demo Data Feature', 'propertyhive' ); ?></a>
							<?php endif; ?>
						</div>
						<?php $this->output_settings_note( __( 'Demo Data', 'propertyhive' ) ); ?>
					</section>

					<section class="ph-onboarding__panel" data-step="complete">
						<h2><?php esc_html_e( "You're all set", 'propertyhive' ); ?></h2>
						<p><?php esc_html_e( 'Property Hive has been configured with your first setup choices.', 'propertyhive' ); ?></p>

						<?php if ( ! empty( $video_url ) ) : ?>
							<div class="ph-onboarding__video">
								<iframe src="<?php echo esc_url( $video_url ); ?>" title="<?php esc_attr_e( 'Property Hive getting started video', 'propertyhive' ); ?>" allowfullscreen></iframe>
							</div>
						<?php else : ?>
							<div class="ph-onboarding__video ph-onboarding__video--placeholder">
								<span><?php esc_html_e( 'Welcome video coming soon', 'propertyhive' ); ?></span>
							</div>
						<?php endif; ?>

						<div class="ph-onboarding__quick-links">
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=property' ) ); ?>"><?php esc_html_e( 'Add a property', 'propertyhive' ); ?></a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ph-settings' ) ); ?>"><?php esc_html_e( 'Open settings', 'propertyhive' ); ?></a>
							<a href="<?php echo esc_url( $this->get_import_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Import guidance', 'propertyhive' ); ?></a>
							<a href="<?php echo esc_url( $this->get_export_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Export guidance', 'propertyhive' ); ?></a>
							<a href="https://docs.wp-property-hive.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'propertyhive' ); ?></a>
						</div>
					</section>

					<div class="ph-onboarding__message" data-message role="alert" aria-live="polite"></div>

					<div class="ph-onboarding__actions">
						<button type="button" class="button ph-onboarding__back" data-back><?php esc_html_e( 'Back', 'propertyhive' ); ?></button>
						<button type="button" class="button button-primary ph-onboarding__next" data-next><?php esc_html_e( 'Continue', 'propertyhive' ); ?></button>
					</div>

					<div class="ph-onboarding__tracking">
						<label class="ph-onboarding__tracking-label">
							<input type="checkbox" name="usage_tracking" value="yes" data-usage-tracking <?php checked( $usage_tracking ); ?>>
							<span class="ph-onboarding__tracking-copy">
								<strong><?php esc_html_e( 'Help improve Property Hive', 'propertyhive' ); ?></strong>
								<small><?php esc_html_e( 'Share non-sensitive setup and feature usage data so we can improve the product. No personal, contact or property details are collected.', 'propertyhive' ); ?></small>
							</span>
						</label>
					</div>
				</main>
			</div>
		</div>
		<?php
	}

	/**
	 * Output a validation error container.
	 *
	 * @param string $field Field key.
	 */
	private function output_field_error( $field ) {
		?>
		<span id="ph-onboarding-error-<?php echo esc_attr( $field ); ?>" class="ph-onboarding__field-error" data-field-error="<?php echo esc_attr( $field ); ?>" aria-live="polite"></span>
		<?php
	}

	/**
	 * Output a checkbox card.
	 *
	 * @param string $name    Input name.
	 * @param string $value   Input value.
	 * @param string $title   Card title.
	 * @param string $summary Card summary.
	 * @param bool   $checked Default checked state.
	 */
	private function output_choice_card( $name, $value, $title, $summary, $checked ) {
		?>
		<label class="ph-onboarding__choice">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked ); ?>>
			<span class="ph-onboarding__choice-check" aria-hidden="true"></span>
			<span class="ph-onboarding__choice-copy">
				<strong><?php echo esc_html( $title ); ?></strong>
				<small><?php echo esc_html( $summary ); ?></small>
			</span>
		</label>
		<?php
	}

	/**
	 * Output a radio card.
	 *
	 * @param string $name     Input name.
	 * @param string $value    Input value.
	 * @param string $title    Card title.
	 * @param string $summary  Card summary.
	 * @param bool   $checked  Default checked state.
	 * @param bool   $disabled Disabled state.
	 */
	private function output_radio_card( $name, $value, $title, $summary, $checked, $disabled ) {
		?>
		<label class="ph-onboarding__choice <?php echo $disabled ? 'is-disabled' : ''; ?>">
			<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php checked( $checked ); ?> <?php disabled( $disabled ); ?>>
			<span class="ph-onboarding__choice-check" aria-hidden="true"></span>
			<span class="ph-onboarding__choice-copy">
				<strong><?php echo esc_html( $title ); ?></strong>
				<small><?php echo esc_html( $summary ); ?></small>
			</span>
		</label>
		<?php
	}

	/**
	 * Output a settings location note.
	 *
	 * @param string $location Settings location.
	 */
	private function output_settings_note( $location ) {
		?>
		<p class="ph-onboarding__settings-note">
			<?php
			printf(
				/* translators: %s: settings location, for example General > International. */
				esc_html__( "These can later be edited under 'Property Hive > Settings > %s'.", 'propertyhive' ),
				esc_html( $location )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Localize onboarding script data.
	 *
	 * @param bool $demo_active Whether demo data add-on is active.
	 */
	private function localize_script( $demo_active ) {
		wp_localize_script(
			'propertyhive_admin_onboarding',
			'propertyhive_onboarding',
			array(
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'nonce'                  => wp_create_nonce( self::NONCE_ACTION ),
				'settings_url'           => admin_url( 'admin.php?page=ph-settings' ),
				'features_url'           => admin_url( 'admin.php?page=ph-settings&tab=features&profilter=free' ),
				'demo_data_active'       => $demo_active ? 'yes' : 'no',
				'import_url'             => $this->get_import_url(),
				'export_url'             => $this->get_export_url(),
				'demo_base_sections'     => array( 'applicant', 'property' ),
				'demo_crm_sub_sections'  => apply_filters( 'propertyhive_onboarding_demo_crm_sub_sections', array( 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'enquiry' ) ),
				'complete_redirect_url'  => admin_url( 'admin.php?page=ph-settings' ),
				'i18n'                   => array(
					'continue'          => __( 'Continue', 'propertyhive' ),
					'finish'            => __( 'Finish setup', 'propertyhive' ),
					'saving'            => __( 'Saving...', 'propertyhive' ),
					'importing'         => __( 'Importing demo data...', 'propertyhive' ),
					'importComplete'    => __( 'Demo data imported.', 'propertyhive' ),
					'importFailed'      => __( 'Demo data could not be imported. You can continue setup and try again later.', 'propertyhive' ),
					'demoDataInactive'  => __( 'The Demo Data feature is not active on this site yet.', 'propertyhive' ),
					'chooseDepartment'  => __( 'Please choose at least one property sector.', 'propertyhive' ),
					'chooseCountry'     => __( 'Please choose a valid country.', 'propertyhive' ),
					'chooseUsage'       => __( 'Please choose how you will use Property Hive.', 'propertyhive' ),
					'chooseDemoData'    => __( 'Please choose whether to import demo data.', 'propertyhive' ),
					'officeNameTooLong' => __( 'Office name must be 120 characters or fewer.', 'propertyhive' ),
					'officeAddressTooLong' => __( 'Address Line 1 must be 120 characters or fewer.', 'propertyhive' ),
					'officeAddress2TooLong' => __( 'Address Line 2 must be 120 characters or fewer.', 'propertyhive' ),
					'officeAddress3TooLong' => __( 'Address Line 3 must be 120 characters or fewer.', 'propertyhive' ),
					'officeAddress4TooLong' => __( 'Address Line 4 must be 120 characters or fewer.', 'propertyhive' ),
					'officePostcodeTooLong' => __( 'Postcode must be 20 characters or fewer.', 'propertyhive' ),
					'officePhoneTooLong' => __( 'Phone number must be 30 characters or fewer.', 'propertyhive' ),
					'officePhoneInvalid' => __( 'Please enter a valid phone number.', 'propertyhive' ),
					'officeEmailTooLong' => __( 'Email address must be 100 characters or fewer.', 'propertyhive' ),
					'officeEmailInvalid' => __( 'Please enter a valid email address.', 'propertyhive' ),
					'skipConfirm'       => __( 'Skip setup? You can still configure Property Hive from settings later.', 'propertyhive' ),
				),
			)
		);
	}

	/**
	 * Get available countries.
	 *
	 * @return array
	 */
	private function get_countries() {
		$countries = new PH_Countries();
		return is_array( $countries->countries ) ? $countries->countries : array();
	}

	/**
	 * Infer the default country for onboarding.
	 *
	 * @return string
	 */
	private function get_default_country() {
		$countries = $this->get_countries();
		$existing  = get_option( 'propertyhive_default_country', '' );
		if ( isset( $countries[ $existing ] ) ) {
			return $existing;
		}

		$locale = get_locale();
		if ( is_string( $locale ) && false !== strpos( $locale, '_' ) ) {
			$locale_country = strtoupper( substr( strrchr( $locale, '_' ), 1 ) );
			if ( isset( $countries[ $locale_country ] ) ) {
				return $locale_country;
			}
		}

		$timezone = wp_timezone_string();
		$mapping  = array(
			'Europe/London'     => 'GB',
			'America/New_York'  => 'US',
			'America/Chicago'   => 'US',
			'America/Denver'    => 'US',
			'America/Los_Angeles' => 'US',
			'Europe/Dublin'     => 'IE',
			'Europe/Paris'      => 'FR',
			'Europe/Madrid'     => 'ES',
			'Europe/Berlin'     => 'DE',
			'Australia/Sydney'  => 'AU',
			'Pacific/Auckland'  => 'NZ',
		);

		if ( isset( $mapping[ $timezone ], $countries[ $mapping[ $timezone ] ] ) ) {
			return $mapping[ $timezone ];
		}

		return isset( $countries['GB'] ) ? 'GB' : key( $countries );
	}

	/**
	 * Sanitize office details from the onboarding form.
	 *
	 * @return array
	 */
	private function sanitize_office_payload() {
		return array(
			'name'             => isset( $_POST['office_name'] ) ? sanitize_text_field( wp_unslash( $_POST['office_name'] ) ) : '',
			'address_1'        => isset( $_POST['office_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['office_address_1'] ) ) : '',
			'address_2'        => isset( $_POST['office_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['office_address_2'] ) ) : '',
			'address_3'        => isset( $_POST['office_address_3'] ) ? sanitize_text_field( wp_unslash( $_POST['office_address_3'] ) ) : '',
			'address_4'        => isset( $_POST['office_address_4'] ) ? sanitize_text_field( wp_unslash( $_POST['office_address_4'] ) ) : '',
			'postcode'         => isset( $_POST['office_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['office_postcode'] ) ) : '',
			'telephone_number' => isset( $_POST['office_telephone_number'] ) ? sanitize_text_field( wp_unslash( $_POST['office_telephone_number'] ) ) : '',
			'email_address'    => isset( $_POST['office_email_address'] ) ? sanitize_text_field( wp_unslash( $_POST['office_email_address'] ) ) : '',
		);
	}

	/**
	 * Validate office details.
	 *
	 * @param array $office Office payload.
	 * @return array
	 */
	private function validate_office_payload( $office ) {
		$errors = array();

		$field_labels = array(
			'name'             => __( 'Office name', 'propertyhive' ),
			'address_1'        => __( 'Address Line 1', 'propertyhive' ),
			'address_2'        => __( 'Address Line 2', 'propertyhive' ),
			'address_3'        => __( 'Address Line 3', 'propertyhive' ),
			'address_4'        => __( 'Address Line 4', 'propertyhive' ),
			'postcode'         => __( 'Postcode', 'propertyhive' ),
			'telephone_number' => __( 'Phone number', 'propertyhive' ),
			'email_address'    => __( 'Email address', 'propertyhive' ),
		);

		$field_keys = array(
			'name'             => 'office_name',
			'address_1'        => 'office_address_1',
			'address_2'        => 'office_address_2',
			'address_3'        => 'office_address_3',
			'address_4'        => 'office_address_4',
			'postcode'         => 'office_postcode',
			'telephone_number' => 'office_telephone_number',
			'email_address'    => 'office_email_address',
		);

		$max_lengths = array(
			'name'             => 120,
			'address_1'        => 120,
			'address_2'        => 120,
			'address_3'        => 120,
			'address_4'        => 120,
			'postcode'         => 20,
			'telephone_number' => 30,
			'email_address'    => 100,
		);

		foreach ( $max_lengths as $field => $max_length ) {
			if ( isset( $office[ $field ] ) && strlen( $office[ $field ] ) > $max_length ) {
				$errors[ $field_keys[ $field ] ] = sprintf(
					/* translators: 1: field label, 2: maximum character count. */
					__( '%1$s must be %2$d characters or fewer.', 'propertyhive' ),
					$field_labels[ $field ],
					$max_length
				);
			}
		}

		if ( ! empty( $office['telephone_number'] ) ) {
			$digits = preg_replace( '/\D+/', '', $office['telephone_number'] );
			if ( strlen( $digits ) < 7 || ! preg_match( '/^[0-9+\-\s().]+$/', $office['telephone_number'] ) ) {
				$errors['office_telephone_number'] = __( 'Please enter a valid phone number.', 'propertyhive' );
			}
		}

		if ( ! empty( $office['email_address'] ) && ! is_email( $office['email_address'] ) ) {
			$errors['office_email_address'] = __( 'Please enter a valid email address.', 'propertyhive' );
		}

		return $errors;
	}

	/**
	 * Get defaults for the office address step.
	 *
	 * @param array $state Onboarding state.
	 * @return array
	 */
	private function get_office_defaults( $state ) {
		$office = array(
			'name'             => '',
			'address_1'        => '',
			'address_2'        => '',
			'address_3'        => '',
			'address_4'        => '',
			'postcode'         => '',
			'telephone_number' => '',
			'email_address'    => '',
		);

		$office_id = $this->get_primary_office_id();
		if ( $office_id ) {
			$office = array(
				'name'             => get_the_title( $office_id ),
				'address_1'        => get_post_meta( $office_id, '_office_address_1', true ),
				'address_2'        => get_post_meta( $office_id, '_office_address_2', true ),
				'address_3'        => get_post_meta( $office_id, '_office_address_3', true ),
				'address_4'        => get_post_meta( $office_id, '_office_address_4', true ),
				'postcode'         => get_post_meta( $office_id, '_office_address_postcode', true ),
				'telephone_number' => $this->get_primary_office_contact_value( $office_id, 'telephone_number' ),
				'email_address'    => $this->get_primary_office_contact_value( $office_id, 'email_address' ),
			);
		}

		if ( ! empty( $state['office'] ) && is_array( $state['office'] ) ) {
			foreach ( array_keys( $office ) as $key ) {
				if ( isset( $state['office'][ $key ] ) && '' !== $state['office'][ $key ] ) {
					$office[ $key ] = $state['office'][ $key ];
				}
			}
		}

		return $office;
	}

	/**
	 * Get the active departments that should receive shared office contact details.
	 *
	 * @return array
	 */
	private function get_office_contact_departments() {
		$state       = self::get_state();
		$allowed     = array( 'residential-sales', 'residential-lettings', 'commercial' );
		$departments = ! empty( $state['departments'] ) && is_array( $state['departments'] ) ? array_values( array_intersect( $allowed, $state['departments'] ) ) : array();

		if ( empty( $departments ) ) {
			foreach ( $allowed as $department ) {
				if ( 'yes' === get_option( 'propertyhive_active_departments_' . str_replace( 'residential-', '', $department ), 'no' ) ) {
					$departments[] = $department;
				}
			}
		}

		return ! empty( $departments ) ? $departments : $allowed;
	}

	/**
	 * Get the first saved office contact value across active departments.
	 *
	 * @param int    $office_id Office post id.
	 * @param string $field     Contact field name.
	 * @return string
	 */
	private function get_primary_office_contact_value( $office_id, $field ) {
		$meta_prefix = 'email_address' === $field ? '_office_email_address_' : '_office_telephone_number_';

		foreach ( $this->get_office_contact_departments() as $department ) {
			$value = get_post_meta( $office_id, $meta_prefix . str_replace( 'residential-', '', $department ), true );
			if ( '' !== $value ) {
				return $value;
			}
		}

		return '';
	}

	/**
	 * Get the primary office id, falling back to the first office.
	 *
	 * @return int
	 */
	private function get_primary_office_id() {
		$office_ids = get_posts(
			array(
				'post_type'      => 'office',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => 'primary',
				'meta_value'     => '1',
			)
		);

		if ( ! empty( $office_ids ) ) {
			return absint( $office_ids[0] );
		}

		$office_ids = get_posts(
			array(
				'post_type'      => 'office',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! empty( $office_ids ) ? absint( $office_ids[0] ) : 0;
	}

	/**
	 * Get current step.
	 *
	 * @param array $state Onboarding state.
	 * @return string
	 */
	private function get_current_step( $state ) {
		if ( isset( $state['status'] ) && 'completed' === $state['status'] ) {
			return 'complete';
		}

		if ( ! empty( $state['last_step'] ) && in_array( $state['last_step'], $this->steps, true ) && 'completed' !== $state['status'] ) {
			return $state['last_step'];
		}

		return 'intro';
	}

	/**
	 * Get import guidance URL.
	 *
	 * @return string
	 */
	private function get_import_url() {
		return apply_filters( 'propertyhive_onboarding_import_url', 'https://docs.wp-property-hive.com/article/56-importing-properties' );
	}

	/**
	 * Get export guidance URL.
	 *
	 * @return string
	 */
	private function get_export_url() {
		return apply_filters( 'propertyhive_onboarding_export_url', 'https://docs.wp-property-hive.com/article/404-managing-portal-feeds' );
	}
}

endif;

return new PH_Admin_Onboarding();
