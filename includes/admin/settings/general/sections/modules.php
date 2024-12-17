	    
		return apply_filters( 'propertyhive_general_modules_settings', array(

array( 'title' => __( 'Disabled Modules', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'modules_options' ),

array(
    'type'    => 'html',
    'html'    => __( 'Here you can choose which modules are enabled or disabled within Property Hive. Check the modules you <strong>DO NOT</strong> wish to use from the list below', 'propertyhive' ) . ':',
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Contacts (Applicants, Owners/Landlords and Third Party Contacts)', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_contacts',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'start'
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Appraisals', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_appraisals',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'middle'
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Viewings', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_viewings',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'middle'
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Offers and Sales', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_offers_sales',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'middle'
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Tenancies', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_tenancies',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'middle'
),

array(
    'title'   => __( 'Disabled Modules', 'propertyhive' ),
    'desc'    => __( 'Enquiries', 'propertyhive' ),
    'id'      => 'propertyhive_module_disabled_enquiries',
    'type'    => 'checkbox',
    'default' => '',
    'checkboxgroup' => 'end'
),

array( 'type' => 'sectionend', 'id' => 'modules_options'),

) ); // End general module settings