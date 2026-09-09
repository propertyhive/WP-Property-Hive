<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( $contact_ids == '' )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $contact_ids = array();
}
if ( !is_array($contact_ids) && $contact_ids != '' && $contact_ids != 0 )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $contact_ids = array($contact_ids);
}

if ( !empty($contact_ids) )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
	foreach ( $contact_ids as $contact_id )
	{
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$contact = new PH_Contact((int)$contact_id);
?>
<div class="contact">
	<div class="name"><a href="<?php echo esc_url(get_edit_post_link((int)$contact_id)); ?>"><?php echo esc_html(get_the_title((int)$contact_id)); ?></a></div>
	<div class="contact-details">
		<?php if ( $contact->get_formatted_full_address() != '' ) { echo esc_html($contact->get_formatted_full_address()) . '<br>'; } ?>
		T: <?php echo esc_html(get_post_meta((int)$contact_id, '_telephone_number', TRUE)); ?><br>
		E: <?php echo esc_html(get_post_meta((int)$contact_id, '_email_address', TRUE)); ?>
	</div>
	<?php do_action( 'propertyhive_lightbox_contact_details', (int)$contact_id, $post->ID ); ?>
</div>
<?php
	}
}
else
{
	echo '-';
}