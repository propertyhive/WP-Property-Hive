jQuery(function($) {

    var wp_inline_edit_function = inlineEditPost.edit;

    inlineEditPost.edit = function( id ) {

        wp_inline_edit_function.apply( this, arguments );

        if ( typeof( id ) == 'object' ) {
            id = parseInt( this.getId( id ) );
        }

        if ( id > 0 ) {

            var data = {
                action: 'propertyhive_check_key_date_recurrence',
                post_id: id,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                if ( response == '' )
                {
                    $( '#book_next_key_date_label', specific_post_edit_row ).addClass("disabled");
                }
                else
                {
                    $( ':input[name="next_date_due"]', specific_post_edit_row).val(response);
                }
            });

            var specific_post_edit_row = $( '#edit-' + id ),
                quick_edit_save_button = $( 'button.save', specific_post_edit_row ),
                specific_post_row = $( '#post-' + id ),
                description = $( '.column-description .cell-main-content', specific_post_row ).text(),
                property = $( '.column-property .cell-main-content', specific_post_row ).text(),
                display_status = $( '.column-status .cell-main-content', specific_post_row ).text(),
                real_status = ["pending", "upcoming", "overdue"].includes(display_status.toLowerCase()) ? 'pending' : display_status.toLowerCase();

            // Replace classes so quick edit Update button is next to Cancel
            quick_edit_save_button.addClass('alignmiddle').removeClass('alignright').before('&nbsp;');

            $( ':input[name="_key_date_status"] option[value="' + real_status + '"]', specific_post_edit_row ).attr("selected", "selected").text(display_status);
            $( ':input[name="_key_date_description"]', specific_post_edit_row).val(description);
            $( '.key_date-property', specific_post_edit_row ).text(property);
        }

        $( ':input[name="_key_date_status"]', specific_post_edit_row ).on( 'change', function() {

            if ( !$( '#book_next_key_date_label', specific_post_edit_row ).hasClass('disabled') )
            {
                if ( this.value == 'complete' )
                {
                    // Show Book Next checkbox
                    $( '#book_next_key_date_label', specific_post_edit_row ).show();
                }
                else
                {
                    // Hide Book Next checkbox
                    $( '#book_next_key_date_label', specific_post_edit_row ).hide();

                    // Hide Next Key Date field, if visible
                    $( '#next_date_due_label', specific_post_edit_row ).hide();

                    // Uncheck Book Next checkbox
                    $( '#book_next_key_date_checkbox', specific_post_edit_row ).prop( 'checked', false );
                }
            }
        });

        $( '#book_next_key_date_checkbox', specific_post_edit_row ).on( 'click',  function() {

            // Show/Hide Next Key Date field when checkbox is checked and unchecked
            if( this.checked )
            {
                $( '#next_date_due_label', specific_post_edit_row ).show();
            }
            else
            {
                $( '#next_date_due_label', specific_post_edit_row ).hide();
            }
        });
    }
});