<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyActions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class PropertyActions implements DependencyInterface {
    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    public static function register_module() {
        $module_json_folder_path = PH()->plugin_path() . '/includes/divi-5/includes/src/components/property-actions';

        ModuleRegistration::register_module(
            $module_json_folder_path,
            [
                'render_callback' => [ self::class, 'render_callback' ],
            ]
        );
    }

    public static function module_styles( $args ) {
        $elements = $args['elements'];

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $elements->style(
                        [
                            'attrName'   => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                ],
            ]
        );
    }

    public static function module_script_data( $args ) {
        $args['elements']->script_data( [ 'attrName' => 'module' ] );
    }

    public static function module_classnames( $args ) {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => $attrs['module']['decoration'] ?? [],
                ]
            )
        );
    }

    private static function get_attr_value( $attrs, $name, $default = '' ) {
        return $attrs[ $name ]['desktop']['value'] ?? $default;
    }

    private static function css_rule( $property, $value ) {
        if ( '' === $value || null === $value ) {
            return '';
        }

        return $property . ':' . esc_attr( $value ) . ';';
    }

    private static function get_button_css( $attrs, $scope_class ) {
        $button_layout           = self::get_attr_value( $attrs, 'buttonLayout', 'inline' );
        $button_fixed_width      = self::get_attr_value( $attrs, 'buttonFixedWidth', '120px' );
        $button_bg_color         = self::get_attr_value( $attrs, 'buttonBgColor', '' );
        $button_text_color       = self::get_attr_value( $attrs, 'buttonTextColor', '' );
        $button_bg_hover_color   = self::get_attr_value( $attrs, 'buttonBgHoverColor', '' );
        $button_text_hover_color = self::get_attr_value( $attrs, 'buttonTextHoverColor', '' );
        $button_padding          = self::get_attr_value( $attrs, 'buttonPadding', '8px 12px' );
        $button_margin           = self::get_attr_value( $attrs, 'buttonMargin', '0 10px 10px 0' );

        $scope = '.' . $scope_class;

        $css  = $scope . ' .property_actions ul{list-style-type:none;margin:0;padding:0;';
        $css .= ( 'equalWidth' === $button_layout ) ? 'display:flex;flex-wrap:wrap;' : '';
        $css .= '}';

        $css .= $scope . ' .property_actions ul li{';
        $css .= ( 'equalWidth' === $button_layout ) ? 'flex:1 1 0;' : 'display:inline-block;';
        $css .= ( 'fixedWidth' === $button_layout ) ? self::css_rule( 'width', $button_fixed_width ) : '';
        $css .= self::css_rule( 'margin', $button_margin );
        $css .= '}';

        $css .= $scope . ' .property_actions ul li a{display:block;';
        $css .= self::css_rule( 'background-color', $button_bg_color );
        $css .= self::css_rule( 'color', $button_text_color );
        $css .= self::css_rule( 'padding', $button_padding );
        $css .= ( 'equalWidth' === $button_layout ) ? 'text-align:center;' : '';
        $css .= '}';

        if ( '' !== $button_bg_hover_color || '' !== $button_text_hover_color ) {
            $css .= $scope . ' .property_actions ul li a:hover{';
            $css .= self::css_rule( 'background-color', $button_bg_hover_color );
            $css .= self::css_rule( 'color', $button_text_hover_color );
            $css .= '}';
        }

        return $css;
    }


    private static function debug_enabled() {
        return ! empty( $_GET['ph_divi_debug'] ) || ( defined( 'PROPERTYHIVE_DIVI5_DEBUG' ) && PROPERTYHIVE_DIVI5_DEBUG );
    }

    private static function debug_message( $message, $candidates = array() ) {
        if ( ! self::debug_enabled() ) { return ''; }
        $candidate_summary = array();
        foreach ( $candidates as $candidate ) {
            $candidate_summary[] = isset( $candidate['source'], $candidate['id'] ) ? $candidate['source'] . ':' . $candidate['id'] : wp_json_encode( $candidate );
        }
        return '<div class="propertyhive-divi5-debug" style="padding:10px;margin:10px 0;border:1px solid #cc8a00;background:#fff8e5;color:#4d3900;font:12px/1.4 monospace;">' . esc_html( 'Property Actions: ' . $message . ( empty( $candidate_summary ) ? '' : ' Candidates: ' . implode( ', ', $candidate_summary ) ) ) . '</div>';
    }

    private static function get_property_candidates() {
        global $property, $post, $wp_query;
        $candidates = array();
        if ( is_object( $property ) && ! empty( $property->id ) ) { $candidates[] = array( 'source' => 'global_property', 'id' => absint( $property->id ), 'property' => $property ); }
        $ids = array(
            'queried_object_id' => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            'queried_object' => ( ( $queried = get_queried_object() ) && is_object( $queried ) && ! empty( $queried->ID ) ) ? absint( $queried->ID ) : 0,
            'wp_query_post' => ( is_object( $wp_query ) && is_object( $wp_query->post ?? null ) && ! empty( $wp_query->post->ID ) ) ? absint( $wp_query->post->ID ) : 0,
            'global_post' => ( is_object( $post ) && ! empty( $post->ID ) ) ? absint( $post->ID ) : 0,
            'get_the_ID' => function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0,
        );
        foreach ( $ids as $source => $id ) { if ( $id ) { $candidates[] = array( 'source' => $source, 'id' => $id ); } }
        return $candidates;
    }

    private static function get_property() {
        global $property;
        if ( ! class_exists( '\\PH_Property' ) ) { return null; }
        foreach ( self::get_property_candidates() as $candidate ) {
            if ( ! empty( $candidate['property'] ) && is_object( $candidate['property'] ) && ! empty( $candidate['property']->id ) ) { return $candidate['property']; }
            if ( empty( $candidate['id'] ) ) { continue; }
            $candidate_property = new \PH_Property( absint( $candidate['id'] ) );
            if ( ! empty( $candidate_property->id ) ) { $property = $candidate_property; return $candidate_property; }
        }
        return null;
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        
        global $property;

        $property = self::get_property();

        if ( ! $property ) {
            return self::debug_message( 'No property could be resolved. Try ?ph_divi_debug=1 on the frontend and check candidate IDs.', self::get_property_candidates() );
        }

        $display = self::get_attr_value( $attrs, 'display', 'list' );

        ob_start();

        $button_layout = self::get_attr_value( $attrs, 'buttonLayout', 'inline' );
        $block_id      = $block->parsed_block['id'] ?? uniqid( 'property-actions-' );
        $scope_class   = 'propertyhive-divi5-property-actions-' . sanitize_html_class( $block_id );

        if ( 'buttons' === $display ) {
            echo '<style>' . self::get_button_css( $attrs, $scope_class ) . '</style>';
        }

        echo '<div class="et_pb_module_inner">';
        echo '<div class="propertyhive-divi5-property-actions ' . esc_attr( $scope_class ) . ' propertyhive-divi5-property-actions--' . esc_attr( $display ) . ' propertyhive-divi5-property-actions--layout-' . esc_attr( $button_layout ) . '">';

        if ( function_exists( 'propertyhive_template_single_actions' ) ) {
            propertyhive_template_single_actions();
        }

        echo '</div>';
        echo '</div>';

        $module_inner = ob_get_clean();

        $module_elements = $elements->style_components( [ 'attrName' => 'module' ] );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => 'propertyhive_divi5_property_actions',
                'name'                => $block->block_type->name,
                'classnamesFunction'  => [ self::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ self::class, 'module_styles' ],
                'scriptDataComponent' => [ self::class, 'module_script_data' ],
                'children'            => $module_elements . $module_inner,
            ]
        );
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyActions() );
    }
);
