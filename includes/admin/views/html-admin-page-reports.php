<?php
/**
 * Admin View: Page - Reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap propertyhive">
	<nav class="nav-tab-wrapper">
		<?php
			foreach ( $reports as $key => $report_group ) {
				echo '<a href="' . esc_url(admin_url( 'admin.php?page=ph-reports&tab=' . urlencode( $key ) )) . '" class="nav-tab ';
				if ( $current_tab == $key ) {
					echo 'nav-tab-active';
				}
				echo '">' . esc_html( $report_group[ 'title' ] ) . '</a>';
			}

			do_action( 'ph_reports_tabs' );
		?>
	</nav>
	<?php //if ( sizeof( $reports[ $current_tab ]['reports'] ) > 1 ) {
		?>
		<ul class="subsubsub">
			<li><?php

				$links = array();

				foreach ( $reports[ $current_tab ]['reports'] as $key => $report ) {

					$link = '<a href="admin.php?page=ph-reports&tab=' . urlencode( $current_tab ) . '&amp;report=' . urlencode( $key ) . '" class="';

					if ( $key == $current_report ) {
						$link .= 'current';
					}

					$link .= '">' . esc_html($report['title']) . '</a>';

					$links[] = $link;

				}

				echo wp_kses_post(implode( ' | </li><li>', $links ));

			?></li>
		</ul>
		<br class="clear" />
		<?php
	//}

	if ( isset( $reports[ $current_tab ][ 'reports' ][ $current_report ] ) ) {

		$report = $reports[ $current_tab ][ 'reports' ][ $current_report ];

		if ( ! isset( $report['hide_title'] ) || $report['hide_title'] != true ) {
			echo '<h1>' . esc_html( $report['title'] ) . '</h1>';
		} else {
			echo '<h1 class="screen-reader-text">' . esc_html( $report['title'] ) . '</h1>';
		}

		if ( $report['description'] ) {
			echo '<p>' . wp_kses_post($report['description']) . '</p>';
		}

		if ( $report['callback'] && ( is_callable( $report['callback'] ) ) ) {
			call_user_func( $report['callback'], $current_report );
		}
	}
	?>
</div>
