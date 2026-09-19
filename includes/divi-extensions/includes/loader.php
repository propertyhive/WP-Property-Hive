<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers native Divi 5 modules with the Divi module library.
 */
add_action( 'divi_module_library_modules_dependency_tree', 'ph_divi_extension_register_modules' );
add_action( 'rest_api_init', 'ph_divi_extension_register_rest_routes' );

function ph_divi_extension_register_modules( $dependency_tree )
{
    if (
        ! is_object( $dependency_tree )
        || ! method_exists( $dependency_tree, 'add_dependency' )
        || ! interface_exists( '\ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface' )
        || ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' )
    )
    {
        return;
    }

    $module_file = PH_DIVI_EXTENSION_PATH . 'includes/modules/PropertyActions/PropertyActions.php';

    if ( file_exists( $module_file ) )
    {
        require_once $module_file;
    }

    if ( class_exists( 'PH_Divi_Property_Actions_Module' ) )
    {
        $dependency_tree->add_dependency( new PH_Divi_Property_Actions_Module() );
    }
}

function ph_divi_extension_register_rest_routes()
{
    register_rest_route(
        'propertyhive/v1',
        '/divi/property-actions-preview',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'ph_divi_extension_property_actions_preview',
            'permission_callback' => 'ph_divi_extension_can_preview_property_actions',
        )
    );
}

function ph_divi_extension_can_preview_property_actions( $request )
{
    $post_id = absint( $request->get_param( 'post_id' ) );

    if ( $post_id )
    {
        return current_user_can( 'edit_post', $post_id );
    }

    return current_user_can( 'edit_posts' );
}

function ph_divi_extension_property_actions_preview( $request )
{
    $attrs = $request->get_param( 'attrs' );

    if ( ! is_array( $attrs ) )
    {
        $attrs = array();
    }

    $post_id = absint( $request->get_param( 'post_id' ) );

    global $post;

    $had_post      = isset( $GLOBALS['post'] );
    $previous_post = $post ?? null;
    $preview_post  = $post_id ? get_post( $post_id ) : null;

    if ( $preview_post instanceof WP_Post )
    {
        $post = $preview_post;
        setup_postdata( $post );
    }

    $html = ph_divi_property_actions_render_actions_html( $attrs, $post_id, null, false );

    if ( $preview_post instanceof WP_Post )
    {
        wp_reset_postdata();
    }

    if ( $had_post )
    {
        $post = $previous_post;
    }
    else
    {
        unset( $GLOBALS['post'] );
    }

    return rest_ensure_response(
        array(
            'html' => $html,
        )
    );
}

