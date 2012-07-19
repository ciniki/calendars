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
function ciniki_calendars_search($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No search specified'), 
		'limit'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No limit specified'), 
		'date'=>array('required'=>'no', 'type'=>'date', 'blank'=>'yes', 'errmsg'=>'No date specified'),
		'full'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No limits specified'),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check access to the calendar, and which modules are turned on for the business
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendars', 'private', 'checkAccess');
	$rc = ciniki_calendars_checkAccess($ciniki, $args['business_id'], 'ciniki.calendars.search');
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'atdo', 'private', 'appointmentSearch');
		$rc = ciniki_atdo__appointmentSearch($ciniki, $args['business_id'], $args);
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
//		ciniki_core_loadMethod($ciniki, 'ciniki', 'tasks', 'private', 'appointmentSearch');
//		$rc = ciniki_tasks__appointmentSearch($ciniki, $args['business_id'], $args);
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
		ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'appointmentSearch');
		$rc = ciniki_wineproduction__appointmentSearch($ciniki, $args['business_id'], $args);
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
