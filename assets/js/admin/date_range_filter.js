jQuery(function() {

    function update_date_range_filter(start, end) {

        var picker = jQuery('#date_range').data('daterangepicker');
        var option = jQuery('#date_range option')
        var date_range_from = jQuery('#date_range_from')
        var date_range_to = jQuery('#date_range_to')

        switch (picker.chosenLabel) {

            case 'Custom Range':
                option.html(start.format("Do MMMM YYYY") + ' - ' + end.format('Do MMMM YYYY'))
                option.val(start.format("Do MMMM YYYY") + ' - ' + end.format('Do MMMM YYYY'))
                date_range_from.val(start.format('YYYY-MM-DD'))
                date_range_to.val(end.format('YYYY-MM-DD'))
                break

            default:
                option.html(picker.chosenLabel)
                option.val(picker.chosenLabel)
                date_range_from.val(start.format('YYYY-MM-DD'))
                date_range_to.val(end.format('YYYY-MM-DD'))
                break
        }
    }

    jQuery('#date_range').daterangepicker({
        startDate: moment(jQuery('#date_range_from').val(), 'YYYY-MM-DD'),
        endDate: moment(jQuery('#date_range_to').val(), 'YYYY-MM-DD'),
        autoApply: true,
        ranges: {
            // The date picker doesn't have a concept of 'Any Time', so valid dates must be used
            // I've used the last and first date of the month (reversed) as it's a range that is not selectable, but is within the current month
            // If I used an already labelled date range (e.g. 'Today'), it would show as 'Today' when selected
            // If I use a nearby date range (e.g. 'Yesterday'), if someone actually selected that range it would show as 'Any Time'
            // If I use a unlikely date range (e.g. 01-01-1970 - 31-12-2070), the custom date range picker would open showing Jan 1970.
            'Any Time' : [moment().endOf('month'), moment().startOf('month')],
            'Today': [moment(), moment()],
            'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()]
        },
    }, update_date_range_filter);

    // This is a little janky. A more preferable solution would be to prevent the default Select dropdown from opening in the first place
    jQuery('#date_range').on('show.daterangepicker', function(ev, picker) {
        jQuery('#date_range').blur();
    });
    jQuery('#date_range').on('hide.daterangepicker', function(ev, picker) {
        jQuery('#date_range').blur();
    });
})
