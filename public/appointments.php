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
// business_id:			The ID of the business to get the appointments from.
// date:				(optional) The date to get the appointments for.  If not specified
//						the current date is used.
//
function ciniki_calendars_appointments($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'date'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Date'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to the calendar, and which modules are turned on for the business
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'checkAccess');
	$rc = ciniki_calendars_checkAccess($ciniki, $args['business_id'], 'ciniki.calendars.appointments');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$modules = $rc['modules'];

	
	// Store the appointments from different modules
	$lists = array();

	if( isset($modules['ciniki.atdo']) ) {
		//
		// Grab the wine production appointments
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'atdo', 'private', 'appointments');
		$rc = ciniki_atdo__appointments($ciniki, $args['business_id'], $args);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['appointments']) ) {
			array_push($lists, $rc['appointments']);
		}
	}

//	if( isset($modules['ciniki.tasks']) ) {
//		//
//		// Grab the wine production appointments
//		//
//		ciniki_core_loadMethod($ciniki, 'ciniki', 'tasks', 'private', 'appointments');
//		$rc = ciniki_tasks__appointments($ciniki, $args['business_id'], $args);
//		if( $rc['stat'] != 'ok' ) {
//			return $rc;
//		}
//		if( isset($rc['appointments']) ) {
//			array_push($lists, $rc['appointments']);
//		}
//	}
	
	if( isset($modules['ciniki.wineproduction']) ) {
		//
		// Grab the wine production appointments
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'appointments');
		$rc = ciniki_wineproduction__appointments($ciniki, $args['business_id'], $args);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['appointments']) ) {
			array_push($lists, $rc['appointments']);
		}
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'mergeAppointments');
	return ciniki_calendars_mergeAppointments($ciniki, $lists);
}
?>
