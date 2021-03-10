jQuery(function($) {

    var wp_inline_edit_function = inlineEditPost.edit;

    inlineEditPost.edit = function( id ) {

        wp_inline_edit_function.apply( this, arguments );

        if ( typeof( id ) == 'object' ) {
            id = parseInt( this.getId( id ) );
        }

        if ( id > 0 ) {

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
    }
});