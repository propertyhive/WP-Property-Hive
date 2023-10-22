<div class="wrap propertyhive">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<div class="icon32 icon32-propertyhive-settings" id="icon-propertyhive"><br /></div><h2 class="nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label )
					echo '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=' . $name ) . '" class="nav-tab nav-tab-' . sanitize_title($name) . ' ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

				do_action( 'propertyhive_settings_tabs' );
			?>
		</h2>

		<?php
			do_action( 'propertyhive_sections_' . $current_tab );
			do_action( 'propertyhive_settings_' . $current_tab );
			do_action( 'propertyhive_settings_tabs_' . $current_tab ); // @deprecated hook
		?>

        <p class="submit">
        	<?php 
        	   if ( ! isset( $GLOBALS['hide_save_button'] ) )
               {
                   $button_text = __( 'Save changes', 'propertyhive' );
                   if ( isset( $GLOBALS['save_button_text'] ) && ! empty( $GLOBALS['save_button_text'] ) )
                   {
                       $button_text = $GLOBALS['save_button_text'];
                   }
            ?>
        		<button name="save" class="button-primary" type="submit" value="<?php echo esc_attr($button_text); ?>"><?php echo esc_html($button_text); ?></button>
        	<?php
               }
        	?>
        	<?php 
        	   if ( isset( $GLOBALS['show_cancel_button'] ) && $GLOBALS['show_cancel_button'] === TRUE )
        	   {
        	       $cancel_href = 'javascript:history.go(-1);';
                   if ( isset( $GLOBALS['cancel_button_href'] ) && ! empty( $GLOBALS['cancel_button_href'] ) )
                   {
                       $cancel_href = $GLOBALS['cancel_button_href'];
                   }
        	?>
                <a href="<?php echo $cancel_href; ?>" class="button"><?php _e( 'Cancel', 'propertyhive' ); ?></a>
            <?php
                }
            ?>
        	<input type="hidden" name="subtab" id="last_tab" />
        	<?php wp_nonce_field( 'propertyhive-settings' ); ?>
        </p>
	</form>
</div>