<?php
//
// Description
// ===========
// This function will merge multiple appointment lists together.
//
// 
// Arguments
// =========
//
//
function ciniki_calendar_mergeAppointments($ciniki, $lists) {

	$appointments = array();
	$cur_idx = array();

	for($i=0;$i<count($lists);$i++) {
		$cur_pos[$i] = 0;
	}

	$last_timestamp = 0;
	$cur_offset = 0;
	while( $cur_offset < 99999999999 ) {
		//
		// Find next appointment
		//
		$cur_offset = 99999999999;
		$cur_pos = -1;
		for($i=0;$i<count($lists);$i++) {
			// Check for end of lists
			if( $cur_idx[$i] > count($list) ) {
				continue;
			}
			$offset = ($list[$cur_idx[$i]]['appointment']['start_ts'] - $last_timestamp);
			if( $offset < $cur_offset ) {
				$cur_offset = $offset;
				$cur_pos = $i;
			}
		}
		
		// If there are none found, we've merged all appointments
		if( $cur_offset == 99999999999 ) {
			break;
		}

		// Push appointment into new array
		array_push($appointments, $lists[$cur_pos]['appointments'][$cur_idx[$cur_pos]]);
		$cur_idx[$cur_pos]++;
	}

	return array('stat'=>'ok', 'appointments'=>$appointments);
}
?>
