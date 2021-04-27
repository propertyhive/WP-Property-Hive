<?php

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    echo '<table style="width:100%">
        <thead>
            <tr>
                <th style="text-align:left;">' . __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ) . '</th>
                <th style="text-align:left;">' . __( 'Subject', 'propertyhive' ) . '</th>
                <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                <th style="text-align:left;">' . __( 'Negotiator', 'propertyhive' ) . '</th>
                <th style="text-align:left;">' . __( 'Office', 'propertyhive' ) . '</th>
            </tr>
        </thead>
        <tbody>';

    while ( $enquiries_query->have_posts() )
    {
        $enquiries_query->the_post();

        $the_enquiry = new PH_Enquiry( get_the_ID() );

        echo '<tr' . ( ($the_enquiry->status == 'closed') ? ' style="opacity:0.4"' : '' ) . '>';
            echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID() ) . '">' . get_the_time( 'jS M Y' ) . ' ' . get_the_time( 'H:i' ) . '</a></td>';
            echo '<td style="text-align:left;">' . get_the_title() . '</td>';
            echo '<td style="text-align:left;">' . $the_enquiry->status . '</td>';
            echo '<td style="text-align:left;">';
            if ($the_enquiry->_negotiator_id == '' || $the_enquiry->_negotiator_id == 0)
            {
                echo '<em>-- ' . __( 'Unassigned', 'propertyhive' ) . ' --</em>';
            }
            else
            {
                $userdata = get_userdata( $the_enquiry->_negotiator_id );
                if ( $userdata !== FALSE )
                {
                    echo $userdata->display_name;
                }
                else
                {
                    echo '<em>Unknown user</em>';
                }
            }
            echo '</td>';
            echo '<td style="text-align:left;">' . get_the_title( $the_enquiry->_office_id ) . '</td>';
        echo '</tr>';
    }

    echo '
        </tbody>
    </table>
    <br>';