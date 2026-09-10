<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$propertyhive_ph_key_date_input = array();
foreach ( array( 'description', 'status', 'due_date_time', 'type', 'date_post_id' ) as $propertyhive_ph_input_key ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only AJAX controls; the save action separately verifies the nonce and CRM permissions.
    $propertyhive_ph_key_date_input[$propertyhive_ph_input_key] = isset( $_POST[$propertyhive_ph_input_key] ) && is_string( $_POST[$propertyhive_ph_input_key] ) ? sanitize_text_field( wp_unslash( $_POST[$propertyhive_ph_input_key] ) ) : '';
}
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only AJAX control; sanitize and escape its displayed contents.
$propertyhive_ph_key_date_input['notes'] = isset( $_POST['notes'] ) && is_string( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
?><td colspan="5">
    <div class="propertyhive_meta_box">
        <div class="options_group">
            <p class="form-field">
                <label for="date_description">Description</label>
                <input type="text" id="date_description" class="short" value="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
 echo esc_attr( $propertyhive_ph_key_date_input['description'] ); ?>">
            </p>
            <p class="form-field">
                <label for="key_date_status">Status</label>
                <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $output = '<select id="key_date_status" name="key_date_status">';

                    foreach ( array( 'pending', 'booked', 'complete', 'on_hold', 'cancelled' ) as $status )
                    {
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.NonceVerification.Missing -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope. This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
                        $selected_value = strtolower( $propertyhive_ph_key_date_input['status'] );
                        if ( in_array($selected_value, array('overdue', 'upcoming') ) )
                        {
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $selected_value = 'pending';
                        }
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $output .= '<option value="' . esc_attr($status) . '"';
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $output .= selected($status, $selected_value, false );
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $output .=  '>' . esc_html(ucwords(str_replace("_", " ", $status))) . '</option>';
                    }

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $output .= '</select>';

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Select options are escaped when assembled; selected() emits WordPress's fixed selected attribute.
                    echo $output;
                ?>
            </p>
            <p class="form-field">
                <?php
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.NonceVerification.Missing -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope. This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
                $due_date_time = strtotime( $propertyhive_ph_key_date_input['due_date_time'] );
                ?>
                <label for="date_due_quick_edit">Due Date</label>
                <input type="date" class="small" name="date_due_quick_edit" id="date_due_quick_edit" value="<?php echo esc_attr( $due_date_time !== false ? gmdate( 'Y-m-d', $due_date_time ) : '' ); ?>" placeholder="">

                <select id="date_due_hours_quick_edit" name="date_due_hours_quick_edit" class="select short" style="width:55px">';
                    <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    for ( $i = 0; $i < 23; ++$i )
                    {
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . esc_attr($j) . '"';
                        if ( gmdate('H', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . esc_html($j) . '</option>';
                    }
                    ?>
                </select>
                :
                <select id="date_due_minutes_quick_edit" name="date_due_minutes_quick_edit" class="select short" style="width:55px">
                    <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    for ( $i = 0; $i < 60; $i+=5 )
                    {
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $j = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo '<option value="' . esc_attr($j) . '"';
                        if ( gmdate('i', $due_date_time) == $j ) { echo ' selected'; }
                        echo '>' . esc_html($j) . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p class="form-field">
                <label for="date_type"><?php echo esc_html(__('Key Date Type', 'propertyhive')); ?></label>
                <select id="date_type" name="date_type" class="select short">
                    <?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $key_date_type_terms = get_terms( array_merge( wp_parse_args( array(
                        'hide_empty' => false,
                        'parent' => 0
                    ) ), array( 'taxonomy' => 'management_key_date_type' ) ) );
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $recurrence_rules = get_option( 'propertyhive_key_date_type', array() );
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $recurrence_rules = is_array( $recurrence_rules ) ? $recurrence_rules : array();

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $parent_post_type = get_post_type( $post_id );
                    if ( !empty( $key_date_type_terms ) && !is_wp_error( $key_date_type_terms ) )
                    {
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        foreach ($key_date_type_terms as $key_date_type_term)
                        {
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $recurrence_type = isset($recurrence_rules[$key_date_type_term->term_id]) ? $recurrence_rules[$key_date_type_term->term_id]['recurrence_type'] : '';
                            if ( $parent_post_type == 'tenancy' || ( $parent_post_type == 'property' && $recurrence_type == 'property_management' ) )
                            {
                                echo '<option value="' . esc_attr($key_date_type_term->term_id) . '"';
                                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
                                if ( $propertyhive_ph_key_date_input['type'] !== '' && $propertyhive_ph_key_date_input['type'] == $key_date_type_term->term_id ) { echo ' selected'; }
                                echo '>' . esc_html($key_date_type_term->name) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <p class="form-field">
                <label for="date_notes_quick_edit">Notes</label>
                <textarea id="date_notes_quick_edit" class="short"><?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
 echo esc_textarea( $propertyhive_ph_key_date_input['notes'] !== '-' ? $propertyhive_ph_key_date_input['notes'] : '' ); ?></textarea>
            </p>
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
            if ( isset($recurrence_rules[$propertyhive_ph_key_date_input['type']]) && isset( $recurrence_rules[$propertyhive_ph_key_date_input['type']]['recurrence_rule'] ) )
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $recurrence = array();

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.NonceVerification.Missing -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope. This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
                foreach (explode(';', $recurrence_rules[$propertyhive_ph_key_date_input['type']]['recurrence_rule']) as $key_value_pair){
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    list($key, $value) = explode('=', $key_value_pair);
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $recurrence[strtolower($key)] = $value;
                }

                if ( isset($recurrence['freq']) && $recurrence['freq'] != 'ONCE' )
                {
                    ?>
                    <p id="next_key_date_checkbox" class="form-field hidden">
                        <label for="book_next_key_date"><?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
 echo /* translators: %s: Key date description. */ esc_html( sprintf( __( 'Book Next %s?', 'propertyhive' ), $propertyhive_ph_key_date_input['description'] !== '' ? $propertyhive_ph_key_date_input['description'] : __( 'Key Date', 'propertyhive' ) ) ); ?></label>
                        <input type="checkbox" id="book_next_key_date" >
                    </p>
                    <?php
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $next_key_date = '';
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $next_key_date_hours = '00';
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $next_key_date_minutes = '00';

                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                        $interval = isset($recurrence['interval']) ? $recurrence['interval'] : '1';
                        switch( $recurrence['freq'] )
                        {
                            case 'DAILY':
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                                $frequency = 'day';
                                break;
                            case 'WEEKLY':
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                                $frequency = 'week';
                                break;
                            case 'MONTHLY':
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                                $frequency = 'month';
                                break;
                            case 'YEARLY':
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                                $frequency = 'year';
                                break;
                        }

                        if ( isset($frequency) )
                        {
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $next_key_timestamp = strtotime('+' . $interval . ' ' . $frequency, $due_date_time);
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $next_key_date = gmdate('Y-m-d', $next_key_timestamp);
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $next_key_date_hours = gmdate('H', $next_key_timestamp);
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            $next_key_date_minutes = gmdate('i', $next_key_timestamp);
                        }
                    ?>
                    <p id="next_key_date_field" class="form-field hidden">
                        <label for="next_key_date">&nbsp;</label>
                        <input type="date" class="small" name="next_key_date" id="next_key_date" value="<?php echo esc_attr($next_key_date); ?>" placeholder="">

                        <select id="next_key_date_hours" name="next_key_date_hours" class="select short" style="width:55px">';
                            <?php
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            for ( $i = 0; $i < 23; ++$i )
                            {
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
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
                            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                            for ( $i = 0; $i < 60; $i+=5 )
                            {
                                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
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
            <button type="button" id="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
 echo esc_attr( (int) $propertyhive_ph_key_date_input['date_post_id'] ); ?>" class="button button-primary save-quick-edit">Update</button>&nbsp;
            <button type="button" id="<?php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This authorized AJAX template only renders controls; key-date writes separately verify their nonce and CRM capability.
 echo esc_attr( (int) $propertyhive_ph_key_date_input['date_post_id'] ); ?>" class="button cancel-quick-edit">Cancel</button>
        </div>
    </div>
</td>
