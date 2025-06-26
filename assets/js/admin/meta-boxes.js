var ph_lightbox_open = false; // Used to determine if a details lightbox is open and therefore which post ID (stored in ph_lightbox_post_id) to pass through to AJAX requests
var ph_lightbox_post_id;
var ph_viewing_negotiators_changed = false;

function ph_init_description_editors()
{
    if (propertyhive_admin_meta_boxes.enable_description_editor != true)
    {
        return;
    }

    jQuery('textarea[name=\'_description[]\']').each(function()
    {
        var this_id = jQuery(this).attr('id');
        this_id = this_id.replace("_description_", "");

        if ( this_id != 'id' ) // ignore template room
        {
            wp.editor.remove("_description_" + this_id);
            wp.editor.initialize(
                "_description_" + this_id,
                {
                    'tinymce' : {
                        'toolbar1': 'formatselect,bold,italic,underline,undo,redo,link',
                    },
                    mediaButtons: false,
                    quicktags: false
                }
            );
        }
    });

    jQuery('textarea[name=\'_room_description[]\']').each(function()
    {
        var this_id = jQuery(this).attr('id');
        this_id = this_id.replace("_room_description_", "");

        if ( this_id != 'id' ) // ignore template room
        {
            wp.editor.remove("_room_description_" + this_id);
            wp.editor.initialize(
                "_room_description_" + this_id,
                {
                    'tinymce' : {
                        'toolbar1': 'formatselect,bold,italic,underline,undo,redo,link',
                    },
                    mediaButtons: false,
                    quicktags: false
                }
            );
        }
    });
}

function ph_set_match_price_currency_symbol()
{
    jQuery('#propertyhive-contact-relationships div[id^=\'tab_applicant_data_\']').each(function()
    {
        // loop through each relationship
        var department = jQuery(this).find('input[name^=\'_applicant_department_\']').filter(':checked').val();

        if ( department == 'residential-sales' )
        {
            var selected_currency = jQuery(this).find('select[name^=\'_applicant_currency_sales_\'] :selected').text();
            if ( selected_currency != '' )
            {
                jQuery(this).find('label[for^=\'_applicant_match_price_range_\'] .currency-symbol').html(selected_currency);
            }
        }
        if ( department == 'residential-lettings' )
        {
            // no match price in lettings
        }
    });
}

function show_other_material_information_rows()
{
    jQuery('#propertyhive-property-material-information .form-field[class*=\'_other_field\']').hide();

    jQuery('#propertyhive-property-material-information .form-field:not([class*=\'_other_field\'])').each(function()
    {
        if ( jQuery(this).next('[class*="_other_field"]').length > 0 ) 
        {
            // If select is 'Other' then show row
            var selected_values = jQuery(this).find('select').val();
            if (selected_values && jQuery.inArray('other', selected_values) !== -1) 
            {
                jQuery(this).next('[class*="_other_field"]').show();
            }
        }
    });
}

function ph_check_duplicate_reference_number()
{
    var data = {
        action:         'propertyhive_check_duplicate_reference_number',
        post_id:        propertyhive_admin_meta_boxes.post_id,
        reference_number: jQuery('#propertyhive-property-address input[name=\'_reference_number\']').val(),
        security:       propertyhive_admin_meta_boxes.check_duplicate_reference_number_nonce
    };

    jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
    {
        if ( response == '1' )
        {
            // exists already
            jQuery('#propertyhive-property-address input[name=\'_reference_number\']').after('<span class="duplicate-reference-number-warning">&nbsp; <span class="dashicons dashicons-info" style="color:#72aee6; vertical-align:middle"></span> This reference number is already in use</span>');
        }
    });
}

