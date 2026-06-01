<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyCouncilTaxBand;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyMeta/PropertyMetaModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyMeta\PropertyMetaModule;

class PropertyCouncilTaxBand extends PropertyMetaModule implements DependencyInterface {
    const MODULE_DIR = 'property-council-tax-band';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_council_tax_band';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-council-tax-band';
    const TEXT_ATTR = 'councilTaxBandText';
    const HAS_ICON = true;
    const DEFAULT_AFTER = '';

    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    protected static function get_property_value( $property ) {
        return $property->council_tax_band ?? '';
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyCouncilTaxBand() );
    }
);
