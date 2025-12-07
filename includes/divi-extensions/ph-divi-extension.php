<?php
/**
 * Internal Property Hive Divi Extension
 * Acts exactly like a standalone Divi Extension plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PH_DIVI_EXTENSION_PATH', plugin_dir_path( __FILE__ ) );
define( 'PH_DIVI_EXTENSION_URL', plugin_dir_url( __FILE__ ) );

// Load Divi Extension Loader (the PHP code that registers all modules)
require_once PH_DIVI_EXTENSION_PATH . 'includes/loader.php';

add_action( 'wp_enqueue_scripts', 'ph_divi_extension_enqueue' );
add_action( 'admin_enqueue_scripts', 'ph_divi_extension_enqueue' );
function ph_divi_extension_enqueue() 
{
    // Only load when Divi's builder is active (front-end or backend)
    if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) 
    {
        wp_enqueue_script(
            'ph-divi-extension-bundle',
            PH_DIVI_EXTENSION_URL . 'build/bundle.js',
            array( 'react', 'react-dom', 'wp-element' ), // <-- key change
            '1.0.0',
            true
        );
    }
}
