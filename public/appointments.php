<?php
//
// Description
// ===========
// This method will return merged appointments from all the calendars 
// which are available for the business.  Appointments from other modules
// will be merged into one appointment list.
//
// Arguments
// =========
//
//
function ciniki_calendars_appointments($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'date'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No follow up specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to the calendar, and which modules are turned on for the business
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'checkAccess');
	$rc = ciniki_calendars_checkAccess($ciniki, $args['business_id'], 'ciniki.calendar.appointments');
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