function ph_divi_property_actions_render_actions_html( $attrs = array(), $post_id = 0, $block = null, $include_styles = true )
{
    if ( ! class_exists( 'PH_Property' ) || ! function_exists( 'propertyhive_template_single_actions' ) )
    {
        return '';
    }

    $post_id = absint( $post_id ?: get_the_ID() );

    if ( ! $post_id )
    {
        return '';
    }

    $property = new PH_Property( $post_id );

    if ( empty( $property->id ) )
    {
        return '';
    }

    $had_global_property = array_key_exists( 'property', $GLOBALS );
    $previous_property   = $GLOBALS['property'] ?? null;
    $GLOBALS['property'] = $property;

    ob_start();

    try
    {
        if ( $include_styles && 'buttons' === ph_divi_property_actions_get_display( $attrs ) )
        {
            echo ph_divi_property_actions_get_button_styles( $attrs, $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        propertyhive_template_single_actions();
        $actions_html = ob_get_clean();
    }
    catch ( \Throwable $exception )
    {
        ob_end_clean();
        ph_divi_property_actions_restore_global_property( $had_global_property, $previous_property );
        throw $exception;
    }

    ph_divi_property_actions_restore_global_property( $had_global_property, $previous_property );

    return $actions_html;
}

function ph_divi_property_actions_get_display( $attrs )
{
    if ( isset( $attrs['display'] ) )
    {
        $display = $attrs['display'];
    }
    else
    {
        $display = ph_divi_property_actions_get_attr_value( $attrs, array( 'propertyActions', 'advanced', 'display' ), 'list' );
    }

    return 'buttons' === $display ? 'buttons' : 'list';
}

function ph_divi_property_actions_get_attr_value( $attrs, $path, $default = '' )
{
    $value = $attrs;

    foreach ( $path as $path_part )
    {
        if ( ! is_array( $value ) || ! array_key_exists( $path_part, $value ) )
        {
            return $default;
        }

        $value = $value[ $path_part ];
    }

    return ph_divi_property_actions_normalize_attr_value( $value, $default );
}

function ph_divi_property_actions_get_responsive_attr_property( $attrs, $path, $property, $default = '' )
{
    $value = $attrs;

    foreach ( $path as $path_part )
    {
        if ( ! is_array( $value ) || ! array_key_exists( $path_part, $value ) )
        {
            return $default;
        }

        $value = $value[ $path_part ];
    }

    if ( isset( $value['desktop']['value'] ) )
    {
        $value = $value['desktop']['value'];
    }

    if ( ! is_array( $value ) || ! array_key_exists( $property, $value ) )
    {
        return $default;
    }

    return ph_divi_property_actions_normalize_attr_value( $value[ $property ], $default );
}

function ph_divi_property_actions_normalize_attr_value( $value, $default = '' )
{
    if ( is_array( $value ) )
    {
        if ( array_key_exists( 'desktop', $value ) )
        {
            return ph_divi_property_actions_normalize_attr_value( $value['desktop'], $default );
        }

        if ( array_key_exists( 'value', $value ) )
        {
            return ph_divi_property_actions_normalize_attr_value( $value['value'], $default );
        }

        if ( array_key_exists( 'color', $value ) )
        {
            return ph_divi_property_actions_normalize_attr_value( $value['color'], $default );
        }

        return $default;
    }

    if ( is_scalar( $value ) && '' !== trim( (string) $value ) )
    {
        return $value;
    }

    return $default;
}

function ph_divi_property_actions_restore_global_property( $had_global_property, $previous_property )
{
    if ( $had_global_property )
    {
        $GLOBALS['property'] = $previous_property;
        return;
    }

    unset( $GLOBALS['property'] );
}

function ph_divi_property_actions_get_button_styles( $attrs, $block = null )
{
    $selector_class = '';

    if (
        $block
        && ! empty( $block->parsed_block['id'] )
        && class_exists( '\ET\Builder\Packages\ModuleUtils\ModuleUtils' )
    )
    {
        $selector_class = \ET\Builder\Packages\ModuleUtils\ModuleUtils::get_module_order_class_name(
            $block->parsed_block['id'],
            $block->parsed_block['storeInstance'] ?? null
        );
    }

    if ( ! $selector_class && $block && isset( $block->parsed_block['orderIndex'] ) )
    {
        $selector_class = 'et_pb_property_actions_widget_' . $block->parsed_block['orderIndex'];
    }

    $selector = $selector_class ? '.' . sanitize_html_class( $selector_class ) . ' .property_actions' : '.et_pb_property_actions_widget .property_actions';

    $button_bg_color   = ph_divi_property_actions_sanitize_css_value( ph_divi_property_actions_get_attr_value( $attrs, array( 'propertyActions', 'decoration', 'buttonBackgroundColor' ), '#000000' ), '#000000' );
    $button_text_color = ph_divi_property_actions_sanitize_css_value( ph_divi_property_actions_get_attr_value( $attrs, array( 'propertyActions', 'decoration', 'buttonTextColor' ), '#ffffff' ), '#ffffff' );
    $button_padding    = ph_divi_property_actions_sanitize_css_value( ph_divi_property_actions_get_attr_value( $attrs, array( 'propertyActions', 'decoration', 'buttonPadding' ), '10px 15px' ), '10px 15px' );
    $button_margin     = ph_divi_property_actions_sanitize_css_value( ph_divi_property_actions_get_attr_value( $attrs, array( 'propertyActions', 'decoration', 'buttonMargin' ), '0 5px 0 0' ), '0 5px 0 0' );
    $fill_width        = ph_divi_property_actions_has_module_width_setting( $attrs );

    return sprintf(
        '<style type="text/css">%1$s{height:100%%;min-height:inherit;max-height:inherit;}%1$s ul{list-style-type:none!important;margin:0!important;padding:0!important;%6$sheight:100%%;min-height:inherit;max-height:inherit;}%1$s ul li{display:inline-block;margin:0!important;padding:0!important;%7$sheight:100%%;min-height:inherit;max-height:inherit;}%1$s ul li a{display:flex;background:%2$s!important;color:%3$s!important;padding:%4$s!important;margin:%5$s!important;text-decoration:none;%8$sheight:100%%;min-height:inherit;max-height:inherit;align-items:center;justify-content:center;}</style>',
        esc_html( $selector ),
        esc_html( $button_bg_color ),
        esc_html( $button_text_color ),
        esc_html( $button_padding ),
        esc_html( $button_margin ),
        $fill_width ? 'width:100%;' : '',
        $fill_width ? 'width:100%;box-sizing:border-box;' : '',
        $fill_width ? 'width:100%;box-sizing:border-box;text-align:center;' : 'box-sizing:border-box;'
    );
}

function ph_divi_property_actions_has_module_width_setting( $attrs )
{
    foreach ( array( 'width', 'maxWidth', 'minWidth' ) as $property )
    {
        $value = ph_divi_property_actions_get_responsive_attr_property(
            $attrs,
            array( 'module', 'decoration', 'sizing' ),
            $property
        );

        if ( '' !== $value && 'auto' !== strtolower( (string) $value ) && 'none' !== strtolower( (string) $value ) )
        {
            return true;
        }
    }

    return false;
}

function ph_divi_property_actions_sanitize_css_value( $value, $default )
{
    if ( ! is_scalar( $value ) )
    {
        return $default;
    }

    $value = trim( wp_strip_all_tags( (string) $value ) );

    if ( '' === $value )
    {
        return $default;
    }

    $value = preg_replace( '/[^#a-zA-Z0-9\s.,()%+\-\/]/', '', $value );

    return '' !== $value ? $value : $default;
}