jQuery( function($){
    
    ph_init_description_editors();

    ph_set_match_price_currency_symbol();

    show_other_material_information_rows();

    ph_check_duplicate_reference_number();

    $('#propertyhive-property-address input[name=\'_reference_number\']').change(function()
    {
        ph_check_duplicate_reference_number();
    });

    $('#propertyhive-property-address input[name=\'_reference_number\']').keydown(function()
    {
        jQuery('#propertyhive-property-address .duplicate-reference-number-warning').remove();
    });

    $('#propertyhive-contact-relationships input[name^=\'_applicant_department_\']').change(function()
    {
        ph_set_match_price_currency_symbol();
    });
    $('#propertyhive-contact-relationships select[name^=\'_applicant_currency_sales_\']').change(function()
    {
        ph_set_match_price_currency_symbol();
    });

    $('#propertyhive-property-material-information .form-field:not([class*=\'_other_field\']) select').on('change', function()
    {
        show_other_material_information_rows();
    });

    // Toggle list table rows on small screens.
    $( 'div[id^=\'propertyhive_\'][id$=\'_meta_box\'], div[id^=\'propertyhive_\'][id$=\'_grid\']' ).on( 'click', '.toggle-row', function() {
        $( this ).closest( 'tr' ).toggleClass( 'is-expanded' );
    });

    $('.propertyhive_meta_box #property_rooms').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'h3',
         update: function( event, ui ) {
            ph_init_description_editors();
         }
     });

    $('.propertyhive_meta_box #property_descriptions').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'h3',
         update: function( event, ui ) {
            ph_init_description_editors();
         }
     });
     
     $('.propertyhive_meta_box #property_features').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_photo_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_floorplan_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_brochure_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_epc_urls').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

     $('.propertyhive_meta_box #property_virtual_tours').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });

    initialise_datepicker();

    // TABS
    $('ul.ph-tabs').show();
    $('div.panel-wrap').each(function(){
        $(this).find('div.panel:not(:first)').hide();
    });
    $('ul.ph-tabs a').click(function(){
        var panel_wrap =  $(this).closest('div.panel-wrap');
        $('ul.ph-tabs li', panel_wrap).removeClass('active');
        $(this).parent().addClass('active');
        $('div.panel', panel_wrap).hide();
        $( $(this).attr('href') ).show();
        return false;
    });
    $('ul.ph-tabs li:visible').eq(0).find('a').click();
    
    // Notes
    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.add_note', function() 
    {
        var section = $(this).attr('data-section');

        if ( propertyhive_admin_meta_boxes.disable_notes_mention != true ) 
        { 
            var content = tinymce.get('add_note').getContent();
            if ( content === false || content === '' )
            {
                return;
            }
        }
        else
        {
            if ( ! $('#propertyhive_' +  section + '_notes_container textarea#add_note').val() ) return;
            content = $('#propertyhive_' +  section + '_notes_container textarea#add_note').val();
        }

        if ( $(this).text() == 'Adding...' ) { return false; }

        $(this).html('Adding...');
        $(this).attr('disabled', 'disabled');
 
        var data = {
            action:         'propertyhive_add_note',
            post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            note:           content.replace(/\\/g, ''),
            note_type:      'propertyhive_note',
            security:       propertyhive_admin_meta_boxes.add_note_nonce,
        };

        if ( $('#propertyhive_' +  section + '_notes_container input[name=\'pinned\']').prop('checked') )
        {
            data.pinned = '1';
        }

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            ph_redraw_notes_grid(section);
        });

        return false;
    });

    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.delete_note', function() 
    {
        var section = $(this).attr('data-section');

        if ( $(this).text() == 'Deleting...' ) { return; }

        var confirm_box = confirm('Are you sure you wish to delete this note?');
        if (!confirm_box)
        {
            return false;
        }

        $(this).html('Deleting...');

        var note = $(this).closest('li.note');
        
        var data = {
            action:         'propertyhive_delete_note',
            note_id:        $(note).attr('rel'),
            security:       propertyhive_admin_meta_boxes.delete_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            ph_redraw_notes_grid(section);
            ph_redraw_pinned_notes_grid(section);
        }, 'json');

        return false;
    });

    $(document).on( 'click', '[id^=\'propertyhive_\'][id$=\'_notes_container\'] a.toggle_note_pinned', function()
    {
        var section = $(this).attr('data-section');

        if ( $(this).text().indexOf('...') >= 0 ) { return; }

        var note = $(this).closest('li.note');

        if ( note.find('div.pinned').length > 0 )
        {
            var loading_text = 'Unpinning...';
        }
        else
        {
            var loading_text = 'Pinning...';
        }
        $(this).html(loading_text);

        var data = {
            action:           'propertyhive_toggle_note_pinned',
            note_id:          $(note).attr('rel'),
            security:         propertyhive_admin_meta_boxes.pin_note_nonce
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            ph_redraw_notes_grid(section);
            ph_redraw_pinned_notes_grid(section);
        }, 'json');

        return false;

    });

    // Notes filter
    $('.notes-filter a').click(function(e)
    {
        e.preventDefault();

        var note_type = $(this).attr('data-filter-class');
        var section = $(this).attr('data-section');

        if ( note_type == '*' )
        {
            // show all notes
            $('#propertyhive_' +  section + '_notes_container .record_notes li').show();

            if ( $('#propertyhive_' +  section + '_notes_container .record_notes li').length > 1 )
            {
                $('#propertyhive_' +  section + '_notes_container .record_notes li#no_notes').hide();
            }
        }
        else
        {
            $('#propertyhive_' +  section + '_notes_container .record_notes li').hide();
            $('#propertyhive_' +  section + '_notes_container .record_notes li.' + note_type).show();
        }

        $(this).parent().parent().find('a').removeClass('current');
        $(this).addClass('current');
    });

    // Key Dates
    var previous_key_date_type;
    $('[id=\'propertyhive-management-dates\']').on( 'focus', '#_add_key_date_type', function() {
        previous_key_date_type = $(this).find("option:selected").text();
    });

    $('[id=\'propertyhive-management-dates\']').on( 'change', '#_add_key_date_type', function() {
        var selected_key_date_type = $('#_add_key_date_type option:selected').text();
        if( $('#_add_key_date_type').val() == '' )
        {
            $('#_add_key_date_description').val('');
        }
        else
        {
            var current_description = $('#_add_key_date_description').val();

            if ( current_description == '' || current_description == previous_key_date_type )
            {
                $('#_add_key_date_description').val(selected_key_date_type);
            }
        }

        previous_key_date_type = selected_key_date_type;
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', 'a.add_key_date', function() {

        if ( !$('#_add_key_date_type').val() || !$('#_add_key_date_description').val() || !$('#_add_key_date_due').val() )
        {
            return false;
        }

        if ( $(this).text() == 'Adding...' ) { return false; }

        $(this).html('Adding...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:               'propertyhive_add_key_date',
            post_id:              propertyhive_admin_meta_boxes.post_id,
            key_date_type:        $('#_add_key_date_type').val(),
            key_date_description: $('#_add_key_date_description').val(),
            key_date_due:         $('#_add_key_date_due').val(),
            key_date_hours:       $('#_add_key_date_due_hours').val(),
            key_date_minutes:     $('#_add_key_date_due_minutes').val(),
            key_date_notes:       $('#_add_key_date_notes').val(),
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            var data = {
                action:  'propertyhive_get_management_dates_grid',
                post_id: propertyhive_admin_meta_boxes.post_id,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_management_dates_container').html(response);
                initialise_datepicker();
            }, 'html');
        });

        return false;
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '#filter-key-dates-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_management_dates_grid',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_type_id: $('#_type_id_filter').val(),
            selected_status:  $('#_date_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_management_dates_container').html(response);
            initialise_datepicker();
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.meta-box-quick-edit', function() {

        var post_id = $(this).attr('id');
        var original_row = $('.post-' + post_id);

        $('.quick-edit-row').hide();
        $('.key-date-row').show();
        original_row.hide();

        if ( $('#quick-edit-' + post_id).length > 0 )
        {
            $('#quick-edit-' + post_id).show();
        }
        else
        {
            original_row.after('<tr id="quick-edit-' + post_id + '" class="quick-edit-row"><td colspan="4">Loading...</td></tr>');

            var data = {
                action: 'propertyhive_get_key_dates_quick_edit_row',
                post_id: propertyhive_admin_meta_boxes.post_id,
                date_post_id: post_id,
                description: $('.post-' + post_id + ' .description .cell-main-content').text(),
                status: $('.post-' + post_id + ' .status .cell-main-content').text(),
                due_date_time: $('.post-' + post_id + ' .date_due .cell-main-content').text(),
                notes: $('.post-' + post_id + ' .notes .hidden-key-date-notes').text(),
                type: $('.post-' + post_id + ' .hidden-date-type-id').text(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                $('#quick-edit-' + post_id).html(response);
                initialise_datepicker();
            }, 'html');
        }

        return false;

    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.meta-box-delete', function() {

        var confirm_box = confirm('Are you sure you wish to delete this key date?');
        if (!confirm_box)
        {
            return confirm_box;
        }

        var post_id = $(this).attr('id');

        if ( $('#quick-edit-' + post_id).length > 0 )
        {
            $('#quick-edit-' + post_id).show();
        }
        else
        {
            var data = {
                action: 'propertyhive_delete_key_date',
                date_post_id: post_id,
                security: propertyhive_admin_meta_boxes.delete_key_date_nonce,
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                var data = {
                    action:  'propertyhive_get_management_dates_grid',
                    post_id: propertyhive_admin_meta_boxes.post_id,
                };

                jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
                {
                    jQuery('#propertyhive_management_dates_container').html(response);
                    initialise_datepicker();
                }, 'html');
            }, 'json');
        }

        return false;

    });

    $('[id=\'propertyhive-management-dates\']').on( 'change', '#key_date_status', function() {

        var quick_edit_row = $(this).closest('tr');
        var next_key_date_checkbox = quick_edit_row.find('#next_key_date_checkbox');

        if (next_key_date_checkbox.length !== 0)
        {
            if ( this.value == 'complete' )
            {
                // Show Book Next checkbox
                next_key_date_checkbox.removeClass('hidden');
            }
            else
            {
                if( !next_key_date_checkbox.hasClass('hidden') )
                {
                    // Hide Book Next checkbox
                    next_key_date_checkbox.addClass('hidden');

                    // Hide Next Key Date field, if visible
                    var next_key_date_field = quick_edit_row.find('#next_key_date_field');
                    if( !next_key_date_field.hasClass('hidden') )
                    {
                        next_key_date_field.addClass('hidden');
                    }

                    // Uncheck Book Next checkbox
                    quick_edit_row.find('#book_next_key_date').prop( 'checked', false );
                }
            }
        }
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '#book_next_key_date', function() {

        // Show/Hide Next Key Date field when checkbox is checked and unchecked
        var next_key_date_field = $(this).closest('tr').find('#next_key_date_field');
        if( this.checked )
        {
            next_key_date_field.removeClass('hidden');
        }
        else
        {
            if( !next_key_date_field.hasClass('hidden') )
            {
                next_key_date_field.addClass('hidden');
            }
        }
    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.save-quick-edit', function() {

        var date_post_id = $(this).attr('id');

        if ( $(this).text() == 'Saving...' ) { return false; }

        $(this).text('Saving...');
        $(this).attr('disabled', 'disabled');

        var quick_edit_row = $('#quick-edit-' + date_post_id);

        var data = {
            action: 'propertyhive_save_key_date',
            post_id: date_post_id,
            description: quick_edit_row.find('#date_description').val(),
            status: quick_edit_row.find('#key_date_status').val(),
            due_date_time: quick_edit_row.find('#date_due_quick_edit').val() + ' ' + quick_edit_row.find('#date_due_hours_quick_edit').val() + ':' + quick_edit_row.find('#date_due_minutes_quick_edit').val(),
            type: quick_edit_row.find('#date_type').val(),
            notes: quick_edit_row.find('#date_notes_quick_edit').val(),
            security: propertyhive_admin_meta_boxes.save_key_date_nonce,
        };

        if (quick_edit_row.find('#book_next_key_date').length !== 0)
        {
            if ( quick_edit_row.find('#book_next_key_date').is(":checked") )
            {
                data.next_key_date = quick_edit_row.find('#next_key_date').val() + ' ' + quick_edit_row.find('#next_key_date_hours').val() + ':' + quick_edit_row.find('#next_key_date_minutes').val();
            }
        }

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            var data = {
                action:           'propertyhive_get_management_dates_grid',
                post_id:          propertyhive_admin_meta_boxes.post_id,
                selected_type_id: $('#_type_id_filter').val(),
                selected_status:  $('#_date_status_filter').val(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_management_dates_container').html(response);
                initialise_datepicker();
            }, 'html');
        }, 'html');

    });

    $('[id=\'propertyhive-management-dates\']').on( 'click', '.cancel-quick-edit', function() {
        $('.quick-edit-row').hide();
        $('.post-' + $(this).attr('id')).show();
    });

    $('[id=\'propertyhive-property-tenancies\']').on( 'click', '#filter-property-tenancies-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_tenancies_grid',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_tenancy_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_tenancies_grid').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-property-viewings\']').on( 'click', '#filter-property-viewings-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_viewings_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_viewing_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_viewings_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-contact-tenancies\']').on( 'click', '#filter-contact-tenancies-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_contact_tenancies_grid',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_tenancy_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_contact_tenancies_grid').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-contact-viewings\']').on( 'click', '#filter-contact-viewings-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_contact_viewings_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_viewing_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_contact_viewings_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-property-offers\']').on( 'click', '#filter-property-offers-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_offers_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_offer_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_offers_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-contact-offers\']').on( 'click', '#filter-contact-offers-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_contact_offers_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_offer_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_contact_offers_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-property-sales\']').on( 'click', '#filter-property-sales-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_sales_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_sale_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_sales_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-contact-sales\']').on( 'click', '#filter-contact-sales-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_contact_sales_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_sale_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_contact_sales_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-property-enquiries\']').on( 'click', '#filter-property-enquiries-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_property_enquiries_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_enquiry_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_property_enquiries_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('[id=\'propertyhive-contact-enquiries\']').on( 'click', '#filter-contact-enquiries-grid', function() {

        if ( $(this).val() == 'Updating...' ) { return false; }

        $(this).val('Updating...');
        $(this).attr('disabled', 'disabled');

        var data = {
            action:           'propertyhive_get_contact_enquiries_meta_box',
            post_id:          propertyhive_admin_meta_boxes.post_id,
            selected_status:  $('#_enquiry_status_filter').val(),
        };

        jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
        {
            jQuery('#propertyhive_contact_enquiries_meta_box').html(response);
        }, 'html');

        return false;
    });

    $('.postbox').on( 'click', 'a[name=\'export_action\'][id^=\'export-\']', function(e) 
    {
        e.preventDefault();

        var record_ids = [];

        $(this).parent().parent().parent().find('a[data-viewing-id]').each(function()
        {
            record_ids.push( parseInt($(this).attr('data-viewing-id')) );
        });
        $(this).parent().parent().parent().find('a[data-offer-id]').each(function()
        {
            record_ids.push( parseInt($(this).attr('data-offer-id')) );
        });
        $(this).parent().parent().parent().find('a[data-sale-id]').each(function()
        {
            record_ids.push( parseInt($(this).attr('data-sale-id')) );
        });

        var new_location = window.location.href;
        new_location = new_location.split("#");
        new_location = new_location[0];

        window.location.href = new_location + '&sub_grid=' + $(this).attr('id').replace("export-", "") + '&record_ids=' + record_ids.join("|")
    });
    
    // Multiselect
    $(".propertyhive_meta_box select.multiselect").chosen();

    $(".post-type-viewing #_negotiator_ids").on('change', function()
    {
        ph_viewing_negotiators_changed = true;
    });

    $( document ).on('click', '.viewing-lightbox', function(e)
    {
        e.preventDefault();
        
        ph_open_details_lightbox($(this).attr('data-viewing-id'), 'viewing');
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-close', function(e)
    {
        e.preventDefault();

        $.fancybox.close();
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-prev', function(e)
    {
        e.preventDefault();

        var previous_post_id = false;
        $('a[data-viewing-id]').each(function()
        {
            var post_id = $(this).attr('data-viewing-id');

            if ( post_id == ph_lightbox_post_id )
            {
                ph_open_details_lightbox(previous_post_id, 'viewing');

                return;
            }

            previous_post_id = post_id;
        });
    });

    $( document ).on('click', '.propertyhive-lightbox-buttons a.button-next', function(e)
    {
        e.preventDefault();

        var use_next = false;
        $('a[data-viewing-id]').each(function()
        {
            var post_id = $(this).attr('data-viewing-id');

            if (use_next)
            {
                ph_open_details_lightbox(post_id, 'viewing');

                return false;
            }

            if ( post_id == ph_lightbox_post_id )
            {
                use_next = true;
            }
        });
    });

    $('#tenure_id').change(function()
    {
        $('#leasehold_information').hide();

        var selected_tenure = $(this).find('option:selected').text();

        $.each(propertyhive_admin_meta_boxes.leasehold_tenures, function(index, value) 
        { 
            if ( selected_tenure != '' && value.toLowerCase() === selected_tenure.toLowerCase() ) 
            {
                $('#leasehold_information').show();
            }
        });
    });

    $('#_shared_ownership').change(function()
    {
        if ( $(this).is(':checked') )
        {
            $('#shared_ownership_information').show();
        }
        else
        {
            $('#shared_ownership_information').hide();
        }
    });
});

