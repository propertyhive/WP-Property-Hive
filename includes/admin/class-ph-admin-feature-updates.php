<?php

if ( ! defined('ABSPATH') ) exit;

/**
 * Class PH_Admin_Feature_Updates
 */
class PH_Admin_Feature_Updates {

	public function __construct() 
	{
		add_action('plugins_loaded', function () 
		{
			if ( is_admin() ) // maybe exclude AJAX calls?
			{
				add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));
			}

            add_filter('pre_http_request', array($this, 'block_add_on_update_info_requests'), 10, 3 );
		});
	}

	public function check_for_updates( $transient )
	{
		if ( ! is_object($transient) ) $transient = (object) ['response' => [], 'checked' => []];

		// Respect manual "Check again"
        $force = isset($_GET['force-check']) && current_user_can('update_plugins');

        // Debounce
        $next = (int) get_site_transient('ph_updates_next');
        if ( !$force && $next && $next > time() )
        {
            return $transient;
        }

        // Gather installed add-ons
        if ( !function_exists('get_plugins') ) 
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins = get_plugins();
        $addons  = [];

        foreach ( $plugins as $file => $data ) 
        {
            // Heuristics you control—tighten to taste:
            $is_ph = (
                (!empty($data['Author']) && stripos($data['Author'], 'PropertyHive') !== false) ||
                (!empty($data['AuthorURI']) && stripos($data['AuthorURI'], 'property-hive') !== false) ||
                (!empty($data['PluginURI']) && stripos($data['PluginURI'], 'property-hive') !== false)
            );
            if ( ! $is_ph ) continue;

            $slug    = dirname($file);
            $version = $data['Version'];
            $license = get_option("ph_license_{$slug}");
            $addons[$file] = [
                'slug'    => $slug,
                'version' => $version,
                'license' => (string) ($license ?? ''),
            ];
            $transient->checked[$file] = $version;
        }

        if ( empty($addons) )
        {
            self::schedule_next(12 * HOUR_IN_SECONDS);
            return $transient;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent'   => 'PropertyHiveUpdater/' . self::UA_VERSION . '; ' . home_url(),
        ];

        // Conditional GET (saves bandwidth when nothing changed)
        if ( ($etag = get_site_transient(self::ETAG_TRANSIENT)) ) 
        {
            $headers['If-None-Match'] = $etag;
        }
        if ( ($lastmod = get_site_transient(self::LM_TRANSIENT)) )
        {
            $headers['If-Modified-Since'] = $lastmod;
        }

        $payload = [
            'site'        => home_url(),
            'wp_version'  => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'addons'      => $addons, // keyed by plugin basename
        ];

        $resp = wp_remote_post(self::ENDPOINT, [
            'timeout' => 10,
            'headers' => $headers,
            'body'    => wp_json_encode($payload),
        ]);

        if ( is_wp_error($resp) ) {
            self::schedule_next(self::backoff_seconds());
            return $transient;
        }

        $code = (int) wp_remote_retrieve_response_code($resp);

        if ( $code === 304 ) { // Not Modified
            self::schedule_next(12 * HOUR_IN_SECONDS);
            return $transient;
        }

        if ( $code < 200 || $code >= 300 ) {
            self::schedule_next(self::backoff_seconds());
            return $transient;
        }

        // Cache ETag / Last-Modified for next time
        $new_etag = wp_remote_retrieve_header($resp, 'etag');
        if ( $new_etag ) set_site_transient(self::ETAG_TRANSIENT, $new_etag, DAY_IN_SECONDS);
        $new_lm   = wp_remote_retrieve_header($resp, 'last-modified');
        if ( $new_lm )   set_site_transient(self::LM_TRANSIENT, $new_lm, DAY_IN_SECONDS);

        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if ( ! is_array($body) ) {
            self::schedule_next(self::backoff_seconds());
            return $transient;
        }

        // Expect: $body['updates'] keyed by plugin basename
        if ( ! empty($body['updates']) && is_array($body['updates']) ) {
            foreach ($body['updates'] as $basename => $update) {
                // Ensure the shape WP expects
                $obj = (object) array_merge([
                    'slug'        => dirname($basename),
                    'plugin'      => $basename,
                    'new_version' => '',
                    'package'     => '',
                ], $update);
                // Only set if there's actually a newer version
                if ( ! empty($obj->new_version) && version_compare($obj->new_version, $transient->checked[$basename] ?? '0', '>') ) {
                    $transient->response[$basename] = $obj;
                } else {
                    unset($transient->response[$basename]);
                }
            }
        }

        // next_check from server or default 12h (+ jitter)
        $next = isset($body['next_check']) ? (int) $body['next_check'] : 12 * HOUR_IN_SECONDS;
        self::schedule_next($next);

        return $transient;
	}

    public function block_add_on_update_info_requests( $pre, $r, $url )
    {
        // If this were a real download, WP would set 'stream' => true; let those pass.
        if ( !empty( $r['stream'] ) ) 
        {
            return $pre;
        }

        $parts = wp_parse_url($url);
        if ( 
            isset($parts['host']) && isset($parts['path']) && 
            stripos($parts['host'], 'wp-property-hive.com') !== false &&
            stripos($parts['path'], '/add-on-store/') !== false &&
            substr($parts['path'], -15) === 'update-info.php'
        ) 
        {
            // This is an old call from the individual add ons. Stop it now as we'll handle this centrally now as of version 2.1.9
            $slug = str_replace("/add-on-store/", "", $parts['path']);
            $slug = str_replace("/update-info.php", "", $slug);
            $slug = explode("-", $slug);

            if ( count($slug) > 1 && $slug[0] == 'propertyhive' )
            {
                // Looking like an addon update request

                array_pop($slug); // remove weird number/code off the end of the URL

                // At this built $slug' should be, for example 'propertyhive-blm-export'
                // Need to get installed plugin of the same slug and return false data if we find one (same slug, same version)

                // Rebuild slug like "propertyhive-blm-export" from the path
                $slug_str = implode("-", $slug);

                // Find installed plugin with matching folder slug
                if ( ! function_exists('get_plugins') ) 
                {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $plugins = get_plugins();

                $installed_basename = '';
                $installed_version  = '';

                foreach ( $plugins as $file => $data ) 
                {
                    // Match folder name to our slug: e.g.
                    // /wp-content/plugins/propertyhive-blm-export/propertyhive-blm-export.php
                    $folder = dirname( $file );

                    if ( stripos($folder, '/' . $slug_str . '.php') )
                    {
                        $installed_basename = $file;
                        $installed_version  = isset($data['Version']) ? (string)$data['Version'] : '';
                        break;
                    }
                }

                // If we didn't find a matching installed add-on, don't interfere
                if ( ! $installed_basename ) {
                    return $pre;
                }

                // Return a legacy-shaped "no update" response
                $payload = [
                    'slug'        => $slug_str,
                    'new_version' => $installed_version ?: false, // mirrors "no update" semantics
                    'package'     => '',
                    'requires'    => '',
                    'tested'      => '',
                    // Add any other keys your legacy scripts expect, e.g. 'last_updated', 'sections', etc.
                ];

                return [
                    'headers'  => [ 'Content-Type' => 'application/json' ],
                    'body'     => wp_json_encode( $payload ),
                    'response' => [ 'code' => 200, 'message' => 'OK' ],
                    'cookies'  => [],
                    'filename' => null,
                ];
            }
            else
            {
                return $pre;
            }
        }

        return $pre;
    }
}

new PH_Admin_Feature_Updates();