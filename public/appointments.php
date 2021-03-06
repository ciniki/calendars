<?php
//
// Description
// ===========
// This method will return a merged list of appointments from the
// wineproduction and atdo module.  The appointments will be for a 
// specified day.
//
// Arguments
// =========
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the appointments from.
// date:                (optional) The date to get the appointments for.  If not specified
//                      the current date is used.
//
function ciniki_calendars_appointments($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'date'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Date'), 
        'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Start Date'), 
        'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'End Date'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to the calendar, and which modules are turned on for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'checkAccess');
    $rc = ciniki_calendars_checkAccess($ciniki, $args['tnid'], 'ciniki.calendars.appointments');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    // Store the appointments from different modules
    $lists = array();

    //
    // Check if any modules are currently using this subscription
    //
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'appointments');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], $args);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.calendars.4', 'msg'=>'Error checking for appointments.', 'err'=>$rc['err']));
            }
            if( isset($rc['appointments']) ) {
                $lists = array_merge($lists, $rc['appointments']);
            }
        }
    }

    //
    // Sort appointments by start_ts
    //
    usort($lists, function($a, $b) {
        if( $a['start_ts'] == $b['start_ts'] ) {
            return 0;
        }
        return ($a['start_ts'] < $b['start_ts'])?-1:1;
        });

    return array('stat'=>'ok', 'appointments'=>$lists);


//  ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'mergeAppointments');
//  return ciniki_calendars_mergeAppointments($ciniki, $lists);
}
?>