function ph_open_details_lightbox(post_id, section)
{
    ph_lightbox_post_id = post_id;

    jQuery.fancybox.close();

    jQuery.fancybox.open({
        src  : ajaxurl + '?action=propertyhive_get_' + section + '_lightbox&post_id=' + ph_lightbox_post_id,
        type : 'ajax',
        beforeLoad: function()
        {
            ph_lightbox_open = true;
        },
        afterShow: function()
        {
            // hide/show next/prev buttons
            var found_current = false;
            var previous_exist = false;
            var next_exist = false;
            jQuery('a[data-' + section + '-id]').each(function()
            {
                var post_id = jQuery(this).attr('data-' + section + '-id');

                if ( found_current )
                {
                    next_exist = true;
                }

                if ( post_id == ph_lightbox_post_id )
                {
                    // this is the lightbox being viewed
                    found_current = true;
                }
                else
                {
                    if ( !found_current )
                    {
                        previous_exist = true;
                    }
                }
            });

            if ( previous_exist )
            {
                jQuery('.propertyhive-lightbox-buttons a.button-prev').show();
            }
            if ( next_exist )
            {
                jQuery('.propertyhive-lightbox-buttons a.button-next').show();
            }

            if ( propertyhive_admin_meta_boxes.disable_notes_mention != true ) 
            {
                tinymce.remove('.propertyhive-lightbox-notes textarea#add_note');

                tinymce.init({
                    selector: '.propertyhive-lightbox-notes textarea#add_note',
                    menubar: false,
                    toolbar: false,
                    statusbar: false,
                    forced_root_block: '', // Disable the <p> tags
                    force_br_newlines: true,
                    force_p_newlines: false,
                    external_plugins: 
                    {
                        'mention' : propertyhive_admin_meta_boxes.plugin_url + '/assets/js/tinymce-mention-plugin/tinymce-mention-plugin.js',
                        'placeholder' : propertyhive_admin_meta_boxes.plugin_url + '/assets/js/tinymce-placeholder/mce.placeholder.js'
                    },
                    setup: function(editor) 
                    {
                        editor.on('init', function() 
                        {
                            editor.getContainer().style.border = '1px solid #ccc';
                        });
                    },
                    mentions: 
                    {
                        source: function (query, process, delimiter) 
                        {
                            // Do your ajax call
                            jQuery.ajax({
                                url: ajaxurl, // Use WordPress AJAX URL
                                method: 'POST',
                                data: {
                                    action: 'propertyhive_fetch_note_mentions',
                                    query: query,
                                    security: propertyhive_admin_meta_boxes.get_notes_nonce
                                },
                                success: function(response) {
                                    console.log(response);
                                    process(response);
                                }
                            });
                        }
                    },
                    content_style: `
                      html, body {
          height: 100%; /* Set both html and body to 100% height */
          margin: 0;
          padding: 0;
          overflow: hidden; /* Prevent scrolling issues */
        }
        .mce-content-body {
          margin: 0; /* Replace margin with padding */
          padding: 1rem; /* Add padding */
          box-sizing: border-box; /* Include padding in height calculation */
          height: 100%;
          overflow-y: auto; /* Allow vertical scrolling within the body */
        }
                    `
                });
            }
        },
        beforeClose: function()
        {
            ph_lightbox_open = false;
        }
    });
}

