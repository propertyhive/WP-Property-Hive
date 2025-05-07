<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?><td colspan="5">
    <div class="propertyhive_meta_box">
        <div class="options_group">
            <p class="form-field">
                <label for="date_description">Description</label>
                <input type="text" id="date_description" class="short" value="<?php echo isset( $_POST['description'] ) ? esc_html(ph_clean($_POST['description'])) : ''; ?>">
            </p>
            <p class="form-field">
                <label for="key_date_status">Status</label>
                <?php
                    $output = '<select id="key_date_status" name="key_date_status">';

                    foreach ( array( 'pending', 'booked', 'complete', 'on_hold', 'cancelled' ) as $status )
                    {
                        $selected_value = isset( $_POST['status'] ) ? strtolower($_POST['status']) : '';
                        if ( in_array($selected_value, array('overdue', 'upcoming') ) )
                        {
                            $selected_value = 'pending';
                        }
                        $output .= '<option value="' . esc_attr($status) . '"';
                        $output .= selected($status, $selected_value, false );
                        $output .=  '>' . esc_html(ucwords(str_replace("_", " ", $status))) . '</option>';
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
                <input type="date" class="small" name="date_due_quick_edit" id="date_due_quick_edit" value="<?php echo esc_attr(date('Y-m-d', $due_date_time)); ?>" placeholder="">

                <select id="date_due_hours_quick_edit" name="date_due_hours_quick_edit" class="select short" style="width:55px">';
                    <?php
                    for ( $i = 0; $i < 23; ++$i )
                    {
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . esc_attr($j) . '"';
                        if ( date('H', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . esc_html($j) . '</option>';
                    }
                    ?>
                </select>
                :
                <select id="date_due_minutes_quick_edit" name="date_due_minutes_quick_edit" class="select short" style="width:55px">
                    <?php
                    for ( $i = 0; $i < 60; $i+=5 )
                    {
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . esc_attr($j) . '"';
                        if ( date('i', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . esc_html($j) . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p class="form-field">
                <label for="date_type"><?php echo esc_html(__('Key Date Type', 'propertyhive')); ?></label>
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
                        foreach ($key_date_type_terms as $key_date_type_term)
                        {
                            $recurrence_type = isset($recurrence_rules[$key_date_type_term->term_id]) ? $recurrence_rules[$key_date_type_term->term_id]['recurrence_type'] : '';
                            if ( $parent_post_type == 'tenancy' || ( $parent_post_type == 'property' && $recurrence_type == 'property_management' ) )
                            {
                                echo '<option value="' . esc_attr($key_date_type_term->term_id) . '"';
                                if ( isset( $_POST['type'] ) && $_POST['type'] == $key_date_type_term->term_id ) { echo ' selected'; }
                                echo '>' . esc_html($key_date_type_term->name) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <p class="form-field">
                <label for="date_notes_quick_edit">Notes</label>
                <textarea id="date_notes_quick_edit" class="short"><?php echo ( isset( $_POST['notes'] ) && $_POST['notes'] != '-' ) ? stripslashes( sanitize_textarea_field($_POST['notes']) ) : ''; ?></textarea>
            </p>
            <?php
            if ( isset($recurrence_rules[$_POST['type']]) && isset( $recurrence_rules[$_POST['type']]['recurrence_rule'] ) )
            {
                $recurrence = array();

                foreach (explode(';', $recurrence_rules[$_POST['type']]['recurrence_rule']) as $key_value_pair){
                    list($key, $value) = explode('=', $key_value_pair);
                    $recurrence[strtolower($key)] = $value;
                }

                if ( isset($recurrence['freq']) && $recurrence['freq'] != 'ONCE' )
                {
                    ?>
                    <p id="next_key_date_checkbox" class="form-field hidden">
                        <label for="book_next_key_date"><?php echo esc_html(__('Book Next ' . ( isset( $_POST['description'] ) ? esc_html(ph_clean($_POST['description'])) : 'Key Date' ) . '?', 'propertyhive')); ?></label>
                        <input type="checkbox" id="book_next_key_date" >
                    </p>
                    <?php
                        $next_key_date = '';
                        $next_key_date_hours = '00';
                        $next_key_date_minutes = '00';

                        $interval = isset($recurrence['interval']) ? $recurrence['interval'] : '1';
                        switch( $recurrence['freq'] )
                        {
                            case 'DAILY':
                                $frequency = 'day';
                                break;
                            case 'WEEKLY':
                                $frequency = 'week';
                                break;
                            case 'MONTHLY':
                                $frequency = 'month';
                                break;
                            case 'YEARLY':
                                $frequency = 'year';
                                break;
                        }

                        if ( isset($frequency) )
                        {
                            $next_key_timestamp = strtotime('+' . $interval . ' ' . $frequency, $due_date_time);
                            $next_key_date = date('Y-m-d', $next_key_timestamp);
                            $next_key_date_hours = date('H', $next_key_timestamp);
                            $next_key_date_minutes = date('i', $next_key_timestamp);
                        }
                    ?>
                    <p id="next_key_date_field" class="form-field hidden">
                        <label for="next_key_date">&nbsp;</label>
                        <input type="date" class="small" name="next_key_date" id="next_key_date" value="<?php echo esc_attr($next_key_date); ?>" placeholder="">

                        <select id="next_key_date_hours" name="next_key_date_hours" class="select short" style="width:55px">';
                            <?php
                            for ( $i = 0; $i < 23; ++$i )
                            {
                                $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                                echo '<option value="' . esc_attr($j) . '"';
                                if ( $next_key_date_hours == $j ) { echo ' selected'; }
                                echo '>' . esc_html($j) . '</option>';
                            }
                            ?>
                        </select>
                        :
                        <select id="next_key_date_minutes" name="next_key_date_minutes" class="select short" style="width:55px">
                            <?php
                            for ( $i = 0; $i < 60; $i+=5 )
                            {
                                $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                                echo '<option value="' . esc_attr($j) . '"';
                                if ( $next_key_date_minutes == $j ) { echo ' selected'; }
                                echo '>' . esc_html($j) . '</option>';
                            }
                            ?>
                        </select>
                    </p>
                    <?php
                }
            }
            ?>
            <button type="button" id="<?php echo esc_attr((int)$_POST['date_post_id']); ?>" class="button button-primary save-quick-edit">Update</button>&nbsp;
            <button type="button" id="<?php echo esc_attr((int)$_POST['date_post_id']); ?>" class="button cancel-quick-edit">Cancel</button>
        </div>
    </div>
</td>
