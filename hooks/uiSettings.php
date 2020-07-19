<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_calendars_hooks_uiSettings($ciniki, $tnid, $args) {

    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array());

    //
    //
    if( isset($ciniki['tenant']['modules']['ciniki.calendars'])
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
                    '1'=>'if( d.colour != null && d.colour != \'\' ) {'
                            . 'd.colour;'
                        . '} else {'
                            . '\'#77ddff\';'
                        . '}',
                    ),
                'cellValues'=>array(
                    '0'=>'if( d.start_ts == 0 ) {'
                            . '"unschedule";'
                        . '} else if( d.appointment_allday == "yes" ) {'
                            . 'd.start_date.split(/ [0-]+:/)[0];'
                        . '} else {'
                            . '\'<span class="maintext">\' + d.start_date.split(/ [0-9]+:/)[0] + \'</span><span class="subtext">\' + d.start_date.split(/, [0-9][0-9][0-9][0-9] /)[1] + \'</span>\''
                        . '}',
                    '1'=>'var t="";'
                        . 'if( d.secondary_colour != null && d.secondary_colour != \'\' ) {'
                            . 't += \'<span class="colourswatch" style="background-color:\' + d.secondary_colour + \'">\';'
                            . 'if( d.secondary_colour_text != null && d.secondary_colour_text != \'\' ) { '
                                . 't += d.secondary_colour_text; '
                            . '} else {'
                                . 't += \'&nbsp;\';'
                            . '} '
                            . 't += \'</span> \''
                        . '} '
                        . 't += d.subject;'
                        . 'if( d.secondary_text != null && d.secondary_text != \'\' ) {'
                            . 't += \' <span class="secondary">\' + d.secondary_text + \'</span>\';'
                        . '} '
                        . 't;',
                    ),
//                'cellEdits'=>array(
//                    '0'=>array('method'=>'ciniki.calendars.main', 'args'=>array('date'=>'d.date')),
//                    '1'=>array('method'=>'d.app;', 'args'=>array('appointment_id'=>'d.id')),
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
