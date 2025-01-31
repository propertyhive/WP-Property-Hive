<?php
/**
 * Admin View: Page - Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$properties_exist = false;
$args = array(
	'post_type' => 'property',
	'post_status' => 'publish',
	'fields' => 'ids',
	'posts_per_page' => 1
);
$properties_query = new WP_Query($args);
if ( $properties_query->have_posts() ) 
{ 
	$properties_exist = true;
}

$offices_exist = false;

$maps_configured = false;

$search_results_page_selected = false;

$license_key_activated = false;

$features_activated = false;

?>

<p>The first steps...</p>

<ul>

	<li<?php if ( $properties_exist ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span> <?php echo esc_html(__( 'Add your properties', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'What\'s an estate agency site without properties? Select one of the options below and add your property stock', 'propertyhive' )); ?>:</p>

	    	<a href="<?php echo admin_url('post-new.php?post_type=property&tutorial=yes'); ?>" class="button button-primary">Add Your First Property</a>
	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=demo_data'); ?>" class="button">Create Demo Data</a>

	    	<?php
	    		if ( apply_filters( 'propertyhive_no_properties_property_import_button', true ) === true )
	            {
		            $button_to_output = false;

		            if ( class_exists('PH_Property_Import') )
					{
						// Already activated. Check can be used
						if ( apply_filters( 'propertyhive_add_on_can_be_used', true, 'propertyhive-property-import' ) === true )
			        	{
			        		$button_to_output = 'normal';
						}
					}

					if ( !$button_to_output )
					{
						$license_type = get_option( 'propertyhive_license_type', '' );
						
						switch ( $license_type )
						{
							case "": { $button_to_output = 'dummy'; break; }
							case "pro": 
							{
								if ( PH()->license->is_valid_pro_license_key() )
								{
									// It should never get this far if import add on already activated, that's why show activate page
									$button_to_output = 'activate'; 
								}
								else
								{
									$button_to_output = 'dummy'; 
								}
								break; 
							}
						}
					}

					// only show dummy button to administrators
					if ( $button_to_output == 'dummy' || $button_to_output == 'activate' )
					{
						if ( !current_user_can( 'manage_options' ) ) 
						{  
							// not an admin
							$button_to_output = false;
						}
					}

					// only show dummy button to people with it installed eyond 1st nov 2023 (when PRO was introduced)
					if ( $button_to_output == 'dummy' )
					{
						$propertyhive_install_timestamp = get_option( 'propertyhive_install_timestamp', '' );
					    if ( !empty($propertyhive_install_timestamp) )
					    {
					    	$november_first_2023 = strtotime('2023-11-01 00:00:00');
					    	if ( $propertyhive_install_timestamp < $november_first_2023 )
					    	{
					    		$button_to_output = false;
					    	}
					    }
					}

					switch ( $button_to_output )
					{
						case "normal":
						{
							echo '<a href="' . admin_url('admin.php?page=propertyhive_import_properties') . '" class="button">
				                ' . esc_html( __( 'Automatically Import Properties', 'propertyhive' ) ) . '
				            </a>';
							break;
						}
						case "dummy":
						{
							echo '<a href="' . admin_url('admin.php?page=ph-import_properties_dummy') . '" class="button">
				                ' . esc_html( __( 'Automatically Import Properties', 'propertyhive' ) ) . ' <span style="color:#FFF; font-size:10px; font-weight:500; border-radius:12px; padding:2px 8px; letter-spacing:1px; background:#00a32a;">PRO</span>
				            </a>';
							break;
						}
						case "activate":
						{
							echo '<a href="' . admin_url('admin.php?page=ph-settings&tab=features&profilter=import') . '" class="button">
				                ' . esc_html( __( 'Activate Property Imports', 'propertyhive' ) ) . '
				            </a>';
							break;
						}
					}
	            }
	    	?>

	    </div>
	</li>

	<li<?php if ( $offices_exist ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span> <?php echo esc_html(__( 'Setup office details', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'Create office instructions', 'propertyhive' )); ?></p>

	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=offices'); ?>" class="button button-primary">Setup Office Details</a>

	    </div>
	</li>

	<li<?php if ( $maps_configured ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span><?php echo esc_html(__( 'Choose map provider', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'Maps are used throughout Property Hive', 'propertyhive' )); ?></p>

	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=general&section=map'); ?>" class="button button-primary">Choose Map Provider</a>

	    </div>
	</li>

	<li<?php if ( $search_results_page_selected ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span> <?php echo esc_html(__( 'Choose your search results page', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'Instructions here', 'propertyhive' )); ?></p>

	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=general'); ?>" class="button button-primary">Choose Search Results Page</a>

	    </div>
	</li>

	<li<?php if ( $license_key_activated ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span> <?php echo esc_html(__( 'Activate license key', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'Instructions here', 'propertyhive' )); ?></p>

	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=licensekey'); ?>" class="button button-primary">Activate License Key</a>

	    </div>
	</li>

	<li<?php if ( $features_activated ) { echo ' class="completed"'; } ?>>
	    <span class="item-title"><span class="dashicons dashicons-arrow-down"></span> <?php echo esc_html(__( 'Manage activated features', 'propertyhive' )); ?></span>
	    <a href="#" class="dismiss" title="<?php echo esc_attr(__( 'Dismiss', 'propertyhive' )); ?>"><span class="dashicons dashicons-visibility"></span></a>
	    <div class="details">
	    	
	    	<p><?php echo esc_html(__( 'Instructions here', 'propertyhive' )); ?></p>

	    	<a href="<?php echo admin_url('admin.php?page=ph-settings&tab=features'); ?>" class="button button-primary">Manage Activated Features</a>

	    </div>
	</li>

</ul>

<script>

jQuery(document).ready(function($) 
{
    $('.widget-checklist li').on('click', function(e) 
    {
        if (!$(e.target).is('.dismiss') && !$(e.target).is('.button')) 
        {
        	if ( $(this).hasClass('expanded') )
        	{
        		$(this).removeClass('expanded');
        	}
        	else
        	{
	        	$('.widget-checklist li').removeClass('expanded');
	            $(this).addClass('expanded');
	        }
        }
    });

    $('.widget-checklist li .dismiss').on('click', function(e) 
    {
        e.preventDefault();
        $(this).closest('li').remove();
    });
});

</script>