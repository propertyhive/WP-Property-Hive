<?php
namespace PropertyHive\Divi5Sim\Modules\BackToSearch;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class BackToSearch extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'back-to-search';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_back_to_search';
    const OUTPUT_CLASS = 'propertyhive-divi5-back-to-search';
    const TITLE = 'Back To Search';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        
        $url = ! empty( $_SESSION['last_search'] ) ? $_SESSION['last_search'] : ( function_exists('ph_get_page_id') ? get_permalink( ph_get_page_id( 'search_results' ) ) : home_url('/') ); $label = static::get_attr_value( $attrs, 'label', 'Back to search' ); return '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new BackToSearch() ); } );
