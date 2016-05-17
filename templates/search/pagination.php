<?php
/**
 * Pagination - Show numbered pagination for search results pages.
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wp_query;
?>
<div class="propertyhive-pagination">
	<?php
		if ( $wp_query->max_num_pages  > 1 )
		{
			echo paginate_links( apply_filters( 'propertyhive_pagination_args', array(
				'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
				'format'       => '',
				'current'      => max( 1, get_query_var( 'paged' ) ),
				'total'        => $wp_query->max_num_pages,
				'prev_text'    => '&larr;',
				'next_text'    => '&rarr;',
				'type'         => 'list',
				'end_size'     => 3,
				'mid_size'     => 3
			) ) );
		}
		else
		{
			echo '&nbsp;';
		}
	?>
</div>