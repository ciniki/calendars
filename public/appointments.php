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
function ciniki_calendar_appointments($ciniki) {




	//
	// Check access to the calendar, and which modules are turned on for the business
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendar', 'private', 'checkAccess');
	$rc = ciniki_calendar_checkAccess($ciniki, $args['business_id'], 'ciniki.calendar.appointments');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$modules = $rc['modules'];

	
	// Store the appointments from different modules
	$lists = array();
	
	//
	// Grab the wine production appointments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', '_appointments');
	$rc = ciniki_wineproduction_appointments($ciniki);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['appointments']) ) {
		array_push($lists, $rc['appointments']);
	}

	
	ciniki_core_loadMethod($ciniki, 'ciniki', 'calendar', 'private', 'mergeAppointments');
	return ciniki_calendar_mergeAppointments($ciniki, $lists);
}
?>
