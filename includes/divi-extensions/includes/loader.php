<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Loads every module inside divi-extension/includes/modules/
 */
add_action( 'et_builder_ready', function() {

    $modules_path = PH_DIVI_EXTENSION_PATH . 'includes/modules/';

    foreach ( glob( $modules_path . '*/' ) as $module_dir ) 
    {
        $module_php = $module_dir . basename( $module_dir ) . '.php';

        if ( file_exists( $module_php ) ) 
        {
            require_once $module_php;
        }
    }
});
