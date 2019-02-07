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

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            $('ul.record_notes').prepend( response );
            $('li#no_notes').hide();
            $('#add_note').val('');
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
        });

        return false;
    });
    
    // Multiselect
    $(".propertyhive_meta_box select.multiselect").chosen();

    // Enforce numeric and comma in contact phone numbers only
    $("#_telephone_number").on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
        var cursor = this.selectionStart,
        regex = /[^0-9,]/gi,
        value = $(this).val();
        
        if(regex.test(value))
        {
            $(this).val(value.replace(regex, ''));
            cursor--;
        }
        
        this.setSelectionRange(cursor, cursor);
      });
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

function restrict_phone_number() {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
        if (inputFilter(this.value)) {
            this.oldValue = this.value;
            this.oldSelectionStart = this.selectionStart;
            this.oldSelectionEnd = this.selectionEnd;
        } else if (this.hasOwnProperty("oldValue")) {
            this.value = this.oldValue;
            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        }
    });
}

// Restricts input for each element in the set of matched elements to the given inputFilter.
(function($) {
  $.fn.inputFilter = function(inputFilter) {
    return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
      if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty("oldValue")) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      }
    });
  };
}(jQuery));