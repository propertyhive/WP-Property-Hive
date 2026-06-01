<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyNotOnMarketMessage;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyNotOnMarketMessage extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-not-on-market-message';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_not_on_market_message';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-not-on-market-message';
    const TITLE = 'Not On Market Message';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        return '<div class="propertyhive-not-on-market-message">' . esc_html__( 'This property is not currently available. It may be sold or temporarily removed from the market.', 'propertyhive' ) . '</div>';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyNotOnMarketMessage() ); } );
