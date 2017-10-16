<?php
//
// Description
// -----------
// This function will process the web request for calendar.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// business_id:     The ID of the business to get calendar for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_calendars_web_processRequest(&$ciniki, $settings, $business_id, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['business']['modules']['ciniki.calendars']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.calendars.8', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }
    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'blocks'=>array(),
        'container-class'=>'ciniki-calendars',
        );

    //
    // Setup titles
    //
    if( count($page['breadcrumbs']) == 0 ) {
        $page['breadcrumbs'][] = array('name'=>$page['title'], 'url'=>$args['base_url']);
    }

    $ciniki['response']['head']['og']['url'] = $args['domain_base_url'];

    //
    // Load business settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Check for image format
    //
/*    $thumbnail_format = 'square-cropped';
    $thumbnail_padding_color = '#ffffff';
    if( isset($settings['page-calendars-thumbnail-format']) && $settings['page-calendars-thumbnail-format'] == 'square-padded' ) {
        $thumbnail_format = $settings['page-calendars-thumbnail-format'];
        if( isset($settings['page-calendars-thumbnail-padding-color']) && $settings['page-calendars-thumbnail-padding-color'] != '' ) {
            $thumbnail_padding_color = $settings['page-calendars-thumbnail-padding-color'];
        } 
    } */

    $weekday_start = isset($settings['page-calendars-weekday-start']) ? $settings['page-calendars-weekday-start'] : 0;
    $start_year = isset($settings['page-calendars-start-year']) ? $settings['page-calendars-start-year'] : 2015;
    $future_years = isset($settings['page-calendars-future-years']) ? $settings['page-calendars-future-years'] : 2;

    //
    // This module is not designed for public display of calendars way in the past or future
    // This helps prevent hacking attempts by entering large year numbers and blowing up the database
    //
    $ltz_sdt = new DateTime('now', new DateTimezone($intl_timezone));

    //
    // Set the last year of valid calendars
    //
    $end_year = $ltz_sdt->format('Y') + $future_years;

    if( isset($args['uri_split'][0]) && is_numeric($args['uri_split'][0]) ) {
        $year = intval($args['uri_split'][0]);

        //
        // Validate the year a valid year for a calendar, between their start and end years
        //
        if( $year < $start_year || $year > $end_year ) {
            return array('stat'=>'404', 'err'=>array('code'=>'ciniki.calendars.9', 'msg'=>'Invalid year'));
        }
        if( isset($args['uri_split'][1]) && is_numeric($args['uri_split'][1]) ) {
            $month = intval($args['uri_split'][1]);
            if( $month < 1 || $month > 12 ) {
                return array('stat'=>'404', 'err'=>array('code'=>'ciniki.calendars.9', 'msg'=>'Invalid year'));
            }
        } else {
            $month = $ltz_sdt->format('m');
        }
    } else {
        $year = $ltz_sdt->format('Y');
        $month = $ltz_sdt->format('m');
    }

    //
    // Setup the date and time for business timezone
    //
    $ltz_sdt->setDate($year, $month, 1);
    $ltz_sdt->setTime(0, 0, 0);
    $ltz_edt = clone $ltz_sdt;
    $ltz_edt->add(new DateInterval('P1M'));
    $ltz_edt->sub(new DateInterval('P1D'));

    $month_label = $ltz_sdt->format('F Y');

    //
    // Setup the date and time in UTC
    //
    $utc_sdt = clone $ltz_sdt;
    $utc_sdt->setTimezone(new DateTimezone('UTC'));
    $utc_edt = clone $ltz_edt;
    $utc_edt->setTimezone(new DateTimezone('UTC'));

    //
    // Remove the 'page-calendars-' prefix from all options
    //
    $stripped_settings = array();
    foreach($settings as $key => $value) {  
        if( strncmp('page-calendars-', $key, 15) == 0 ) {
            $stripped_settings[str_replace('page-calendars-', '',$key)] = $value;
        }
    }

    //
    // Load the calendar items
    //
    $legend = array();
    $items = array();
    foreach($ciniki['business']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'web', 'calendarsWebItems');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $stripped_settings, $business_id, array(
                'ltz_start'=>$ltz_sdt,
                'ltz_end'=>$ltz_edt,
                'utc_start'=>$utc_sdt,
                'utc_end'=>$utc_edt,
                ));
            if( isset($rc['items']) ) {
                foreach($rc['items'] as $dt => $day) {
                    if( !isset($items[$dt]) ) {
                        $items[$dt] = $day;
                    } else {
                        $items[$dt]['items'] = array_merge($items[$dt]['items'], $day['items']);
                    }
                }
            }
            if( isset($rc['legend']) && count($rc['legend']) ) {
                $legend = array_merge($legend, $rc['legend']);
            }
        }
    }

    //
    // Setup previous and next dates
    //
    $prev_year = $ltz_sdt->format('Y');
    $prev_month = $ltz_sdt->format('n');
    if( $prev_month <= 1 ) {
        $prev_year--;
        $prev_month = 12;
    } else {
        $prev_month--;
    }
    $next_year = $ltz_sdt->format('Y');
    $next_month = $ltz_sdt->format('n');
    if( $next_month >= 12 ) {
        $next_year++;
        $next_month = 1;
    } else {
        $next_month++;
    }

    //
    // Setup the next and previous urls
    //
    if( $prev_year >= $start_year ) {
        $prev_url = $args['base_url'] . '/' . $prev_year . '/' . $prev_month;
    } else {
        $prev_url = '';
    }
    if( $next_year <= $end_year ) {
        $next_url = $args['base_url'] . '/' . $next_year . '/' . $next_month;
    } else {
        $next_url = '';
    }
    $page['blocks'][] = array('type'=>'calendar', 
        'section' => 'ciniki-calendars',
        'weekday_start' => $weekday_start,
        'start_year' => $start_year,
        'end_year' => $end_year,
        'calendar_label' => $month_label,
        'prev_url' => $prev_url, 
        'next_url' => $next_url,
        'legend_top' => $legend,
        'legend_bottom' => $legend,
        'start'=>$ltz_sdt, 
        'end'=>$ltz_edt, 
        'items'=>$items,
        'display'=>(isset($settings['page-calendars-display-format']) ? $settings['page-calendars-display-format'] : 'list'),
        );

    return array('stat'=>'ok', 'page'=>$page);
}
?>
