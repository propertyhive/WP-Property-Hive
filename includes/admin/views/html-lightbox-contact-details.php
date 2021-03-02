<?php
	if ( $contact_ids == '' )
    {
        $contact_ids = array();
    }
    if ( !is_array($contact_ids) && $contact_ids != '' && $contact_ids != 0 )
    {
        $contact_ids = array($contact_ids);
    }
?>

CONTACT DETAILS IN VIEW <?php echo implode(", ", $contact_ids); ?>