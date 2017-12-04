<?php
//
// Description
// ===========
// This method will search appointments in the wineproduction and atdo
// modules for a tenant.   The results will be return as a single 
// list of appointments.
//
// Arguments
// =========
// api_key:
// auth_token:
// tnid:         The ID of the tenant the request is for.
// start_needle:        The string to search the appointments for a match.
// limit:               (optional) The maximum number of results to return.
// date:                (optional) The date to start the search from.  Results will be
//                      returned closest to this date and moving outwards in either direction.
// full:                (optional) Search closed tasks and bottled wineproduction orders.
//
function ciniki_calendars_search($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'), 
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'), 
        'date'=>array('required'=>'no', 'type'=>'date', 'blank'=>'yes', 'name'=>'Date'),
        'full'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Full Flag'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to the calendar, and which modules are turned on for the tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'checkAccess');
    $rc = ciniki_calendars_checkAccess($ciniki, $args['tnid'], 'ciniki.calendars.search');
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
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'appointmentSearch');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], $args);
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.calendars.5', 'msg'=>'Error checking for appointments.', 'err'=>$rc['err']));
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
        if( $a['appointment']['start_ts'] == $b['appointment']['start_ts'] ) {
            return 0;
        }
        return ($a['appointment']['start_ts'] < $b['appointment']['start_ts'])?-1:1;
        });
    return array('stat'=>'ok', 'appointments'=>$lists);

/*  Code removed may 17, 2015 */
/*  $modules = $rc['modules'];

    
    // Store the appointments from different modules
    $lists = array();

    if( isset($modules['ciniki.atdo']) ) {
        //
        // Grab the wine production appointments
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'atdo', 'private', 'appointmentSearch');
        $rc = ciniki_atdo__appointmentSearch($ciniki, $args['tnid'], $args);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['appointments']) ) {
            array_push($lists, $rc['appointments']);
        }
    }

//  if( isset($modules['ciniki.tasks']) ) {
//      //
//      // Grab the wine production appointments
//      //
//      ciniki_core_loadMethod($ciniki, 'ciniki', 'tasks', 'private', 'appointmentSearch');
//      $rc = ciniki_tasks__appointmentSearch($ciniki, $args['tnid'], $args);
//      if( $rc['stat'] != 'ok' ) {
//          return $rc;
//      }
//      if( isset($rc['appointments']) ) {
//          array_push($lists, $rc['appointments']);
//      }
//  }

    if( isset($modules['ciniki.wineproduction']) ) {
        //
        // Grab the wine production appointments
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'appointmentSearch');
        $rc = ciniki_wineproduction__appointmentSearch($ciniki, $args['tnid'], $args);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['appointments']) ) {
            array_push($lists, $rc['appointments']);
        }
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'mergeAppointments');
    return ciniki_calendars_mergeAppointments($ciniki, $lists);
*/
}
?>
