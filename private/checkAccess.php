<?php
//
// Description
// ===========
// This function will check the user has access to the calendar module, and 
// return a list of other modules enabled for the business.
//
// Arguments
// =========
// business_id: 		The ID of the business the request is for.
// 
// Returns
// =======
//
function ciniki_calendar_checkAccess($ciniki, $business_id, $method) {

	//
	// Check if the module is turned on for the business
	// Check the business is active
	// Get the ruleset for this module
	//
	$strsql = "SELECT ruleset FROM ciniki_businesses, ciniki_business_modules "
		. "WHERE ciniki_businesses.id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_businesses.status = 1 "														// Business is active
		. "AND ciniki_businesses.id = ciniki_business_modules.business_id "
		. "AND ciniki_business_modules.package = 'ciniki' "
		. "AND ciniki_business_modules.module = 'calendar' "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'businesses', 'module');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['module']) || !isset($rc['module']['ruleset']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'501', 'msg'=>'Access denied.'));
	}

	//
	// Sysadmins are allowed full access
	//
	if( ($ciniki['session']['user']['perms'] & 0x01) == 0x01 ) {
		return array('stat'=>'ok');
	}

	//
	// Check to see if the ruleset is valid
	//
	if( !isset($rulesets[$rc['module']['ruleset']]) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'358', 'msg'=>'Access denied.'));
	}
	$ruleset = $rc['module']['ruleset'];

	// 
	// Get the rules for the specified method
	//
	$rules = array();
	if( isset($rulesets[$ruleset]['methods']) && isset($rulesets[$ruleset]['methods'][$method]) ) {
		$rules = $rulesets[$ruleset]['methods'][$method];
	} elseif( isset($rulesets[$ruleset]['default']) ) {
		$rules = $rulesets[$ruleset]['default'];
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'359', 'msg'=>'Access denied.'));
	}


	//
	// Apply the rules.  Any matching rule will allow access.
	//


	//
	// If business_group specified, check the session user in the business_users table.
	//
	if( isset($rules['business_group']) && $rules['business_group'] > 0 ) {
		//
		// Compare the session users bitmask, with the bitmask specified in the rules
		// If when OR'd together, any bits are set, they have access.
		//
		$strsql = sprintf("SELECT business_id, user_id FROM ciniki_business_users "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND user_id = '" . ciniki_core_dbQuote($ciniki, $ciniki['session']['user']['id']) . "' "
			. "AND (groups & 0x%x) > 0 ", ciniki_core_dbQuote($ciniki, $rules['business_group']));
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'businesses', 'user');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		//
		// Double check business_id and user_id match, for single row returned.
		//
		if( !isset($rc['user']) || !isset($rc['user']['business_id']) 
			|| $rc['user']['business_id'] != $business_id 
			|| $rc['user']['user_id'] != $ciniki['session']['user']['id'] ) {
			// Access Granted!
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'360', 'msg'=>'Access denied.'));
		}
	}

	//
	// If all tests passed, then return ok
	//
	return array('stat'=>'ok');
}
?>
