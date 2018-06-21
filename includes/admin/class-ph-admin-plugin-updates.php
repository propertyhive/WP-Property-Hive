<?php
/**
 * Manages Property Hive plugin updating on the Plugins screen, showing warning and messages where applicable such as key updates which might break things
 *
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class PH_Plugin_Updates
 */
class PH_Plugin_Updates {

	/**
	 * The upgrade notice shown inline.
	 *
	 * @var string
	 */
	protected $upgrade_notice = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'in_plugin_update_message-propertyhive/propertyhive.php', array( $this, 'in_plugin_update_message' ), 10, 2 );
	}

	/**
	 * Show plugin changes on the plugins screen.
	 *
	 * @param array    $args Unused parameter.
	 * @param stdClass $response Plugin update response.
	 */
	public function in_plugin_update_message( $args, $response ) {
		$this->new_version            = $response->new_version;
		$this->upgrade_notice         = $this->get_upgrade_notice( $response->new_version );

		echo apply_filters( 'propertyhive_in_plugin_update_message', $this->upgrade_notice ? '<br><span style="color:#900">' . wp_kses_post( $this->upgrade_notice ) . '</span>' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the upgrade notice from WordPress.org.
	 *
	 * @param  string $version Property Hive new version.
	 * @return string
	 */
	protected function get_upgrade_notice( $new_version ) {
		$transient_name = 'ph_upgrade_notice_' . $new_version;
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/propertyhive/trunk/README.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $new_version );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}
		return $upgrade_notice;
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content Property Hive readme file content.
	 * @param  string $new_version Property Hive new version.
	 * @return string
	 */
	private function parse_update_notice( $content, $new_version ) {
		
		$notice_regexp     = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
		$upgrade_notice    = '';

		$matches = null;
		if ( preg_match( $notice_regexp, $content, $matches ) ) {
			
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			if ( version_compare( trim( $matches[1] ), PH_VERSION, '>' ) ) {

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= '<br><strong>- ';
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
					$upgrade_notice .= '</strong>';
				}

				//$upgrade_notice .= '</p>';
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
}
new PH_Plugin_Updates();