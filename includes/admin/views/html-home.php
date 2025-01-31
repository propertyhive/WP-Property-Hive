<?php
/**
 * Admin View: Page - Home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap propertyhive">

	<h1>New Home Page</h1>

	<div class="propertyhive-home-container">

		<div class="propertyhive-home-widgets">

			<?php
				foreach ( $widgets as $key => $widget )
				{
					echo '<div class="widget widget-' . esc_attr($key) . '">';
						echo '<div class="widget-header">';
							echo '<div class="close"><a href="" title="Remove widget"><span class="dashicons dashicons-no"></span></a></div>';
							echo '<h3>' . esc_html($widget['title']) . '</h3>';
						echo '</div>';
						echo '<div class="widget-contents">';
							include($widget['template']);
						echo '</div>';
					echo '</div>';
				}
			?>

		</div>

		<div class="propertyhive-home-sidebar">

			<div class="widget widget-pro-promo">
				Unlock more..
			</div>

			<div class="widget widget-support">
				<div class="widget-header">
					<div class="close"><a href="" title="Remove widget"><span class="dashicons dashicons-no"></span></a></div>
					<h3>Need help?</h3>
				</div>
				<div class="widget-contents">
					<ul>
						<li><a href=""><span class="dashicons dashicons-editor-help"></span> Documentation</a></li>
						<li><a href=""><span class="dashicons dashicons-editor-help"></span> FAQs</a></li>
						<li><a href=""><span class="dashicons dashicons-editor-help"></span> Developer directory</a></li>
						<li><a href=""><span class="dashicons dashicons-editor-help"></span> Talk to us (pro only)</a></li>
					</ul>
				</div>
			</div>

		</div>

	</div>
			
</div>