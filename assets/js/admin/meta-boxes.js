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
            $('ul.record_notes').prepend( response );
            $('li#no_notes').hide();
            $('#add_note').val('');
            $('#pinned').prop("checked", false);
        });

        return false;
    });

    $('[id^=\'propertyhive-\'][id$=\'-notes\']').on( 'click', 'a.delete_note', function() {
        
        var confirm_box = confirm('Are you sure you wish to delete this note?');
        if (!confirm_box)
        {
            return false;
        }

        var note = $(this).closest('li.note');
        
        var data = {
            action:         'propertyhive_delete_note',
            note_id:        $(note).attr('rel'),
            security:       propertyhive_admin_meta_boxes.delete_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            $(note).remove();

            if ($('ul.record_notes li').length <= 1)
            {
                $('li#no_notes').show();
            }
        }, 'json');

        return false;
    });

    $('[id^=\'propertyhive-\'][id$=\'-notes\']').on( 'click', 'a.toggle_note_pinned', function() {

        var note = $(this).closest('li.note');

        var data = {
            action:           'propertyhive_toggle_note_pinned',
            note_id:          $(note).attr('rel'),
            security:         propertyhive_admin_meta_boxes.pin_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {

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
