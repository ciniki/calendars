<?php
//
// Description
// -----------
// This function will return the list of options for the module that can be set for the website.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure.
// tnid:     The ID of the tenant to get foodmarket web options for.
//
// args:            The possible arguments for posts
//
//
// Returns
// -------
//
function ciniki_calendars_hooks_webOptions(&$ciniki, $tnid, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['tenant']['modules']['ciniki.calendars']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.calendars.6', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }

    //
    // Get the settings from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_web_settings', 'tnid', $tnid, 'ciniki.web', 'settings', 'page-calendars');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['settings']) ) {
        $settings = array();
    } else {
        $settings = $rc['settings'];
    }

    //
    // Get the options available from other modules
    //
    $options = array();
    $options[] = array(
        'label'=>'Weekday Start',
        'setting'=>'page-calendars-weekday-start', 
        'type'=>'toggle',
        'value'=>(isset($settings['page-calendars-weekday-start'])?$settings['page-calendars-weekday-start']:'0'),
        'toggles'=>array(
            array('value'=>'0', 'label'=>'Sun'),
            array('value'=>'1', 'label'=>'Mon'),
            array('value'=>'2', 'label'=>'Tue'),
            array('value'=>'3', 'label'=>'Wed'),
            array('value'=>'4', 'label'=>'Thu'),
            array('value'=>'5', 'label'=>'Fri'),
            array('value'=>'6', 'label'=>'Sat'),
            ),
        );
    $options[] = array(
        'label'=>'First year of your calendar',
        'setting'=>'page-calendars-start-year',
        'type'=>'text',
        'size'=>'small',
        'value'=>(isset($settings['page-calendars-start-year'])?$settings['page-calendars-start-year']:''),
        );
    $options[] = array(
        'label'=>'Number of Future Years Visible',
        'setting'=>'page-calendars-future-years',
        'type'=>'text',
        'size'=>'small',
        'value'=>(isset($settings['page-calendars-future-years'])?$settings['page-calendars-future-years']:''),
        );
    $options[] = array(
        'label'=>'Display Format',
        'setting'=>'page-calendars-display-format', 
        'type'=>'toggle',
        'value'=>(isset($settings['page-calendars-display-format'])?$settings['page-calendars-display-format']:'list'),
        'toggles'=>array(
            array('value'=>'list', 'label'=>'List'),
            array('value'=>'grid', 'label'=>'Grid'),
            ),
        );

    //
    // Remove the 'page-calendars-' prefix from all options
    //
    $stripped_settings = array();
    foreach($settings as $key => $value) {  
        $stripped_settings[str_replace('page-calendars-', '',$key)] = $value;
    }

    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'calendarsWebOptions');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $tnid, array('settings'=>$stripped_settings));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.calendars.7', 'msg'=>'Unable to get options', 'pmsg'=>$rc['err']));
            }
            if( isset($rc['options']) ) {
                foreach($rc['options'] as $key => $option) {
                    $option['setting'] = 'page-calendars-' . $option['setting'];
                    $options[] = $option;
                }
            }
        }
    }

    $pages['ciniki.calendars'] = array('name'=>'Calendar', 'options'=>$options);

    return array('stat'=>'ok', 'pages'=>$pages);
}
?>
