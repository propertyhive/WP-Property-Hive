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

     $('.propertyhive_meta_box #property_virtual_tours').sortable({
         opacity: 0.8,
         revert: true,
         handle: 'label'
     });
     
    // DATE PICKER FIELDS
    $( ".date-picker" ).datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: true
    });
    
    // Contact relationship modals
    /*$( ".open-modal" ).click(function() {
        $( $(this).attr('href') ).dialog({
            autoOpen: false,
            resizable: false,
            draggable: false
        });
        $( $(this).attr('href') ).dialog( "open" );
        return false;
    });*/
    
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
    
    // Property notes
    $('#propertyhive-property-notes').on( 'click', 'a.add_note', function() {
        if ( ! $('textarea#add_property_note').val() ) return;

        //$('#propertyhive-property-notes').block({ message: null, overlayCSS: { background: '#fff url(' + propertyhive_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
        
        var data = {
            action:         'propertyhive_add_note',
            post_id:        propertyhive_admin_meta_boxes.post_id,
            note:           $('textarea#add_property_note').val(),
            note_type:      'propertyhive_note',
            security:       propertyhive_admin_meta_boxes.add_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            $('ul.record_notes').prepend( response );
            //$('#propertyhive-property-notes').unblock();
            $('#add_property_note').val('');
        });

        return false;

    });

    $('#propertyhive-property-notes').on( 'click', 'a.delete_note', function() {
        
        var note = $(this).closest('li.note');
        
        //$(note).block({ message: null, overlayCSS: { background: '#fff url(' + propertyhive_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

        var data = {
            action:         'propertyhive_delete_note',
            note_id:        $(note).attr('rel'),
            security:       propertyhive_admin_meta_boxes.delete_note_nonce,
        };

        $.post( propertyhive_admin_meta_boxes.ajax_url, data, function(response) {
            $(note).remove();
        });

        return false;
    });
    
    // Multiselect
    $("#propertyhive-property-residential-details select.multiselect").chosen();

});