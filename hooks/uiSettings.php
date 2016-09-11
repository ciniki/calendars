<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_calendars_hooks_uiSettings($ciniki, $business_id, $args) {

    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array());

    //
    //
    if( isset($ciniki['business']['modules']['ciniki.calendars'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>6000,
            'id'=>'calendars',
            'label'=>'Calendars', 
            'edit'=>array('app'=>'ciniki.calendars.main'),
            'search'=>array(
                'method'=>'ciniki.calendars.search',
                'args'=>array(),
                'container'=>'appointments',
                'searchtype'=>'appointments',
                'cols'=>2,
                'cellClasses'=>array('multiline slice_0', 'schedule_appointment'),
                'cellColours'=>array(
                    '0'=>'\'\'',
                    '1'=>'if( d.appointment != null && d.appointment.colour != null && d.appointment.colour != \'\' ) {'
                            . 'd.appointment.colour;'
                        . '} else {'
                            . '\'#77ddff\';'
                        . '}',
                    ),
                'cellValues'=>array(
                    '0'=>'if( d.appointment.start_ts == 0 ) {'
                            . '"unschedule";'
                        . '} else if( d.appointment_allday == "yes" ) {'
                            . 'd.appointment.start_date.split(/ [0-]+:/)[0];'
                        . '} else {'
                            . '\'<span class="maintext">\' + d.appointment.start_date.split(/ [0-9]+:/)[0] + \'</span><span class="subtext">\' + d.appointment.start_date.split(/, [0-9][0-9][0-9][0-9] /)[1] + \'</span>\''
                        . '}',
                    '1'=>'var t="";'
                        . 'if( d.appointment.secondary_colour != null && d.appointment.secondary_colour != \'\' ) {'
                            . 't += \'<span class="colourswatch" style="background-color:\' + d.appointment.secondary_colour + \'">\';'
                            . 'if( d.appointment.secondary_colour_text != null && d.appointment.secondary_colour_text != \'\' ) { '
                                . 't += d.appointment.secondary_colour_text; '
                            . '} else {'
                                . 't += \'&nbsp;\';'
                            . '} '
                            . 't += \'</span> \''
                        . '} '
                        . 't += d.appointment.subject;'
                        . 'if( d.appointment.secondary_text != null && d.appointment.secondary_text != \'\' ) {'
                            . 't += \' <span class="secondary">\' + d.appointment.secondary_text + \'</span>\';'
                        . '} '
                        . 't;',
                    ),
//                'cellEdits'=>array(
//                    '0'=>array('method'=>'ciniki.calendars.main', 'args'=>array('date'=>'d.appointment.date')),
//                    '1'=>array('method'=>'d.app;', 'args'=>array('appointment_id'=>'d.appointment.id')),
//                    ),
                'noData'=>'No appointments found',
//                'edit'=>array('method'=>'ciniki.calendars.main', 'args'=>array('order_id'=>'d.order.id;')),
                'submit'=>array('method'=>'ciniki.calendars.main', 'args'=>array('search'=>'search_str')),
                ),
            );
        if( ciniki_core_checkModuleFlags($ciniki, 'ciniki.atdo', 0x01) ) {
            $menu_item['add'] = array('app'=>'ciniki.atdo.main', 'args'=>array('add'=>'\'"appointment"\''));
        }
        $rsp['menu_items'][] = $menu_item;
    } 

    return $rsp;
}
?>