// VIEWINGS //
jQuery(window).on('load', function()
{
    //redraw_viewing_details_meta_box(); // called from within redraw_viewing_actions()
    redraw_viewing_actions();
});

function redraw_viewing_details_meta_box()
{
    if ( jQuery('#propertyhive_viewing_details_meta_box_container').length > 0 )
    {
        jQuery('#propertyhive_viewing_details_meta_box_container').html('Loading...');

        var data = {
            action:         'propertyhive_get_viewing_details_meta_box',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            security:       propertyhive_admin_meta_boxes.viewing_details_meta_nonce,
            readonly:       ph_lightbox_open
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            jQuery('#propertyhive_viewing_details_meta_box_container').html(response);
        }, 'html');
    }
}

function redraw_viewing_actions()
{
    if ( jQuery('#propertyhive_viewing_actions_meta_box_container').length > 0 )
    {
        jQuery('#propertyhive_viewing_actions_meta_box_container').html('Loading...');
        
        var data = {
            action:         'propertyhive_get_viewing_actions',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            jQuery('#propertyhive_viewing_actions_meta_box_container').html(response);

            jQuery(document).trigger('ph:adminViewingActionsRedrawn');
            jQuery(document).trigger('ph:adminPostActionsRedrawn', ['viewing']);

            ph_redraw_notes_grid('viewing');
        }, 'html');
    }

    redraw_viewing_details_meta_box();
}

