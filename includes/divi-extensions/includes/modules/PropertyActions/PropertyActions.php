<?php
/**
 * Native Divi 5 Property Actions module.
 *
 * @package PropertyHive
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class PH_Divi_Property_Actions_Module implements DependencyInterface {

    /**
     * Register the module metadata and server render callback.
     *
     * @return void
     */
    public function load(): void {
        ModuleRegistration::register_module(
            __DIR__,
            array(
                'render_callback' => array( self::class, 'render_callback' ),
            )
        );
    }

    /**
     * Add standard Divi module classes.
     *
     * @param array $args Classname arguments.
     * @return void
     */
    public static function module_classnames( array $args ): void {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

        $classnames_instance->add(
            ElementClassnames::classnames(
                array(
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? array(),
                        array(
                            'link' => $attrs['module']['advanced']['link'] ?? array(),
                        )
                    ),
                )
            )
        );
    }

    /**
     * Render standard Divi module styles.
     *
     * @param array $args Style arguments.
     * @return void
     */
    public static function module_styles( array $args ): void {
        $attrs    = $args['attrs'] ?? array();
        $elements = $args['elements'];
        $settings = $args['settings'] ?? array();

        Style::add(
            array(
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => array(
                    $elements->style(
                        array(
                            'attrName'   => 'module',
                            'styleProps' => array(
                                'disabledOn' => array(
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ),
                            ),
                        )
                    ),
                    CssStyle::style(
                        array(
                            'selector' => $args['orderClass'],
                            'attr'     => $attrs['css'] ?? array(),
                        )
                    ),
                ),
            )
        );
    }

    /**
     * Register standard Divi module script data.
     *
     * @param array $args Script data arguments.
     * @return void
     */
    public static function module_script_data( array $args ): void {
        $elements = $args['elements'];

        $elements->script_data(
            array(
                'attrName' => 'module',
            )
        );
    }

    /**
     * Render Property Hive's property actions inside a native Divi 5 module wrapper.
     *
     * @param array          $attrs                       Module attributes.
     * @param string         $content                     Block content.
     * @param WP_Block       $block                       WordPress block instance.
     * @param ModuleElements $elements                    Divi module elements instance.
     * @param array          $default_printed_style_attrs Default printed style attributes.
     * @return string
     */
    public static function render_callback(
        array $attrs,
        string $content,
        WP_Block $block,
        ModuleElements $elements,
        array $default_printed_style_attrs
    ): string {
        if ( ! function_exists( 'ph_divi_property_actions_render_actions_html' ) )
        {
            return '';
        }

        $actions_html = ph_divi_property_actions_render_actions_html( $attrs, get_the_ID(), $block );

        if ( '' === trim( $actions_html ) )
        {
            return '';
        }

        $block_id       = $block->parsed_block['id'] ?? '';
        $store_instance = $block->parsed_block['storeInstance'] ?? null;
        $parent         = null;

        if ( class_exists( '\ET\Builder\FrontEnd\BlockParser\BlockParserStore' ) && $block_id )
        {
            $parent = \ET\Builder\FrontEnd\BlockParser\BlockParserStore::get_parent( $block_id, $store_instance );
        }

        return Module::render(
            array(
                'orderIndex'               => $block->parsed_block['orderIndex'] ?? null,
                'storeInstance'            => $store_instance,
                'attrs'                    => $attrs,
                'elements'                 => $elements,
                'id'                       => $block_id,
                'name'                     => $block->block_type->name,
                'classnamesFunction'       => array( self::class, 'module_classnames' ),
                'moduleCategory'           => $block->block_type->category,
                'stylesComponent'          => array( self::class, 'module_styles' ),
                'scriptDataComponent'      => array( self::class, 'module_script_data' ),
                'parentAttrs'              => $parent->attrs ?? array(),
                'parentId'                 => $parent->id ?? '',
                'parentName'               => $parent->blockName ?? '',
                'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
                'children'                 => $elements->style_components(
                    array(
                        'attrName' => 'module',
                    )
                ) . $actions_html,
            )
        );
    }
}
