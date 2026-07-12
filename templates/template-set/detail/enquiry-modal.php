<?php
/**
 * Shared Template Set enquiry lightbox.
 *
 * Override this template by copying it to yourtheme/propertyhive/template-set/detail/enquiry-modal.php
 *
 * Available variables: $post_id.
 *
 * @author  PropertyHive
 * @package PropertyHive/Templates/TemplateSet
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="makeEnquiry<?php echo (int) $post_id; ?>" class="ph-template-enquiry-modal" style="display:none;">
	<h2><?php esc_html_e( 'Make Enquiry', 'propertyhive' ); ?></h2>
	<p><?php esc_html_e( 'Please complete the form below and a member of staff will be in touch shortly.', 'propertyhive' ); ?></p>
	<?php propertyhive_enquiry_form(); ?>
</div>