jQuery(document).ready(function($)
{
    $(document).on('click', 'a.viewing-action', function(e)
    {
        e.preventDefault();

        var this_href = $(this).attr('href');

        $(this).attr('disabled', 'disabled');

        if ( this_href == '#action_panel_viewing_carried_out' )
        {
            var data = {
                action:         'propertyhive_viewing_carried_out',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response)
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_no_show' )
        {
            var data = {
                action:         'propertyhive_viewing_no_show',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_applicant_booking_confirmation' )
        {
            var data = {
                action:         'propertyhive_viewing_email_applicant_booking_confirmation',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_owner_booking_confirmation' )
        {
            var data = {
                action:         'propertyhive_viewing_email_owner_booking_confirmation',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_attending_negotiator_booking_confirmation' )
        {
            if ( ph_viewing_negotiators_changed )
            {
                alert("The negotiators have changed since the viewing was last saved. Please save the viewing then try again");
                return;
            }

            var data = {
                action:         'propertyhive_viewing_email_attending_negotiator_booking_confirmation',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_applicant_cancellation_notification' )
        {
            var data = {
                action:         'propertyhive_viewing_email_applicant_cancellation_notification',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_owner_cancellation_notification' )
        {
            var data = {
                action:         'propertyhive_viewing_email_owner_cancellation_notification',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_email_attending_negotiator_cancellation_notification' )
        {
            if ( ph_viewing_negotiators_changed )
            {
                alert("The negotiators have changed since the viewing was last saved. Please save the viewing then try again");
                return;
            }

            var data = {
                action:         'propertyhive_viewing_email_attending_negotiator_cancellation_notification',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                if ( !response.success )
                {
                    alert('Error: ' + response.data);
                }
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_feedback_not_required' )
        {
            var data = {
                action:         'propertyhive_viewing_feedback_not_required',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_feedback_passed_on' )
        {
            var data = {
                action:         'propertyhive_viewing_feedback_passed_on',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_pending' )
        {
            var data = {
                action:         'propertyhive_viewing_revert_pending',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_viewing_revert_feedback_pending' )
        {
            var data = {
                action:         'propertyhive_viewing_revert_feedback_pending',
                viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
                security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
            };
            jQuery.post( ajaxurl, data, function(response) 
            {
                redraw_viewing_actions();
            }, 'json');
            return;
        }

        $('#propertyhive_viewing_actions_meta_box').stop().fadeOut(300, function()
        {
            $(this_href).stop().fadeIn(300, function()
            {
                
            });
        });
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.action-cancel', function(e)
    {
        e.preventDefault();

        redraw_viewing_actions();
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.owner-booking-confirmation-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_owner_booking_confirmation');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_owner_confirmation_email_subject').val());
        form_data.append('body', $('#_owner_confirmation_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_owner_confirmation_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });
        return;
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.applicant-booking-confirmation-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_applicant_booking_confirmation');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_applicant_confirmation_email_subject').val());
        form_data.append('body', $('#_applicant_confirmation_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_applicant_confirmation_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });
        return;
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.attending-negotiator-booking-confirmation-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_attending_negotiator_booking_confirmation');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_attending_negotiator_confirmation_email_subject').val());
        form_data.append('body', $('#_attending_negotiator_confirmation_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_attending_negotiator_confirmation_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });

        return;
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.owner-cancellation-notification-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_owner_cancellation_notification');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_owner_cancellation_notification_email_subject').val());
        form_data.append('body', $('#_owner_cancellation_notification_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_owner_cancellation_notification_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });
        return;
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.applicant-cancellation-notification-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_applicant_cancellation_notification');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_applicant_cancellation_notification_email_subject').val());
        form_data.append('body', $('#_applicant_cancellation_notification_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_applicant_cancellation_notification_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });
        return;
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.attending-negotiator-cancellation-notification-action-submit', function(e)
    {
        e.preventDefault();

        var ph_action_button = $(this);
        ph_action_button.attr('disabled', 'disabled');

        // Create FormData object and append data
        var form_data = new FormData();
        form_data.append('action', 'propertyhive_viewing_email_attending_negotiator_cancellation_notification');
        form_data.append('viewing_id', ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ));
        form_data.append('subject', $('#_attending_negotiator_cancellation_notification_email_subject').val());
        form_data.append('body', $('#_attending_negotiator_cancellation_notification_email_body').val());
        form_data.append('security', propertyhive_admin_meta_boxes.viewing_actions_nonce);

        // Get the file input element and the selected files
        var file_input = $('#_attending_negotiator_cancellation_notification_email_attachment');
        var files = file_input.prop('files');   

        // Check if at least one file has been selected
        if (files.length !== 0) 
        {
            // Append each file to the FormData object
            $.each(files, function(index, file) 
            {
                form_data.append('attachments[]', file);
            });
        }

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            dataType: 'json', // Expect JSON response
            success: function(response) 
            {
                if (response.success) 
                {
                    redraw_viewing_actions();
                }
                else
                {
                    alert('Error: ' + response.data);
                    ph_action_button.attr('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error: ' + textStatus + ' : ' + errorThrown);
                alert('An unexpected error occurred: ' + textStatus + ' : ' + errorThrown);
            }
        });

        return;
    });


    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.cancelled-reason-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_cancelled',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            cancelled_reason: $('#_cancelled_reason').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        if ($('#_cancelled_reason_public').is(':checked')) 
        {
            data.cancelled_reason_public = $('#_cancelled_reason_public').val();
        }

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.interested-feedback-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_interested_feedback',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            feedback:       $('#_interested_feedback').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    });

    $(document).on('click', '#propertyhive_viewing_actions_meta_box_container a.not-interested-feedback-action-submit', function(e)
    {
        e.preventDefault();

        $(this).attr('disabled', 'disabled');

        // Submit interested feedback
        var data = {
            action:         'propertyhive_viewing_not_interested_feedback',
            viewing_id:     ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
            feedback:       $('#_not_interested_feedback').val(),
            security:       propertyhive_admin_meta_boxes.viewing_actions_nonce,
        };

        jQuery.post( ajaxurl, data, function(response) 
        {
            redraw_viewing_actions();
        }, 'json');
    })
});

function ph_redraw_notes_grid(section)
{
    var data = {
        action:         'propertyhive_get_notes_grid',
        post_id:        ( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_admin_meta_boxes.post_id ),
        section:        section,
        security:       propertyhive_admin_meta_boxes.get_notes_nonce
    };

    jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
    {
        if ( propertyhive_admin_meta_boxes.disable_notes_mention != true ) 
        { 
            tinymce.remove('.propertyhive-lightbox-notes textarea#add_note'); 
            tinymce.remove('#propertyhive-' + section + '-history-notes textarea#add_note'); 
        }

        jQuery('#propertyhive_' +  section + '_notes_container').html(response);

        if ( propertyhive_admin_meta_boxes.disable_notes_mention != true ) 
        { 
            tinymce.init({
                selector: ( ph_lightbox_open ? '.propertyhive-lightbox-notes' : '#propertyhive-' + section + '-history-notes' ) + ' textarea#add_note',
                menubar: false,
                toolbar: false,
                statusbar: false,
                forced_root_block: '', // Disable the <p> tags
                force_br_newlines: true,
                force_p_newlines: false,
                external_plugins: 
                {
                    'mention' : propertyhive_admin_meta_boxes.plugin_url + '/assets/js/tinymce-mention-plugin/tinymce-mention-plugin.js',
                    'placeholder' : propertyhive_admin_meta_boxes.plugin_url + '/assets/js/tinymce-placeholder/mce.placeholder.js'
                },
                setup: function(editor) 
                {
                    editor.on('init', function() 
                    {
                        editor.getContainer().style.border = '1px solid #ccc';
                    });
                },
                mentions: 
                {
                    source: function (query, process, delimiter) 
                    {
                        // Do your ajax call
                        jQuery.ajax({
                            url: ajaxurl, // Use WordPress AJAX URL
                            method: 'POST',
                            data: {
                                action: 'propertyhive_fetch_note_mentions',
                                query: query,
                                security: propertyhive_admin_meta_boxes.get_notes_nonce
                            },
                            success: function(response) {
                                process(response);
                            }
                        });
                    }
                },
                content_style: `
                  html, body {
      height: 100%; /* Set both html and body to 100% height */
      margin: 0;
      padding: 0;
      overflow: hidden; /* Prevent scrolling issues */
    }
    .mce-content-body {
      margin: 0; /* Replace margin with padding */
      padding: 1rem; /* Add padding */
      box-sizing: border-box; /* Include padding in height calculation */
      height: 100%;
      overflow-y: auto; /* Allow vertical scrolling within the body */
    }
                `
            });
        }
    }, 'html');
}

function ph_redraw_pinned_notes_grid(section)
{
    var data = {
        action:         'propertyhive_get_pinned_notes_grid',
        post_id:        propertyhive_admin_meta_boxes.post_id,
        pinned:         1,
        section:        section,
        security:       propertyhive_admin_meta_boxes.get_notes_nonce
    };

    jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
    {
        jQuery('#propertyhive_' +  section + '_pinned_notes_container').html(response);
    }, 'html');
}

function initialise_datepicker() {
    jQuery( ".date-picker" ).datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: true
    }).on("change", function(e) {
        var curDate = jQuery(this).val();
        var valid  = true;
        
        if ( curDate != '' )
        {
            var splitDate = curDate.split("-")
            if ( splitDate.length != 3 )
            {
                valid = false;
            }
            else
            {
                if ( splitDate[0].length != 4 || splitDate[1].length != 2 || splitDate[2].length != 2 )
                {
                    valid = false;
                }
            }

            if (!valid) 
            {
                alert("Invalid date entered. Please select a date from the calendar and ensure date is in the format YYYY-MM-DD");
            }
        }
    });
}

function add_months(date, months) {
    var d = date.getDate();
    date.setMonth(date.getMonth() + +months);
    if (date.getDate() != d)
    {
        date.setDate(0);
    }
    return date;
}
