jQuery(function($)
{
    var qvars = ph_utm_get_url_vars()

    $.each([ 'utm_source','utm_medium','utm_term', 'utm_content', 'utm_campaign', 'gclid', 'fbclid' ], function( i,v ) {

        var cookie_field = ph_utm_get_q_vars(v,qvars)

        if ( cookie_field != '' )
            ph_utm_set_cookie(v, cookie_field, 30);

        var curval = ph_utm_get_cookie(v)

        if (curval != undefined) {
            curval = decodeURIComponent(curval).replace(/[%]/g,' ')
            if (v == 'username') {
                //Maybe this should apply to all... We'll see...
                curval = curval.replace(/\+/g, ' ')
            }

            jQuery('input[name=\"'+v+'\"]').val(curval)
            jQuery('input#'+v).val(curval)
            jQuery('input.'+v).val(curval)
        }
    });
});

function ph_utm_get_q_vars(v,qvars){
    if (qvars[v] != undefined) {
        return qvars[v]
    }
    return ''
}

function ph_utm_get_url_vars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function ph_utm_set_cookie(name, value, days) {
    if (!value) return;
    var expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + encodeURIComponent(value) + ';path=/;expires=' + expires.toUTCString();
}

function ph_utm_get_cookie(name) {
    var escaped = name.replace(/([.*+?^${}()|[\]\\])/g, '\\$1');
    var pattern = new RegExp('(?:^|; )' + escaped + '=([^;]*)');
    var matches = document.cookie.match(pattern);
    return matches ? decodeURIComponent(matches[1]) : undefined;
}