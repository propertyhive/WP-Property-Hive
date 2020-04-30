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

		if ( is_network_admin() || ! is_multisite() ) 
		{
			$license = PH()->license->get_current_license();

			$valid_license = false;

			if ( is_array($license) && empty($license) && get_option( 'propertyhive_license_key', '' ) != '' )
			{
				
			}
			elseif ( isset($license['active']) && $license['active'] != '1' )
			{

			}
			else
			{
				if ( isset($license['expires_at']) && $license['expires_at'] != '' )
				{
					if ( strtotime($license['expires_at']) <= time() )
					{
						// Expired

					}
					elseif ( 
						strtotime($license['expires_at']) > time() &&
						strtotime($license['expires_at']) < (time() + 30 * 24 * 60 * 60)
					)
					{
						// Expires in less than 30 days

					}
					elseif (strtotime($license['expires_at']) > time())
					{
						// Valid
						$valid_license = true;
					}
				}
			}

			if ( !$valid_license )
			{
				$add_ons = @file_get_contents('http://wp-property-hive.com/add-ons-json.php');

				if ($add_ons !== FALSE && $add_ons !== '')
		        {
		            $add_ons = json_decode($add_ons);
		            
		            if ($add_ons !== FALSE && !empty($add_ons))
		            {
		            	foreach ($add_ons as $add_on)
		                {
		                	$url = trim($add_on->url, '/');

		                	if ( 
		                		isset($add_on->wordpress_plugin_file) && $add_on->wordpress_plugin_file != '' && $add_on->wordpress_plugin_file != false &&
		                		isset($add_on->wordpress_version_option_name) && $add_on->wordpress_version_option_name != '' && $add_on->wordpress_version_option_name != false 
		                	)
		                	{
			                	$plugin_file = $add_on->wordpress_plugin_file;

			                	if ( is_plugin_active($plugin_file) )
			                	{
				                	$current_version = get_option($add_on->wordpress_version_option_name, '');

				                	if ( $current_version != '' )
				                	{
					                	// check if add on update available
					                	$new_version = $add_on->version;

					                	if( version_compare($new_version, $current_version) == 1 )
					                	{
						                	add_action( 'after_plugin_row_' . $plugin_file, array( $this, 'test' ), 10, 3 );
						                }
					                }
					            }
				            }
		                }
		            }
		        }
		    }
	    }
	}

	public function test(  $plugin_file, $plugin_data, $status ) 
	{
		if ( is_network_admin() ) {
			$active_class = is_plugin_active_for_network( $plugin_file ) ? ' active' : '';
		} else {
			$active_class = is_plugin_active( $plugin_file ) ? ' active' : '';
		}

		echo '<tr class="plugin-update-tr' . $active_class . '">
			<td colspan="3" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-warning notice-alt">
					<p>An update is available for this add on. It is recommended that you update to ensure you receive the latest features and bug fixes. You can access all updates to any purchased add ons for 12 months by <a href="https://wp-property-hive.com/product/12-month-license-key-subscription/" target="_blank">purchasing a license key</a>.</p>
				</div>
			</td>
		</tr>';
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
		
		$upgrade_notice    = '';

		$notices = explode("== Upgrade Notice ==", $content, 2);

		if ( count($notices) < 2 ) { return ''; }

		$notices = (array) preg_split( '~[\r\n]+~', trim( $notices[1] ) );

		for ( $i = 0; $i < count($notices); ++$i )
		{
			$version = trim(str_replace("=", "", $notices[$i]));
			if ( version_compare( $version, PH_VERSION, '>' ) ) 
			{
				$upgrade_notice .= '<br><strong>- ';
				$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $notices[$i+1] );
				$upgrade_notice .= '</strong>';
			}

			++$i;
		}

		return wp_kses_post( $upgrade_notice );
	}
}
new PH_Plugin_Updates();