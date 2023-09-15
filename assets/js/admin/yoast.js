/* global YoastSEO */

class PropertyHiveYoastPlugin {
    constructor() {
       // Ensure YoastSEO.js is present and can access the necessary features.
       if ( typeof YoastSEO === "undefined" || typeof YoastSEO.analysis === "undefined" || typeof YoastSEO.analysis.worker === "undefined" ) {
            return;
        }

        YoastSEO.app.registerPlugin( "PropertyHiveYoastPlugin", { status: "ready" } );
        
        this.registerModifications();
    }

    /**
     * Registers the addContent modification.
     *
     * @returns {void}
     */
    registerModifications() {
        const callback = this.addContent.bind( this );

        // Ensure that the additional data is being seen as a modification to the content.
        YoastSEO.app.registerModification( "content", callback, "PropertyHiveYoastPlugin", 10 );
    }

    /**
     * Adds to the content to be analyzed by the analyzer.
     *
     * @param {string} data The current data string.
     *
     * @returns {string} The data string parameter with the added content.
     */
    addContent( data ) {

        if ( jQuery('#property_rooms input[name=\'_room_name[]\']').length > 0 )
        {
            jQuery('#property_rooms input[name=\'_room_name[]\']').each(function()
            {
                if ( jQuery(this).val() != '' )
                {
                    data += " " + jQuery(this).val();
                }
            });
        }

        if ( jQuery('#property_rooms input[name=\'_room_dimensions[]\']').length > 0 )
        {
            jQuery('#property_rooms input[name=\'_room_dimensions[]\']').each(function()
            {
                if ( jQuery(this).val() != '' )
                {
                    data += " " + jQuery(this).val();
                }
            });
        }

        if ( jQuery('#property_rooms textarea[name=\'_room_description[]\']').length > 0 )
        {
            jQuery('#property_rooms textarea[name=\'_room_description[]\']').each(function()
            {
                if ( jQuery(this).val() != '' )
                {
                    data += " " + jQuery(this).val();
                }
            });
        }

        if ( jQuery('#property_rooms input[name=\'_description_name[]\']').length > 0 )
        {
            jQuery('#property_rooms input[name=\'_description_name[]\']').each(function()
            {
                if ( jQuery(this).val() != '' )
                {
                    data += " " + jQuery(this).val();
                }
            });
        }

        if ( jQuery('#property_rooms textarea[name=\'_description[]\']').length > 0 )
        {
            jQuery('#property_rooms textarea[name=\'_description[]\']').each(function()
            {
                if ( jQuery(this).val() != '' )
                {
                    data += " " + jQuery(this).val();
                }
            });
        }

        return data;
    }
}

jQuery( window ).on(
    "YoastSEO:ready",
    function() {
        jQuery(document).on( 'change', '#property_rooms input', function() 
        {
            YoastSEO.app.pluginReloaded( 'PropertyHiveYoastPlugin' );
        });
        jQuery(document).on( 'change', '#property_rooms textarea', function() 
        {
            YoastSEO.app.pluginReloaded( 'PropertyHiveYoastPlugin' );
        });
    }
);

/**
 * Adds eventlistener to load the plugin.
 */
var phYoastPlugin;
if ( typeof YoastSEO !== "undefined" && typeof YoastSEO.app !== "undefined" ) 
{
    phYoastPlugin = new PropertyHiveYoastPlugin();
} 
else 
{
  jQuery( window ).on(
    "YoastSEO:ready",
    function() {
        phYoastPlugin = new PropertyHiveYoastPlugin();
    }
  );
}