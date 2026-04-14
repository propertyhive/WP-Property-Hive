<?php
/**
 * PropertyHive Frontend Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Frontend' ) ) :

/**
 * PH_Settings_Frontend.
 */
class PH_Settings_Frontend extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'frontend';
		$this->label = __( 'Frontend', 'propertyhive' );

        add_action( 'admin_init', array( $this, 'check_for_reset_search_form') );
        add_action( 'admin_init', array( $this, 'check_for_delete_search_form') );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 16 );
		add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'propertyhive_admin_field_search_forms_table', array( $this, 'search_forms_table' ) );
        add_action( 'propertyhive_admin_field_search_form_fields', array( $this, 'search_form_fields' ) );
	}

    public function check_for_reset_search_form()
    {
        if ( isset($_GET['action']) && $_GET['action'] == 'resetsearchform' && isset($_GET['id']) && $_GET['id'] != '' )
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $current_id = ( !isset( $_GET['id'] ) ) ? '' : sanitize_title( $_GET['id'] );

            $existing_search_forms = ( (isset($current_settings['search_forms'])) ? $current_settings['search_forms'] : array() );

            if ( !isset($existing_search_forms[$current_id]) )
            {
                die("Trying to reset a non-existant search form. Please go back and try again");
            }

            if ( isset($existing_search_forms[$current_id]) )
            {
                $existing_search_forms[$current_id] = array();
            }

            $current_settings['search_forms'] = $existing_search_forms;

            update_option( 'propertyhive_template_assistant', $current_settings );
        }
    }

    public function check_for_delete_search_form()
    {
        if ( isset($_GET['action']) && $_GET['action'] == 'deletesearchform' && isset($_GET['id']) && $_GET['id'] != '' && $_GET['id'] != 'default' )
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $current_id = ( !isset( $_GET['id'] ) ) ? '' : sanitize_title( $_GET['id'] );

            $existing_search_forms = ( (isset($current_settings['search_forms'])) ? $current_settings['search_forms'] : array() );

            if ( !isset($existing_search_forms[$current_id]) )
            {
                die("Trying to delete a non-existant search form. Please go back and try again");
            }

            if ( isset($existing_search_forms[$current_id]) )
            {
                unset($existing_search_forms[$current_id]);
            }

            $current_settings['search_forms'] = $existing_search_forms;

            update_option( 'propertyhive_template_assistant', $current_settings );
        }
    }

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'Search Results', 'propertyhive' ),
			'search-forms' => __( 'Search Forms', 'propertyhive' ),
			'flags' => __( 'Flags', 'propertyhive' ),
		);

		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$current_settings = get_option( 'propertyhive_template_assistant', array() );

		$settings = array(

            array( 'title' => __( 'Search Results Page Layout', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'template_assistant_search_results_settings' )

        );

        $settings[] = array(
            'title' => __( 'Default Sort Order', 'propertyhive' ),
            'id'        => 'search_result_default_order',
            'type'      => 'select',
            'default'   => ( isset($current_settings['search_result_default_order']) ? $current_settings['search_result_default_order'] : ''),
            'options'   => array(
                '' => 'Price Descending (' . __( 'default', 'propertyhive') . ')',
                'price-asc' => 'Price Ascending',
                'date' => 'Date Added',
            )
        );

        $settings[] = array(
            'title' => __( 'Properties Per Row', 'propertyhive' ),
            'id'        => 'search_result_columns',
            'type'      => 'select',
            'default'   => ( isset($current_settings['search_result_columns']) ? $current_settings['search_result_columns'] : '1'),
            'options'   => array(
                '1' => '1 (' . __( 'default', 'propertyhive') . ')',
                '2' => '2',
                '3' => '3',
                '4' => '4',
            )
        );

        $settings[] = array(
            'title' => __( 'Result Layout', 'propertyhive' ),
            'id'        => 'search_result_layout',
            'type'      => 'select',
            'default'   => ( isset($current_settings['search_result_layout']) ? $current_settings['search_result_layout'] : '1'),
            'options'   => array(
                '1' => 'List Layout 1 (default)',
                '2' => 'List Layout 2 (card)',
            )
        );

        $search_result_fields = array( 'price', 'floor_area', 'summary', 'actions' );
        if ( isset($current_settings['search_result_fields']) && is_array($current_settings['search_result_fields']) )
        {
            if ( !empty($current_settings['search_result_fields']) )
            {
                $search_result_fields = $current_settings['search_result_fields'];
            }
            else
            {
                $search_result_fields = array();
            }
        }

        $fields = array(
            array( 'id' => 'price', 'label' => 'Price / Rent' ),
            array( 'id' => 'floor_area', 'label' => 'Floor Area (commercial only)' ),
            array( 'id' => 'summary', 'label' => 'Summary Description' ),
            array( 'id' => 'actions', 'label' => 'Actions (i.e. More Details Button)' ),
            array( 'id' => 'rooms', 'label' => 'Rooms Counts' ),
            array( 'id' => 'availability', 'label' => 'Availability' ),
            array( 'id' => 'property_type', 'label' => 'Property Type' ),
            array( 'id' => 'available_date', 'label' => 'Available Date (lettings only)' ),
        );
        $custom_field_selected = false;
        if ( isset($current_settings['custom_fields']) && is_array($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            $label = '<select name="search_result_fields_custom_field"><option value="">Custom Field...</option>';
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                $label .= '<option value="custom_field' . $custom_field['field_name'] . '"';
                if ( in_array('custom_field' . $custom_field['field_name'], $search_result_fields) )
                {
                    $label .= ' selected';
                    $custom_field_selected = true;
                }
                $label .= '>' . $custom_field['field_label'] . '</option>';
            }
            $label .= '</select>';

            $fields[] = array( 'id' => 'custom_field', 'label' => $label );
        }

        // Need to sort order to match what's saved
        foreach ( $search_result_fields as $j => $search_result_field )
        {
            foreach ( $fields as $i => $field )
            {
                if ( $field['id'] == $search_result_field || ( $field['id'] == 'custom_field' && substr($search_result_field, 0, 12) == 'custom_field' ) )
                {
                    $fields[$i]['order'] = $j;
                }
            }
        }
        foreach ( $fields as $i => $field )
        {
            if ( !isset($field['order']) )
            {
                $fields[$i]['order'] = $i + 99;
            }
        }

        // order $fields by 'order' key
        $sorter = array();
        $ret = array();
        reset($fields);
        foreach ($fields as $ii => $va) 
        {
            $sorter[$ii] = $va['order'];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) 
        {
            $ret[$ii] = $fields[$ii];
        }
        $fields = $ret;

        $html = '<span class="form-field-options" id="sortable_options">';
        foreach ( $fields as $field )
        {
            $html .= '<span style="display:block; padding:3px 0;">
                <i class="fa fa-reorder" style="cursor:pointer; opacity:0.3"></i> &nbsp;
                <input type="checkbox" name="search_result_fields[]" value="' . $field['id'] . '"';
            if ( in_array($field['id'], $search_result_fields) || ( $field['id'] == 'custom_field' && $custom_field_selected ) )
            {
                $html .= ' checked';
            }
            $html .= '>
                ' . $field['label'] . '
            </span>';
        }
        $html .= '</span>

        <script>
            jQuery(document).ready(function($)
            {
                $( "#sortable_options" )
                .sortable({
                    axis: "y",
                    handle: "i",
                    stop: function( event, ui ) 
                    {
                        // IE doesn\'t register the blur when sorting
                        // so trigger focusout handlers to remove .ui-state-focus
                        //ui.item.children( "h3" ).triggerHandler( "focusout" );
             
                        // Refresh accordion to handle new order
                        //$( this ).accordion( "refresh" );
                    },
                    update: function( event, ui ) 
                    {
                        // Update hidden fields
                        var fields_order = $(this).sortable(\'toArray\');
                        
                        //$(\'#active_fields_order\').val( fields_order.join("|") );
                    }
                });
            });
        </script>';

        $settings[] = array(
            'title' => __( 'Fields Shown', 'propertyhive' ),
            'type'      => 'html',
            'html'      => $html
        );

        if ( get_option('propertyhive_images_stored_as', '') != 'urls' )
        {
            $image_sizes = get_intermediate_image_sizes();
            $image_size_options = array();
            foreach ( $image_sizes as $image_size )
            {
                $image_size_options[$image_size] = $image_size;
            }

            $settings[] = array(
                'title' => __( 'Image Size Used', 'propertyhive' ),
                'id'        => 'search_result_image_size',
                'type'      => 'select',
                'default'   => ( isset($current_settings['search_result_image_size']) ? $current_settings['search_result_image_size'] : 'medium'),
                'options'   => $image_size_options
            );
        }

        $columns_1_css = file_get_contents(PH()->plugin_path() . '/assets/css/search-results-layouts/columns-1.css');
        $columns_2_css = file_get_contents(PH()->plugin_path() . '/assets/css/search-results-layouts/columns-2.css');
        $columns_3_css = file_get_contents(PH()->plugin_path() . '/assets/css/search-results-layouts/columns-3.css');
        $columns_4_css = file_get_contents(PH()->plugin_path() . '/assets/css/search-results-layouts/columns-4.css');
        $layout_1_css = '';
        $layout_2_css = file_get_contents(PH()->plugin_path() . '/assets/css/search-results-layouts/content-property-2.css');

        $settings[] = array(
            'title' => __( 'Customise CSS', 'propertyhive' ),
            'id'        => 'search_result_css',
            'type'      => 'textarea',
            'default'   => ( isset($current_settings['search_result_css']) ? $current_settings['search_result_css'] : $columns_1_css . "\n\n" . $layout_1_css ),
            'css'       => 'height:200px;width:100%;',
        );

        if ( isset($current_settings['search_result_css']) && trim($current_settings['search_result_css']) != '' )
        {
            $settings[] = array(
                'type'      => 'html',
                'html'      => '<div id="change_warning" style="display:none; color:#900">
                    By changing the options above the CSS been regenerated. Please note that this will overwrite any customisations you\'ve previously made to the CSS.
                </div>'
            );
        }

        $settings[] = array(
            'title' => __( 'Apply CSS To All Pages', 'propertyhive' ),
            'id'        => 'search_result_css_all_pages',
            'type'      => 'checkbox',
            'default'   => isset($current_settings['search_result_css_all_pages']) && $current_settings['search_result_css_all_pages'] == 'yes' ? 'yes' : '',
        );

        $settings[] = array(
            'type'      => 'html',
            'html'      => '<script>

                jQuery(document).ready(function()
                {
                    jQuery(\'#search_result_columns\').change(function()
                    {
                        generate_search_results_css();
                    });
                    jQuery(\'#search_result_layout\').change(function()
                    {
                        generate_search_results_css();
                    });
                });

                function generate_search_results_css()
                {
                    jQuery(\'#search_result_css\').val(\'\');

                    jQuery(\'#change_warning\').slideDown();

                    var columns_css = \'\';
                    var layout_css = \'\';
                    switch ( jQuery(\'#search_result_columns\').val() )
                    {
                        case \'1\':
                        {
                            columns_css = "' . str_replace(array("\r\n", "\n"), '\n', $columns_1_css) . '";
                            break;
                        }
                        case \'2\':
                        {
                            columns_css = "' . str_replace(array("\r\n", "\n"), '\n', $columns_2_css) . '";
                            break;
                        }
                        case \'3\':
                        {
                            columns_css = "' . str_replace(array("\r\n", "\n"), '\n', $columns_3_css) . '";
                            break;
                        }
                        case \'4\':
                        {
                            columns_css = "' . str_replace(array("\r\n", "\n"), '\n', $columns_4_css) . '";
                            break;
                        }
                    }

                    switch ( jQuery(\'#search_result_layout\').val() )
                    {
                        case \'1\':
                        {
                            layout_css = "' . str_replace(array("\r\n", "\n"), '\n', $layout_1_css) . '";
                            break;
                        }
                        case \'2\':
                        {
                            layout_css = "' . str_replace(array("\r\n", "\n"), '\n', $layout_2_css) . '";
                            break;
                        }
                    }

                    jQuery(\'#search_result_css\').val( columns_css + "\n\n" + layout_css );
                }

            </script>'
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'template_assistant_search_results_settings');

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
	}

	public function get_search_forms_settings()
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        $settings = array(

            array( 'title' => __( 'Search Forms', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'template_assistant_search_forms_settings' )

        );

        $settings[] = array(
            'type' => 'search_forms_table',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'template_assistant_search_forms_settings');

        return $settings;
    }

    public function get_search_form_settings()
    {
        global $current_section;

        wp_enqueue_script( 'jquery-ui-accordion' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( !isset($current_settings['search_forms']) )
        {
            $current_settings['search_forms'] = array();
        }
        if ( !isset($current_settings['search_forms']['default']) )
        {
            $current_settings['search_forms']['default'] = array();
        }

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $search_form_details = array();

        if ($current_id != '')
        {
            $search_forms = $current_settings['search_forms'];

            if (isset($search_forms[$current_id]))
            {
                $search_form_details = $search_forms[$current_id];
            }
            else
            {
                die('Trying to edit a search form which does not exist. Please go back and try again.');
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addsearchform' ? 'Add Search Form' : 'Edit Search Form' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'searchforms' ),

        );

        $custom_attributes = array();
        if ($current_id == 'default' || $current_section == 'editsearchform')
        {
            $custom_attributes['disabled'] = 'disabled';
        }

        $settings[] = array(
            'title' => __( 'ID', 'propertyhive' ),
            'id'        => 'form_id',
            'default'   => ( (isset($current_id)) ? $current_id : ''),
            'type'      => 'text',
            'desc_tip'  =>  false,
            'custom_attributes' => $custom_attributes
        );

        $settings[] = array(
            'type' => 'search_form_fields',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'searchforms');

        return $settings;
    }

    /**
     * Output list of search forms
     *
     * @access public
     * @return void
     */
    public function search_forms_table() {
        global $wpdb, $post;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=frontend&section=addsearchform' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Search Form', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php esc_html_e( 'Search Forms', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_portals widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="id"><?php esc_html_e( 'ID', 'propertyhive' ); ?></th>
                            <th class="shortcode"><?php esc_html_e( 'Shortcode', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_settings = get_option( 'propertyhive_template_assistant', array() );
                            $search_forms = array();
                            if ($current_settings !== FALSE)
                            {
                                if (isset($current_settings['search_forms']))
                                {
                                    $search_forms = $current_settings['search_forms'];
                                }
                            }

                            if ( !isset($search_forms['default']) )
                            {
                                $search_forms['default'] = array();
                            }

                            if (!empty($search_forms))
                            {
                                foreach ($search_forms as $id => $search_form)
                                {
                                    echo '<tr>';
                                        echo '<td class="id">' . $id . '</td>';
                                        echo '<td class="shortcode"><pre style="background:#EEE; padding:5px; display:inline">[property_search_form id="' . $id . '"]</pre></td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=frontend&section=editsearchform&id=' . $id )) . '">' . esc_html(__( 'Edit Fields', 'propertyhive' )) . '</a>
                                            <a class="button" href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=frontend&section=search-forms&action=resetsearchform&id=' . $id )) . '">' . esc_html(__( 'Reset To Default Fields', 'propertyhive' )) . '</a>
                                            ' .  ( ( $id != 'default' ) ? '<a class="button" href="' . esc_url(admin_url( 'admin.php?page=ph-settings&tab=frontend&section=search-forms&action=deletesearchform&id=' . $id )) . '" onclick="var confirmBox = confirm(\'Are you sure you wish to delete this search form?\'); return confirmBox;">' . esc_html(__( 'Delete', 'propertyhive' )) . '</a>' : '' ) . '
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="3">' . esc_html(__( 'No search forms exist', 'propertyhive' )) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ph-settings&tab=frontend&section=addsearchform' )); ?>" class="button alignright"><?php echo esc_html(__( 'Add New Search Form', 'propertyhive' )); ?></a>
            </td>
        </tr>
        <?php
    }

    private function output_search_form_field( $id, $field )
    {
        echo '
        <div class="group" id="' . $id . '">
            <h3>' . trim( $id, '_' ) . '</h3>
            <div>';
        if ( $id == 'department' )
        {
            echo '<p><label for="type_'.$id.'">Type:</label> <select name="type[' . $id . ']" id="type_'.$id.'">
                <option value="radio"' . ( ( !isset($field['type']) || ( isset($field['type']) && $field['type'] == 'radio' ) ) ? ' selected' : '' ) . '>Radio Buttons</option>
                <option value="select"' . ( ( isset($field['type']) && $field['type'] == 'select' ) ? ' selected' : '' ) . '>Dropdown</option>
                ' . ( ( isset($field['type']) && $field['type'] != 'select' && $field['type'] != 'radio' ) ? '<option value="' . $field['type'] . '" selected>' . $field['type'] . '</option>' : '' ) . '
            </select></p>';
        }
        else
        {
            echo '<input type="hidden" name="type[' . $id . ']" id="type_'.$id.'" value="' . ( ( isset($field['type']) ) ? $field['type'] : '' ) . '">';
        }

        echo  ' <p><label for="show_label_'.$id.'">Show Label:</label> <input type="checkbox" name="show_label[' . $id . ']" id="show_label_'.$id.'" value="1"' . ( ( isset($field['show_label']) && $field['show_label'] === true ) ? ' checked' : '' ) . '></p>
                
                <p><label for="label_'.$id.'">Label:</label> <input type="text" name="label[' . $id . ']" id="label_'.$id.'" value="' . ( ( isset($field['label']) ) ? $field['label'] : '' ) . '"></p>
                
                <p><label for="before_'.$id.'">Before:</label> <input type="text" name="before[' . $id . ']" id="before_'.$id.'" value="' . ( ( isset($field['before']) ) ? htmlentities($field['before']) : '' ) . '"></p>
                
                <p><label for="after_'.$id.'">After:</label> <input type="text" name="after[' . $id . ']" id="after_'.$id.'" value="' . ( ( isset($field['after']) ) ? htmlentities($field['after']) : '' ) . '"></p>';

        if ( isset($field['type']) && in_array($field['type'], array('text', 'email', 'date', 'number', 'password')) )
        {
            echo '
            <p><label for="placeholder_'.$id.'">Placeholder:</label> <input type="text" name="placeholder[' . $id . ']" id="placeholder_'.$id.'" value="' . ( ( isset($field['placeholder']) ) ? htmlentities($field['placeholder']) : '' ) . '"></p>
            ';
        }

        if ( isset($field['type']) && in_array($field['type'], array('slider')) )
        {
            echo '
            <p><label for="min_'.$id.'">Min:</label> <input type="number" name="min[' . $id . ']" id="min_'.$id.'" value="' . ( ( isset($field['min']) ) ? htmlentities($field['min']) : '0' ) . '"></p>
            ';

            echo '
            <p><label for="max_'.$id.'">Max:</label> <input type="number" name="max[' . $id . ']" id="max_'.$id.'" value="' . ( ( isset($field['max']) ) ? htmlentities($field['max']) : '' ) . '"></p>
            ';

            echo '
            <p><label for="step_'.$id.'">Step:</label> <input type="number" name="step[' . $id . ']" id="step_'.$id.'" value="' . ( ( isset($field['step']) ) ? htmlentities($field['step']) : '1' ) . '"></p>
            ';
        }

        if ( isset($field['type']) && in_array($field['type'], array('office')) )
        {
            echo '
            <p><label for="blank_option_'.$id.'">Blank Option:</label> <input type="text" name="blank_option[' . $id . ']" id="blank_option_'.$id.'" value="' . ( ( isset($field['blank_option']) ) ? htmlentities($field['blank_option']) : __( 'No Preference', 'propertyhive' ) ) . '"></p>
            ';
        }

        if ( taxonomy_exists($id) || ( isset($field['custom_field']) && $field['custom_field'] === true && $field['type'] == 'select' ) )
        {
            echo '
            <p><label for="blank_option_'.$id.'">Blank Option:</label> <input type="text" name="blank_option[' . $id . ']" id="blank_option_'.$id.'" value="' . ( ( isset($field['blank_option']) ) ? htmlentities($field['blank_option']) : __( 'No Preference', 'propertyhive' ) ) . '"></p>
            ';

            if ( taxonomy_exists($id) && in_array( $id, apply_filters( 'propertyhive_template_assistant_multi_level_taxonomy_fields', array('property_type', 'commercial_property_type', 'location') ) ) )
            {
                echo '
                <p><label for="parent_terms_only_'.$id.'">Top-Level Terms Only:</label> <input type="checkbox" name="parent_terms_only[' . $id . ']" id="parent_terms_only_'.$id.'" value="yes"' . ( ( isset($field['parent_terms_only']) && $field['parent_terms_only'] === true ) ? ' checked' : '' ) . '></p>
                ';

                echo '
                <p><label for="hide_empty_'.$id.'">Hide Terms With No Properties Assigned:</label> <input type="checkbox" name="hide_empty[' . $id . ']" id="hide_empty_'.$id.'" value="yes"' . ( ( isset($field['hide_empty']) && $field['hide_empty'] === true ) ? ' checked' : '' ) . '></p>
                ';
            }

            if ( taxonomy_exists($id) && in_array( $id, apply_filters( 'propertyhive_template_assistant_dynamic_population_taxonomy_fields', array('location') ) ) )
            {
                echo '
                <p><label for="dynamic_population_'.$id.'">Dynamically Populate Cascading Dropdowns:</label> <input type="checkbox" name="dynamic_population[' . $id . ']" id="dynamic_population_'.$id.'" value="yes"' . ( ( isset($field['dynamic_population']) && $field['dynamic_population'] === true ) ? ' checked' : '' ) . '></p>
                ';
            }

            echo '
            <p><label for="multiselect_'.$id.'">Multi-Select:</label> <input type="checkbox" name="multiselect[' . $id . ']" id="multiselect_'.$id.'" value="yes"' . ( ( isset($field['multiselect']) && $field['multiselect'] === true ) ? ' checked' : '' ) . '></p>
            ';
        }

        if ( $id == 'office' )
        {
            echo '
            <p><label for="multiselect_'.$id.'">Multi-Select:</label> <input type="checkbox" name="multiselect[' . $id . ']" id="multiselect_'.$id.'" value="yes"' . ( ( isset($field['multiselect']) && $field['multiselect'] === true ) ? ' checked' : '' ) . '></p>
            ';
        }

        if ( isset($field['options']) && !taxonomy_exists($id) && ( !isset($field['custom_field']) || ( isset($field['custom_field']) && $field['custom_field'] === false ) ) )
        {
            echo '<p><label for="">Options: ';

            echo '<a href="" class="add-search-form-field-option" id="add_search_form_field_option_' . $id . '">Add Option</a>';

            echo '</label><br>';

            echo '<span class="form-field-options" id="sortable_options_' . $id . '">';
            $i = 0;
            foreach ( $field['options'] as $key => $value )
            {
                echo '<span style="display:block"><i class="fa fa-reorder" style="cursor:pointer; opacity:0.3"></i> ';
                echo '<input type="text" name="option_keys[' . $id . '][]" value="' . $key . '">';
                echo '<input type="text" name="options_values[' . $id . '][]" value="' . $value . '">';
                echo '</span>';

                ++$i;
            }
            echo '</span>';

            echo '</p>';
?>
<script>
            jQuery(document).ready(function($)
            {
                $( "#sortable_options_<?php echo $id; ?>" )
                .sortable({
                    axis: "y",
                    handle: "i",
                    stop: function( event, ui ) 
                    {
                        // IE doesn't register the blur when sorting
                        // so trigger focusout handlers to remove .ui-state-focus
                        //ui.item.children( "h3" ).triggerHandler( "focusout" );
             
                        // Refresh accordion to handle new order
                        //$( this ).accordion( "refresh" );
                    },
                    update: function( event, ui ) 
                    {
                        // Update hidden fields
                        var fields_order = $(this).sortable('toArray');
                        
                        //$('#active_fields_order').val( fields_order.join("|") );
                    }
                });
            });
        </script>
<?php
        }

        echo '</div>
        </div>';
    }

    /**
     * Output list of search form active/inactive fields
     *
     * @access public
     * @return void
     */
    public function search_form_fields() {
        global $wpdb, $post;

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( !isset($current_settings['search_forms']) )
        {
            $current_settings['search_forms'] = array();
        }
        if ( !isset($current_settings['search_forms']['default']) )
        {
            $current_settings['search_forms']['default'] = array();
        }

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $search_form_details = array();

        if ($current_id != '')
        {
            $search_forms = $current_settings['search_forms'];

            if (isset($search_forms[$current_id]))
            {
                $search_form_details = $search_forms[$current_id];
            }
            else
            {
                die('Trying to edit search form which does not exist. Please go back and try again.');
            }
        }

        $all_fields = ph_get_search_form_fields();
        $all_fields['address_keyword'] = array(
            'type' => 'text',
            'label' => __( 'Location', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-address_keyword">'
        );
        if ( class_exists('PH_Radial_Search') )
        {
            $all_fields['radius'] = array(
                'type' => 'select',
                'label' => __( 'Radius', 'propertyhive' ),
                'show_label' => true,
                'before' => '<div class="control control-radius">',
                'options' => array(
                    '' => __( 'This Area Only', 'propertyhive' ),
                    '1' => __( 'Within 1 Mile', 'propertyhive' ),
                    '2' => __( 'Within 2 Miles', 'propertyhive' ),
                    '3' => __( 'Within 3 Miles', 'propertyhive' ),
                    '5' => __( 'Within 5 Miles', 'propertyhive' ),
                    '10' => __( 'Within 10 Miles', 'propertyhive' ),
                )
            );
        }
        $all_fields['location'] = array(
            'type' => 'location',
            'label' => __( 'Location', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-location">'
        );
        $all_fields['parking'] = array(
            'type' => 'parking',
            'label' => __( 'Parking', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-parking residential-only">'
        );
        $all_fields['outside_space'] = array(
            'type' => 'outside_space',
            'label' => __( 'Outside Space', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-outside_space residential-only">'
        );
        $all_fields['availability'] = array(
            'type' => 'availability',
            'label' => __( 'Status', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-availability">'
        );
        $all_fields['marketing_flag'] = array(
            'type' => 'marketing_flag',
            'label' => __( 'Marketing Flag', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-marketing_flag">'
        );
        $all_fields['tenure'] = array(
            'type' => 'tenure',
            'label' => __( 'Tenure', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-tenure residential-only">'
        );
        $all_fields['commercial_tenure'] = array(
            'type' => 'commercial_tenure',
            'label' => __( 'Commercial Tenure', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_tenure commercial-only">'
        );
        $all_fields['commercial_for_sale_to_rent'] = array(
            'type' => 'select',
            'label' => __( 'For Sale / To Rent', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_for_sale_to_rent commercial-only">',
            'options' => array(
                '' => __( 'No Preference', 'propertyhive' ),
                'for_sale' => __( 'For Sale', 'propertyhive' ),
                'to_rent' => __( 'To Rent', 'propertyhive' ),
            )
        );

        $prices = array(
            '' => __( 'No preference', 'propertyhive' ),
            '100000' => '£100,000',
            '200000' => '£200,000',
            '300000' => '£300,000',
            '400000' => '£400,000',
            '500000' => '£500,000',
            '750000' => '£750,000',
        );
        $all_fields['commercial_minimum_price'] = array(
            'type' => 'select',
            'label' => __( 'Minimum Price', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_minimum_price commercial-sales-only">',
            'options' => $prices
        );
        $all_fields['commercial_maximum_price'] = array(
            'type' => 'select',
            'label' => __( 'Maximum Price', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_maximum_price commercial-sales-only">',
            'options' => $prices
        );

        $prices = array(
            '' => __( 'No preference', 'propertyhive' ),
            '500' => '£500',
            '750' => '£750',
            '1000' => '£1,000',
            '1500' => '£1,500',
            '2000' => '£2,000',
            '3000' => '£3,000',
        );
        $all_fields['commercial_minimum_rent'] = array(
            'type' => 'select',
            'label' => __( 'Minimum Rent', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_minimum_rent commercial-lettings-only">',
            'options' => $prices
        );
        $all_fields['commercial_maximum_rent'] = array(
            'type' => 'select',
            'label' => __( 'Maximum Rent', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-commercial_maximum_rent commercial-lettings-only">',
            'options' => $prices
        );

        $all_fields['sale_by'] = array(
            'type' => 'sale_by',
            'label' => __( 'Sale By', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-sale_by">'
        );
        $all_fields['furnished'] = array(
            'type' => 'furnished',
            'label' => __( 'Furnished', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-furnished lettings-only">'
        );

        $price_ranges = array(
            '' => __( 'No preference', 'propertyhive' ),
            '100000-200000' => '£100,000 - £200,000',
            '200000-300000' => '£200,000 - £300,000',
            '300000-400000' => '£300,000 - £400,000',
            '400000-500000' => '£400,000 - £500,000',
            '500000-750000' => '£500,000 - £750,000',
            '750000-1000000' => '£750,000 - £1,000,000',
        );

        $all_fields['price_range'] = array(
            'type' => 'select',
            'label' => __( 'Price', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-price-range sales-only">',
            'options' => $price_ranges
        );

        $all_fields['price_slider'] = array(
            'type' => 'slider',
            'label' => __( 'Price', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-price-slider sales-only">',
            'min' => '0',
            'max' => '1000000',
            'step' => '10000',
        );

        $rent_ranges = array(
            '' => __( 'No preference', 'propertyhive' ),
            '100-200' => '£100 - £200 PCM',
            '200-300' => '£200 - £300 PCM',
            '300-400' => '£300 - £400 PCM',
            '400-500' => '£400 - £500 PCM',
            '500-750' => '£500 - £750 PCM',
            '750-1000' => '£750 - £1,000 PCM',
        );

        $all_fields['rent_range'] = array(
            'type' => 'select',
            'label' => __( 'Rent', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-rent-range lettings-only">',
            'options' => $rent_ranges
        );

        $all_fields['rent_slider'] = array(
            'type' => 'slider',
            'label' => __( 'Rent', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-rent-slider lettings-only">',
            'min' => '0',
            'max' => '1000',
            'step' => '100',
        );

        $bedrooms = array(
            '' => __( 'No preference', 'propertyhive' ),
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        );

        $all_fields['bedrooms'] = array(
            'type' => 'select',
            'label' => __( 'Bedrooms', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-bedrooms residential-only">',
            'options' => $bedrooms
        );

        $all_fields['maximum_bedrooms'] = array(
            'type' => 'select',
            'label' => __( 'Max Beds', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-maximum_bedrooms residential-only">',
            'options' => $bedrooms
        );

        $bathrooms = array(
            '' => __( 'No preference', 'propertyhive' ),
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        );

        $all_fields['minimum_bathrooms'] = array(
            'type' => 'select',
            'label' => __( 'Min Bathrooms', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-minimum_bathrooms residential-only">',
            'options' => $bathrooms
        );
        $all_fields['maximum_bathrooms'] = array(
            'type' => 'select',
            'label' => __( 'Max Bathrooms', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-maximum_bathrooms residential-only">',
            'options' => $bathrooms
        );

        $reception_rooms = array(
            '' => __( 'No preference', 'propertyhive' ),
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        );

        $all_fields['minimum_reception_rooms'] = array(
            'type' => 'select',
            'label' => __( 'Min Receptions', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-minimum_reception_rooms residential-only">',
            'options' => $reception_rooms
        );
        $all_fields['maximum_reception_rooms'] = array(
            'type' => 'select',
            'label' => __( 'Max Receptions', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-maximum_reception_rooms residential-only">',
            'options' => $reception_rooms
        );

        $all_fields['bedrooms_slider'] = array(
            'type' => 'slider',
            'label' => __( 'Bedrooms', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-bedrooms-slider residential-only">',
            'min' => '0',
            'max' => '10',
        );
        $all_fields['available_date_from'] = array(
            'type' => 'date',
            'label' => __( 'Available From', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-available_date_from lettings-only">'
        );

        $all_fields['office'] = array(
            'type' => 'office',
            'label' => __( 'Office', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-office">'
        );

        $all_fields['keyword'] = array(
            'type' => 'text',
            'label' => __( 'Keyword', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-keyword">'
        );

        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $all_fields['property_feature'] = array(
                'type' => 'property_feature',
                'label' => __( 'Property Features', 'propertyhive' ),
                'show_label' => true,
                'before' => '<div class="control control-property_feature">',
                'multiselect' => true,
            );
        }

        $all_fields = apply_filters( 'propertyhive_search_form_all_fields', $all_fields );

        $currencies = array(
            '' => '',
            'GBP' => 'GBP',
            'EUR' => 'EUR',
            'USD' => 'USD',
        );

        $all_fields['currency'] = array(
            'type' => 'select',
            'label' => __( 'Currency', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-currency">',
            'options' => $currencies
        );

        $date_added_days = array(
            '' => __( 'No preference', 'propertyhive' ),
            '1' => 'Last 24 Hours',
            '3' => 'Last 3 Days',
            '7' => 'Last 7 Days',
            '14' => 'Last 14 Days',
        );

        $all_fields['date_added'] = array(
            'type' => 'select',
            'show_label' => true,
            'label' => __( 'Date Added', 'propertyhive' ),
            'options' => $date_added_days
        );

        $form_controls = ph_get_search_form_fields();
        $active_fields = apply_filters( 'propertyhive_search_form_fields_' . $current_id, $form_controls );

        // Add any additional fields
        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $id => $custom_field )
            {
                $field_type = 'text';
                if ( isset($custom_field['field_type']) )
                {
                    switch ( $custom_field['field_type'] )
                    {
                        case 'select':
                        case 'multiselect':
                        {
                            $field_type = 'select';
                            break;
                        }
                        case 'checkbox':
                        {
                            $field_type = 'checkbox';
                            break;
                        }
                    }
                    
                }

                $all_fields[$custom_field['field_name']] = array(
                    'type' => $field_type,
                    'label' => $custom_field['field_label'],
                    'show_label' => true,
                    'before' => '<div class="control control-' . trim( $custom_field['field_name'], '_' ) . '">',
                    'custom_field' => true,
                );

                if ( isset($active_fields[$custom_field['field_name']]) )
                {
                    $active_fields[$custom_field['field_name']]['custom_field'] = true;
                }
            }
        }

        $inactive_fields = array();
        foreach ( $all_fields as $id => $field )
        {
            if ( !isset($active_fields[$id]) )
            {
                if ( isset($search_form_details['inactive_fields'][$id]) && !empty($search_form_details['inactive_fields'][$id]) )
                {
                    $field = array_merge($field, $search_form_details['inactive_fields'][$id]);
                }
                $inactive_fields[$id] = $field;
            }
        }
?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php esc_html_e( 'Active Fields', 'propertyhive' ) ?></th>
            <td class="forminp">
                <div id="sortable1" class="connectedSortable" style="min-height:30px;">
                <?php
                    foreach ( $active_fields as $id => $field )
                    {
                        if ( isset( $field['type'] ) && $field['type'] == 'hidden' ) { continue; }

                        $this->output_search_form_field( $id, $field );
                    }
                ?>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php esc_html_e( 'Inactive Fields', 'propertyhive' ) ?></th>
            <td class="forminp">
                <div id="sortable2" class="connectedSortable" style="min-height:30px;">
                <?php
                    if ( !class_exists('PH_Radial_Search') )
                    {
                        // Show radial search with link to buy add on
                        echo '<div class="group" id="radius-placeholder">
                            <h3>radius</h3>
                            <div class="">This field requires the <a href="https://wp-property-hive.com/addons/radial-search/" target="_blank">Radial Search add on</a></div>
                        </div>';
                    }
                    foreach ( $inactive_fields as $id => $field )
                    {
                        if ( isset( $field['type'] ) && $field['type'] == 'hidden' ) { continue; }

                        $this->output_search_form_field( $id, $field );
                    }
                ?>
                </div>
            </td>
        </tr>

        <input type="hidden" name="active_fields_order" id="active_fields_order" value="<?php
            $field_ids = array();
            foreach ( $active_fields as $id => $field )
            {
                $field_ids[] = $id;
            }
            echo implode("|", $field_ids);
        ?>">
        <input type="hidden" name="inactive_fields_order" id="inactive_fields_order" value="<?php
            $field_ids = array();
            foreach ( $inactive_fields as $id => $field )
            {
                $field_ids[] = $id;
            }
            echo implode("|", $field_ids);
        ?>">

        <script>
            jQuery(document).ready(function($)
            {
                $( "#sortable1" )
                .accordion({
                    collapsible: true,
                    active: false,
                    header: "> div > h3",
                    heightStyle: "content"
                })
                .sortable({
                    axis: "y",
                    handle: "h3",
                    connectWith: ".connectedSortable",
                    stop: function( event, ui ) 
                    {
                        // IE doesn't register the blur when sorting
                        // so trigger focusout handlers to remove .ui-state-focus
                        ui.item.children( "h3" ).triggerHandler( "focusout" );
             
                        // Refresh accordion to handle new order
                        $( this ).accordion( "refresh" );
                    },
                    update: function( event, ui ) 
                    {
                        // Update hidden fields
                        var fields_order = $(this).sortable('toArray');

                        fields_order = jQuery.grep(fields_order, function(value) {
                            return value != 'radius-placeholder';
                        });
                        
                        $('#active_fields_order').val( fields_order.join("|") );
                    }
                });

                $( "#sortable2" )
                .accordion({
                    collapsible: true,
                    active: false,
                    header: "> div > h3",
                    heightStyle: "content"
                })
                .sortable({
                    axis: "y",
                    handle: "h3",
                    connectWith: ".connectedSortable",
                    stop: function( event, ui ) 
                    {
                        // IE doesn't register the blur when sorting
                        // so trigger focusout handlers to remove .ui-state-focus
                        ui.item.children( "h3" ).triggerHandler( "focusout" );
             
                        // Refresh accordion to handle new order
                        $( this ).accordion( "refresh" );
                    },
                    update: function( event, ui ) 
                    {
                        // Update hidden fields
                        var fields_order = $(this).sortable('toArray');

                        fields_order = jQuery.grep(fields_order, function(value) {
                            return value != 'radius-placeholder';
                        });
                        
                        $('#inactive_fields_order').val( fields_order.join("|") );
                    }
                });

                // Handle add/remove options
                $('body').on('click', '.add-search-form-field-option', function(e)
                {
                    e.preventDefault();

                    var this_id = $(this).attr('id').replace("add_search_form_field_option_", "");

                    var clone = $('#sortable_options_' + this_id).children('span').eq(0).clone();
                    clone.find('input').val('');

                    clone.appendTo( $('#sortable_options_' + this_id) );

                    add_remove_option_links();
                });

                $('body').on('click', '.remove-search-form-field-option', function(e)
                {
                    e.preventDefault();
                    
                    $(this).parent().remove();

                    add_remove_option_links();
                });

                add_remove_option_links();
            });

            function add_remove_option_links()
            {
                jQuery('.connectedSortable .group a.remove-search-form-field-option').remove();

                jQuery('.connectedSortable .group').each(function()
                {
                    if ( jQuery(this).find('.add-search-form-field-option').length > 0 )
                    {   
                        console.log(jQuery(this).find('.form-field-options span').length);
                        if ( jQuery(this).find('.form-field-options span').length > 1 )
                        {
                            jQuery(this).find('.form-field-options span').append(' <a href="" class="remove-search-form-field-option">X</a>');
                        }
                    }
                });
            }
        </script>
<?php
    }

    /**
     * Get flag settings
     *
     * @return array Array of settings
     */
    public function get_flags_settings() {

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        $settings = array(

            array( 'title' => __( 'Flags', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'template_assistant_flags_settings' )

        );

        $settings[] = array(
            'title' => __( 'Show Flags On Search Results', 'propertyhive' ),
            'id'        => 'flags_active',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['flags_active']) && $current_settings['flags_active'] == '1' ) ? 'yes' : ''),
            'desc'      => 'If checked flags will be shown in search results over the property thumbnail containing the property availability or marketing flag if one selected'
        );

        $settings[] = array(
            'title' => __( 'Show Flags On Property Details', 'propertyhive' ),
            'id'        => 'flags_active_single',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['flags_active_single']) && $current_settings['flags_active_single'] == '1' ) ? 'yes' : ''),
            'desc'      => 'If checked flags will be shown over the main image slideshow on the full property details page'
        );

        $settings[] = array(
            'title' => __( 'Position Over Thumbnail', 'propertyhive' ),
            'id'        => 'flag_position',
            'type'      => 'select',
            'default'   => ( isset($current_settings['flag_position']) ? $current_settings['flag_position'] : ''),
            'options'   => array(
                'top:0; left:0;' => 'Top Left',
                'top:0; right:0;' => 'Top Right',
                'bottom:0; left:0;' => 'Bottom Left',
                'bottom:0; right:0;' => 'Bottom Right',
                'top:0; left:0; right:0;' => 'Across Top',
                'bottom:0; left:0; right:0;' => 'Across Bottom',
            )
        );

        $settings[] = array(
            'title' => __( 'Background Colour', 'propertyhive' ),
            'id'        => 'flag_bg_color',
            'type'      => 'color',
            'default'   => ( isset($current_settings['flag_bg_color']) ? $current_settings['flag_bg_color'] : '#000'),
        );

        $settings[] = array(
            'title' => __( 'Text Colour', 'propertyhive' ),
            'id'        => 'flag_text_color',
            'type'      => 'color',
            'default'   => ( isset($current_settings['flag_text_color']) ? $current_settings['flag_text_color'] : '#FFF'),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'template_assistant_flags_settings');

        return $settings;
    }

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section, $hide_save_button;

		if ( $current_section ) 
        {
        	switch ($current_section)
            {
            	case "search-forms": { $hide_save_button = true; $settings = $this->get_search_forms_settings(); break; }
                case "addsearchform": { $settings = $this->get_search_form_settings(); break; }
                case "editsearchform": { $settings = $this->get_search_form_settings(); break; }
            	case "flags": { $settings = $this->get_flags_settings();  break; }
                default: { die("Unknown setting section"); }
            }
        }
        else
        {
        	$settings = $this->get_settings(); 
        }

		PH_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() 
	{
		global $current_section;

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

		if ( $current_section != '' ) 
        {
        	switch ($current_section)
        	{
        		case "flags": 
                {
                    $propertyhive_template_assistant = array(
                        'flags_active' => ( ( isset($_POST['flags_active']) ) ? sanitize_text_field($_POST['flags_active']) : '' ),
                        'flags_active_single' => ( ( isset($_POST['flags_active_single']) ) ? sanitize_text_field($_POST['flags_active_single']) : '' ),
                        'flag_position' => sanitize_text_field($_POST['flag_position']),
                        'flag_bg_color' => sanitize_text_field($_POST['flag_bg_color']),
                        'flag_text_color' => sanitize_text_field($_POST['flag_text_color']),
                    );

                    $propertyhive_template_assistant = array_merge($current_settings, $propertyhive_template_assistant);

                    update_option( 'propertyhive_template_assistant', $propertyhive_template_assistant );
                    break; 
                }
        		case "addsearchform": 
                case "editsearchform": 
                {
                    $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                    $existing_search_forms = ( (isset($current_settings['search_forms'])) ? $current_settings['search_forms'] : array() );

                    if ( $current_section == 'editsearchform' && $current_id != 'default' && !isset($existing_search_forms[$current_id]) )
                    {
                        die("Trying to edit a non-existant search form. Please go back and try again");
                    }

                    if ( isset($existing_search_forms[$current_id]) )
                    {
                        unset($existing_search_forms[$current_id]);
                    }

                    $current_id = ( ( isset($_POST['form_id']) && $_POST['form_id'] != '' ) ? str_replace("-", "_", sanitize_title($_POST['form_id'])) : $current_id );
                    if ($current_section == 'addsearchform' && trim($current_id) == '' )
                    {
                        $current_id = 'custom';
                    }

                    $active_fields = array();
                    $inactive_fields = array();

                    if ( isset($_POST['active_fields_order']) && $_POST['active_fields_order'] != '' )
                    {
                        $field_ids = explode("|", sanitize_text_field($_POST['active_fields_order']));
                        if ( !empty($field_ids) )
                        {
                            foreach ( $field_ids as $field_id )
                            {
                                $active_fields[$field_id] = array(
                                    'show_label' => ( ( isset($_POST['show_label'][$field_id]) && $_POST['show_label'][$field_id] == '1' ) ? true : false ),
                                    'label' => ( isset($_POST['label'][$field_id]) ? stripslashes($_POST['label'][$field_id]) : '' ),
                                );

                                if ( isset($_POST['type'][$field_id]) && $_POST['type'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['type'] = stripslashes($_POST['type'][$field_id]);
                                }
                                if ( isset($_POST['before'][$field_id]) && $_POST['before'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['before'] = stripslashes($_POST['before'][$field_id]);
                                }
                                if ( isset($_POST['after'][$field_id]) && $_POST['after'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['after'] = stripslashes($_POST['after'][$field_id]);
                                }
                                if ( isset($_POST['placeholder'][$field_id]) && $_POST['placeholder'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['placeholder'] = stripslashes($_POST['placeholder'][$field_id]);
                                }
                                if ( isset($_POST['min'][$field_id]) && $_POST['min'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['min'] = stripslashes($_POST['min'][$field_id]);
                                }
                                if ( isset($_POST['max'][$field_id]) && $_POST['max'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['max'] = stripslashes($_POST['max'][$field_id]);
                                }
                                if ( isset($_POST['step'][$field_id]) && $_POST['step'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['step'] = stripslashes($_POST['step'][$field_id]);
                                }
                                if ( isset($_POST['blank_option'][$field_id]) && $_POST['blank_option'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['blank_option'] = stripslashes($_POST['blank_option'][$field_id]);
                                }
                                if ( isset($_POST['parent_terms_only'][$field_id]) && $_POST['parent_terms_only'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['parent_terms_only'] = true;
                                }
                                if ( isset($_POST['dynamic_population'][$field_id]) && $_POST['dynamic_population'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['dynamic_population'] = true;
                                }
                                if ( isset($_POST['hide_empty'][$field_id]) && $_POST['hide_empty'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['hide_empty'] = true;
                                }
                                if ( isset($_POST['multiselect'][$field_id]) && $_POST['multiselect'][$field_id] != '' )
                                {
                                    $active_fields[$field_id]['multiselect'] = true;
                                }

                                if ( isset($_POST['option_keys'][$field_id]) && is_array($_POST['option_keys'][$field_id]) && !empty($_POST['option_keys'][$field_id]) )
                                {
                                    $options = array();
                                    foreach ( $_POST['option_keys'][$field_id] as  $i => $key )
                                    {
                                        $options[$key] = $_POST['options_values'][$field_id][$i];
                                    }
                                    $active_fields[$field_id]['options'] = $options;
                                }
                            }
                        }
                    }

                    if ( isset($_POST['inactive_fields_order']) && $_POST['inactive_fields_order'] != '' )
                    {
                        $field_ids = explode("|", sanitize_text_field($_POST['inactive_fields_order']));
                        if ( !empty($field_ids) )
                        {
                            foreach ( $field_ids as $field_id )
                            {
                                $inactive_fields[$field_id] = array(
                                    'show_label' => ( ( isset($_POST['show_label'][$field_id]) && $_POST['show_label'][$field_id] == '1' ) ? true : false ),
                                    'label' => ( isset($_POST['label'][$field_id]) ? stripslashes($_POST['label'][$field_id]) : '' ),
                                );

                                if ( isset($_POST['type'][$field_id]) && $_POST['type'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['type'] = stripslashes($_POST['type'][$field_id]);
                                }
                                if ( isset($_POST['before'][$field_id]) && $_POST['before'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['before'] = stripslashes($_POST['before'][$field_id]);
                                }
                                if ( isset($_POST['after'][$field_id]) && $_POST['after'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['after'] = stripslashes($_POST['after'][$field_id]);
                                }
                                if ( isset($_POST['placeholder'][$field_id]) && $_POST['placeholder'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['placeholder'] = stripslashes($_POST['placeholder'][$field_id]);
                                }
                                if ( isset($_POST['blank_option'][$field_id]) && $_POST['blank_option'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['blank_option'] = stripslashes($_POST['blank_option'][$field_id]);
                                }
                                if ( isset($_POST['parent_terms_only'][$field_id]) && $_POST['parent_terms_only'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['parent_terms_only'] = true;
                                }
                                if ( isset($_POST['dynamic_population'][$field_id]) && $_POST['dynamic_population'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['dynamic_population'] = true;
                                }
                                if ( isset($_POST['hide_empty'][$field_id]) && $_POST['hide_empty'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['hide_empty'] = true;
                                }
                                if ( isset($_POST['multiselect'][$field_id]) && $_POST['multiselect'][$field_id] != '' )
                                {
                                    $inactive_fields[$field_id]['multiselect'] = true;
                                }

                                if ( isset($_POST['option_keys'][$field_id]) && is_array($_POST['option_keys'][$field_id]) && !empty($_POST['option_keys'][$field_id]) )
                                {
                                    $options = array();
                                    foreach ( $_POST['option_keys'][$field_id] as  $i => $key )
                                    {
                                        $options[$key] = $_POST['options_values'][$field_id][$i];
                                    }
                                    $inactive_fields[$field_id]['options'] = $options;
                                }
                            }
                        }
                    }

                    $existing_search_forms[$current_id] = array(
                        'active_fields' => $active_fields,
                        'inactive_fields' => $inactive_fields,
                    );

                    $current_settings['search_forms'] = $existing_search_forms;

                    update_option( 'propertyhive_template_assistant', $current_settings );

                    break; 
                }
				default: { die("Unknown setting section"); }
			}
		}
		else
		{
			$search_results_fields = array();
            if ( isset($_POST['search_result_fields']) && is_array($_POST['search_result_fields']) )
            {
                $search_results_fields = $_POST['search_result_fields'];

                $new_search_results_fields = array();
                foreach ( $search_results_fields as $search_results_field )
                {
                    if ( $search_results_field == 'custom_field' )
                    {  
                        if ( isset($_POST['search_result_fields_custom_field']) && $_POST['search_result_fields_custom_field'] != '' )
                        {
                            $new_search_results_fields[] = ph_clean($_POST['search_result_fields_custom_field']);
                        }
                    }
                    else
                    {
                        $new_search_results_fields[] = $search_results_field;
                    }
                }

                $search_results_fields = $new_search_results_fields;
            }

            $propertyhive_template_assistant = array(
                'search_result_default_order' => ph_clean($_POST['search_result_default_order']),
                'search_result_columns' => (int)$_POST['search_result_columns'],
                'search_result_layout' => (int)$_POST['search_result_layout'],
                'search_result_fields' => $search_results_fields,
                'search_result_image_size' => ( isset($_POST['search_result_image_size']) ? ph_clean($_POST['search_result_image_size']) : 'medium' ),
                'search_result_css' => wp_unslash($_POST['search_result_css']),
                'search_result_css_all_pages' => isset($_POST['search_result_css_all_pages']) ? 'yes' : '',
            );

            $propertyhive_template_assistant = array_merge($current_settings, $propertyhive_template_assistant);

            update_option( 'propertyhive_template_assistant', $propertyhive_template_assistant );
		}
	}
}

endif;

return new PH_Settings_Frontend();