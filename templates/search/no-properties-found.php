<?php
/**
 * Displayed when no property are found matching the current query.
 *
 * Override this template by copying it to yourtheme/propertyhive/search/no-properties-found.php
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<p class="propertyhive-info no-results-message"><?php echo esc_html(__( 'No properties were found matching your criteria.', 'propertyhive' )); ?></p>