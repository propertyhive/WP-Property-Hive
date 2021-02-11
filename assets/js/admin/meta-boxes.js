jQuery( function($){
    
    $('.propertyhive_meta_box #property_rooms').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'h3'
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
    //$('#propertyhive-property-notes, #propertyhive-contact-notes, #propertyhive-enquiry-notes, #propertyhive-viewing-notes, #propertyhive-offer-notes, #propertyhive-sale-notes').on( 'click', 'a.add_note', function() {
    $('[id^=\'propertyhive-\'][id$=\'-notes\']').on( 'click', 'a.add_note', function() {
        if ( ! $('textarea#add_note').val() ) return;

        if ( $(this).text() == 'Adding...' ) { return false; }

        $(this).html('Adding...');
        $(this).attr('disabled', 'disabled');
 
        var data = {
            action:         'propertyhive_add_note',
            post_id:        propertyhive_admin_meta_boxes.post_id,
            note:           $('textarea#add_note').val(),
            note_type:      'propertyhive_note',
            security:       propertyhive_admin_meta_boxes.add_note_nonce,
        };

        if ( $('#pinned').prop('checked') )
        {
            data.pinned = '1';
        }

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        propertyhive_admin_meta_boxes.post_id,
                section:        jQuery('#notes_grid_section').val(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_notes_container').html(response);
            }, 'html');
        });

        return false;
    });

    $('[id^=\'propertyhive-\'][id$=\'-notes\']').on( 'click', 'a.delete_note', function() {
        
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
            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        propertyhive_admin_meta_boxes.post_id,
                section:        jQuery('#notes_grid_section').val(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_notes_container').html(response);
            }, 'html');
        }, 'json');

        return false;
    });

    $('[id^=\'propertyhive-\'][id$=\'-notes\']').on( 'click', 'a.toggle_note_pinned', function() {

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
            security:         propertyhive_admin_meta_boxes.pin_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {

            var data = {
                action:         'propertyhive_get_notes_grid',
                post_id:        propertyhive_admin_meta_boxes.post_id,
                section:        jQuery('#notes_grid_section').val(),
            };

            jQuery.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response)
            {
                jQuery('#propertyhive_notes_container').html(response);
            }, 'html');

        }, 'json');

        return false;

    });

    // Notes filter
    $('.notes-filter a').click(function(e)
    {
        e.preventDefault();

        var note_type = $(this).attr('data-filter-class');

        if ( note_type == '*' )
        {
            // show all notes
            $('.record_notes li').show();

            if ( $('.record_notes li').length > 1 )
            {
                $('.record_notes li#no_notes').hide();
            }
        }
        else
        {
            $('.record_notes li').hide();
            $('.record_notes li.' + note_type).show();
        }

        $('.notes-filter a').removeClass('current');
        $(this).addClass('current');
    });

    // Key Dates
    $('[id=\'propertyhive-management-dates\']').on( 'click', 'a.add_key_date', function() {

        if ( !$('#_add_key_date_description').val() || !$('#_add_key_date_due').val() ) return;

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
        };

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
            jQuery('#propertyhive_property_tenancies_container').html(response);
        }, 'html');

        return false;
    });
    
    // Multiselect
    $(".propertyhive_meta_box select.multiselect").chosen();
});

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
