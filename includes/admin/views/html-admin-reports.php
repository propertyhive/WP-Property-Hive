<div class="wrap propertyhive">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<div class="icon32 icon32-propertyhive-settings" id="icon-propertyhive"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label )
					echo '<a href="' . admin_url( 'admin.php?page=ph-reports&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

				do_action( 'propertyhive_reports_tabs' );
			?>
		</h2>

		<?php
			include("html-admin-reports-" . $current_tab . ".php");
		?>
	</form>
</div>