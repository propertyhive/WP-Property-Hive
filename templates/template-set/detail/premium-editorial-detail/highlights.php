<?php
/**
 * Private Office masthead brief.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/premium-editorial-detail/highlights.php
 *
 * Available variables: $property, $template, $highlights.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( empty( $highlights[0]['value'] ) || ( 'yes' !== PH_Template_Set_Request_Context::get_editorial_show_brief() && ! PH_Template_Set_Request_Context::is_template_editor_active() ) ) { return; }
?>
<p class="ph-template-editorial-brief"><?php echo esc_html( $highlights[0]['value'] ); ?></p>
