<?php

/*
*  honeycomb_update
*
*  This class will connect with and download updates from the PropertyHive website
*
*  @type	class
*  @date	07/03/2016
*
*/

class honeycomb_update
{
	var $settings;
	
	
	/*
	*  Constructor
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function __construct()
	{
		// vars
		$this->settings = array(
			'version'	=>	'',
			'remote'	=>	'http://wp-property-hive.com/theme-store/honeycomb-a4tyytf87/update-info.php',
			'basename'	=>	'honeycomb',
			'slug'		=>	'honeycomb',
		);

		// filters
		add_filter('pre_set_site_transient_update_themes', array($this, 'check_update'));
		add_filter('themes_api', array($this, 'check_info'), 10, 3);
	}
	
	
	/*
	*  get_remote
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 31/01/13
	*/
	
	function get_remote()
	{
		 // vars
        $info = false;
        
		// Get the remote info
        $request = wp_remote_post( $this->settings['remote'] );
        if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200)
        {
            $info = @unserialize($request['body']);
            $info->slug = $this->settings['slug'];
        }
        
        
        return $info;
	}
	
	
	/*
	*  check_update
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 31/01/13
	*/
	
	function check_update( $transient )
	{
	    if( empty($transient->checked) )
	    {
            return $transient;
        }

        
        // vars
        $info = $this->get_remote();
        
        // validate
        if( !$info )
        {
	        return $transient;
        }

        // compare versions
        if( version_compare($info->version, $this->get_version(), '<=') )
        {
        	return $transient;
        }

        // create new object for update
        $obj = new stdClass();
        $obj->slug = $info->slug;
        $obj->new_version = $info->version;
        $obj->url = $info->homepage;
        $obj->package = $info->download_link;

        // add to transient
        $transient->response[ $this->settings['basename'] ] = (array) $obj;
        
        return $transient;
	}
	
	
	/*
	*  check_info
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 31/01/13
	*/
	
    function check_info( $false, $action, $arg )
    {
    	// validate
    	if( !isset($arg->slug) || $arg->slug != $this->settings['slug'] )
    	{
	    	return $false;
    	}
    	
    	
    	if( $action == 'theme_information' )
    	{
	    	$false = $this->get_remote();
    	}
    	
    	        
        return $false;
    }
    
    
    /*
    *  get_version
    *
    *  This function will return the current version of this theme 
    *
    *  @type	function
    *  @date	27/08/13
    *
    *  @param	N/A
    *  @return	(string)
    */
    
    function get_version()
    {
    	// populate only once
    	if( !$this->settings['version'] )
    	{
	    	$theme_data = wp_get_theme( 'honeycomb' );
	    	if ( $theme_data->exists() )
	    	{
		    	$this->settings['version'] = $theme_data->get( 'Version' );
		    }
    	}
    	
    	// return
    	return $this->settings['version'];
	}
}


// instantiate
if( is_admin() )
{
	new honeycomb_update();
}

?>
