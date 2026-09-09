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
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			foreach ( $reports as $key => $report_group ) {
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=ph-reports&tab=' . urlencode( $key ) ) ) . '" class="nav-tab ';
				if ( $current_tab == $key ) {
					echo 'nav-tab-active';
				}
				echo '">' . esc_html( $report_group[ 'title' ] ) . '</a>';
			}

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing public Property Hive extension hook ph_reports_tabs; changing the established name would detach installed callbacks.
			do_action( 'ph_reports_tabs' );
		?>
	</nav>
	<?php //if ( sizeof( $reports[ $current_tab ]['reports'] ) > 1 ) {
		?>
		<ul class="subsubsub">
			<li><?php

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$links = array();

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				foreach ( $reports[ $current_tab ]['reports'] as $key => $report ) {

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local report navigation markup.
					$link = '<a href="' . esc_url( 'admin.php?page=ph-reports&tab=' . urlencode( $current_tab ) . '&report=' . urlencode( $key ) ) . '" class="';

					if ( $key == $current_report ) {
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local report navigation markup.
						$link .= 'current';
					}

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local report navigation markup.
					$link .= '">' . esc_html( $report['title'] ) . '</a>';

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
					$links[] = $link;

				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Locally assembled navigation links: URLs and titles are escaped above; class values and separators are fixed markup.
				echo implode( ' | </li><li>', $links );

			?></li>
		</ul>
		<br class="clear" />
		<?php
	//}

	if ( isset( $reports[ $current_tab ][ 'reports' ][ $current_report ] ) ) {

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$report = $reports[ $current_tab ][ 'reports' ][ $current_report ];

		if ( ! isset( $report['hide_title'] ) || $report['hide_title'] != true ) {
			echo '<h1>' . esc_html( $report['title'] ) . '</h1>';
		} else {
			echo '<h1 class="screen-reader-text">' . esc_html( $report['title'] ) . '</h1>';
		}

		if ( $report['description'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted HTML from PHP report registrations via propertyhive_admin_reports (core descriptions are empty). Extension callbacks own escaping of dynamic values in their descriptions.
			echo '<p>' . $report['description'] . '</p>';
		}

		if ( $report['callback'] && ( is_callable( $report['callback'] ) ) ) {
			call_user_func( $report['callback'], $current_report );
		}
	}
	?>
</div>
