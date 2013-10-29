<?php
//
// Description
// ===========
// This method will search appointments in the wineproduction and atdo
// modules for a business.   The results will be return as a single 
// list of appointments.
//
// Arguments
// =========
// api_key:
// auth_token:
// business_id:			The ID of the business the request is for.
// start_needle:		The string to search the appointments for a match.
// limit:				(optional) The maximum number of results to return.
// date:				(optional) The date to start the search from.  Results will be
//						returned closest to this date and moving outwards in either direction.
// full:				(optional) Search closed tasks and bottled wineproduction orders.
//
function ciniki_calendars_search($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
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
