<td colspan="4">
    <div class="propertyhive_meta_box">
        <div class="options_group">
            <p class="form-field">
                <label for="date_description">Description</label>
                <input type="text" id="date_description" class="short" value="<?php echo isset( $_POST['description'] ) ? $_POST['description'] : ''; ?>">
            </p>
            <p class="form-field">
                <label for="key_date_status">Status</label>
                <?php
                    $output = '<select id="key_date_status" name="key_date_status">';

                    foreach ( array( 'pending', 'booked', 'complete' ) as $status )
                    {
                        $selected_value = isset( $_POST['status'] ) ? strtolower($_POST['status']) : '';
                        if ( in_array($selected_value, array('overdue', 'upcoming') ) )
                        {
                            $selected_value = 'pending';
                        }
                        $output .= '<option value="' . $status . '"';
                        $output .= selected($status, $selected_value, false );
                        $output .=  '>' . ucwords($status) . '</option>';
                    }

                    $output .= '</select>';

                    echo $output;
                ?>
            </p>
            <p class="form-field">
                <?php
                $due_date_time = isset( $_POST['due_date_time'] ) ? strtotime($_POST['due_date_time']) : '';
                ?>
                <label for="date_due_quick_edit">Due Date</label>
                <input type="text" id="date_due_quick_edit" name="date_due_quick_edit" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="<?php echo date('Y-m-d', $due_date_time); ?>">

                <select id="date_due_hours_quick_edit" name="date_due_hours_quick_edit" class="select short" style="width:55px">';
                    <?php
                    for ( $i = 0; $i < 23; ++$i )
                    {
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . $j . '"';
                        if ( date('H', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . $j . '</option>';
                    }
                    ?>
                </select>
                :
                <select id="date_due_minutes_quick_edit" name="date_due_minutes_quick_edit" class="select short" style="width:55px">
                    <?php
                    for ( $i = 0; $i < 60; $i+=5 )
                    {
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . $j . '"';
                        if ( date('i', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . $j . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p class="form-field">
                <label for="date_type"><?php echo __('Key Date Type', 'propertyhive'); ?></label>
                <select id="date_type" name="date_type" class="select short">
                    <?php
                    $key_date_type_terms = get_terms( 'management_key_date_type', array(
                        'hide_empty' => false,
                        'parent' => 0
                    ) );
                    $recurrence_rules = get_option( 'propertyhive_key_date_type', array() );
                    $recurrence_rules = is_array( $recurrence_rules ) ? $recurrence_rules : array();

                    $parent_post_type = get_post_type( $post_id );
                    if ( !empty( $key_date_type_terms ) && !is_wp_error( $key_date_type_terms ) )
                    {
                        var_dump($_POST['type']);
                        foreach ($key_date_type_terms as $key_date_type_term)
                        {
                            var_dump($key_date_type_term->term_id);
                            $recurrence_type = isset($recurrence_rules[$key_date_type_term->term_id]) ? $recurrence_rules[$key_date_type_term->term_id]['recurrence_type'] : '';
                            if ( $parent_post_type == 'tenancy' || ( $parent_post_type == 'property' && $recurrence_type == 'property_management' ) )
                            {
                                echo '<option value="' . $key_date_type_term->term_id . '"';
                                if ( isset( $_POST['type'] ) && $_POST['type'] == $key_date_type_term->term_id ) { echo ' selected'; }
                                echo '>' . $key_date_type_term->name . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <button type="button" id="<?php echo $_POST['date_post_id']; ?>" class="button button-primary save-quick-edit">Update</button>&nbsp;
            <button type="button" id="<?php echo $_POST['date_post_id']; ?>" class="button cancel-quick-edit">Cancel</button>
        </div>
    </div>
</td>
