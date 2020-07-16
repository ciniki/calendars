//
function ciniki_calendars_main() {
    if( M.size == 'compact' ) {
        this.selectedPanel = 'dayschedule';
    } else {
        if( M.userSettings['ui-calendar-view'] == 'day' ) {
            this.selectedPanel = 'dayschedule';
        } else {
            this.selectedPanel = 'mwschedule';
        }
    }
    this.mwnumdays = 55;
//  this.mwnumdays = 41;
//  this.mwnumdays = 27;
//  this.mwnumdays = 6;

    this.cb = null;
    this.toggleOptions = {'off':'Off', 'on':'On'};
    this.durationOptions = {'1440':'All day', '15':'15', '30':'30', '45':'45', '60':'60', '90':'1:30', '120':'2h'};
    this.durationButtons = {'-30':'-30', '-15':'-15', '+15':'+15', '+30':'+30', '+2h':'+120'};
    this.repeatOptions = {'10':'Daily', '20':'Weekly', '30':'Monthly by Date', '31':'Monthly by Weekday','40':'Yearly'};
    this.repeatIntervals = {'1':'1', '2':'2', '3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};

    //
    // Panels
    //
    this.init = function() {
        //
        // The panel to display the Calendars, which include any tenant appointments
        //
        this.dayschedule = new M.panel('Calendar',
            'ciniki_calendars_main', 'dayschedule',
            'mc', 'xlarge', 'sectioned', 'ciniki.calendars.main.dayschedule');
        this.dayschedule.data = {};
        this.dayschedule.appointments = null;
//      var dt = new Date();
//      this.dayschedule.date = dt.toISOString().substring(0,10);
        this.dayschedule.date = null;
        this.dayschedule.datePickerValue = function(s, d) { return this.date; }
        this.dayschedule.sections = {
            'datepicker':{'label':'', 'type':'datepicker', 'livesearch':'yes', 'livesearchtype':'appointments', 
                'livesearchempty':'no', 'livesearchcols':2, 'fn':'M.ciniki_calendars_main.showSelectedDayCb',
                'hint':'Search',
                'headerValues':null,
                'noData':'No appointments found',
                },
            'schedule':{'label':'', 'type':'dayschedule', 'calloffset':0,
                'start':'8:00',
                'end':'20:00',
                'notimelabel':'All Day',},
            };
//      this.dayschedule.sectionData = function(i, d) {
//          if( i == 'schedule' ) { return this.appointments; }
//          return M.ciniki_calendars_main.dayschedule.data;
//      };
        this.dayschedule.scheduleDate = function(s, d) {
            return this.date;
        };
//      this.dayschedule.appointmentDayEvents = function(i, d, day) {
//          var rsp = M.api.getJSON('ciniki.calendars.appointments', {'tnid':M.curTenantID, 'date':day});
//          if( rsp.stat == 'ok' ) {
//              return rsp.appointments;
//          }
//          return null;
//      };
        this.dayschedule.appointmentEventText = function(ev) { 
            var t = '';
            if( ev.secondary_colour != null && ev.secondary_colour != '' ) {
                t += '<span class="colourswatch" style="background-color:' + ev.secondary_colour + '">';
                if( ev.secondary_colour_text != null && ev.secondary_colour_text != '' ) { t += ev.secondary_colour_text; }
                else { t += '&nbsp;'; }
                t += '</span> '
            }
            t += ev.subject;
            if( ev.secondary_text != null && ev.secondary_text != '' ) {
                t += ' <span class="secondary">' + ev.secondary_text + '</span>';
            }
            return t;
        };
        this.dayschedule.appointmentColour = function(ev) {
            if( ev != null && ev.colour != null && ev.colour != '' ) {
                return ev.colour;
            }
            return '#77ddff';
        };
        this.dayschedule.appointmentTimeFn = function(d, t, ad) {
            if( M.curTenant.modules['ciniki.fatt'] != null ) {
                return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showDaySchedule(null,null);\',\'mc\',{\'add\':\'courses\',\'date\':\'' + d + '\',\'time\':\'' + t + '\',\'allday\':\'' + ad + '\'});';
            } else {
                return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null,null);\',\'mc\',{\'add\':\'appointment\',\'date\':\'' + d + '\',\'time\':\'' + t + '\',\'allday\':\'' + ad + '\'});';
            }
        };
        this.dayschedule.appointmentFn = function(ev) {
            if( ev.app != null ) {
                return 'M.startApp(\'' + ev.app + '\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
            } else {
                if( ev.module == 'ciniki.wineproduction' ) {
                    return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
                } 
                if( ev.module == 'ciniki.atdo' ) {
                    return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + ev.id + '\'});';
                }
                if( ev.module == 'ciniki.fatt' ) {
                    return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
                }
            }
            return '';
        };
        this.dayschedule.liveSearchCb = function(s, i, value) {
            // Send the current selected date along, so search is based on that date
            if( value != '' ) {
                var date = encodeURIComponent(M.gE(this.panelUID + '_datepicker_field').innerHTML);
                M.api.getJSONBgCb('ciniki.calendars.search', {'tnid':M.curTenantID, 'start_needle':value, 'limit':'10', 'date':date}, 
                    function(rsp) { 
                        M.ciniki_calendars_main.dayschedule.liveSearchShow('datepicker', null, M.gE(M.ciniki_calendars_main.dayschedule.panelUID + '_' + i), rsp.appointments); 
                    });
            }
            return true;
        };
        this.dayschedule.liveSearchSubmitFn = function(s, search_str) {
            M.ciniki_calendars_main.searchAppointments('M.ciniki_calendars_main.showDaySchedule(null, null);', search_str);
        };
        this.dayschedule.liveSearchResultCellColour = function(s, f, i, j, d) {
            if( s == 'datepicker' && j == 1 ) { return this.appointmentColour(d); }
            return '';
        };
        this.dayschedule.liveSearchResultClass = function(s, f, i, j, d) {
            if( s == 'datepicker' && j == 1 ) {
                return 'schedule_appointment';
            }
            return 'multiline slice_0';
        };
        this.dayschedule.liveSearchResultValue = function(s, f, i, j, d) {
            if( j == 0 ) {
                if( d.start_ts == 0 ) {
                    return 'unscheduled';
                } 
                if( d.allday == 'yes' ) {
                    return d.start_date.split(/ [0-9]+:/)[0];
                }
                return '<span class="maintext">' + d.start_date.split(/ [0-9]+:/)[0] + '</span><span class="subtext">' + d.start_date.split(/, [0-9][0-9][0-9][0-9] /)[1] + '</span>';
            } else if( j == 1 ) {
                return this.appointmentEventText(d);
            }
            return '';
        }
        this.dayschedule.liveSearchResultCellFn = function(s, f, i, j, d) {
            if( j == 0 && d.start_ts > 0 ) {
                return 'M.ciniki_calendars_main.showDaySchedule(null, \'' + d.date + '\');'; 
            }
            if( d.app != null ) {
                return 'M.startApp(\'' + d.app + '\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
            } else {
                if( d.module == 'ciniki.wineproduction' ) {
                    return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.atdo' ) {
                    return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.fatt' ) {
                    return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
            }
            return '';
        };
        this.dayschedule.addClose('Back');
        this.dayschedule.addLeftButton('mwcalendar', 'Month', 'M.ciniki_calendars_main.showMWSchedule();');

        //
        // The panel to display a whole month
        //
        this.mwschedule = new M.panel('Calendar',
            'ciniki_calendars_main', 'mwschedule',
            'mc', 'full', 'sectioned', 'ciniki.calendars.main.mwschedule');
        this.mwschedule.data = {};
        this.mwschedule.appointments = null;
        this.mwschedule.date = null;
        this.mwschedule.sections = {
            'datepicker':{'label':'', 'type':'weekpicker', 'livesearch':'yes', 'livesearchtype':'appointments', 
                'livesearchempty':'no', 'livesearchcols':2, 'fn':'M.ciniki_calendars_main.showSelectedDayCb',
                'hint':'Search',
                'headerValues':null,
                'noData':'No appointments found',
                },
            'mwschedule':{'label':'', 'type':'mwschedule', 'calloffset':0,
                'start':'8:00',
                'end':'20:00',
                'dayfn':'M.ciniki_calendars_main.mwDayCb',
                'notimelabel':'All Day',},
            };
        this.mwschedule.datePickerValue = function(s, d) { 
            return this.date;
        };
        this.mwschedule.appointmentAbbrSubject = function(ev) { 
            var t = '';
            if( ev.secondary_colour != null && ev.secondary_colour != '' ) {
                t += '<span class="colourswatch" style="background-color:' + ev.secondary_colour + '">';
                if( ev.abbr_secondary_colour_text != null && ev.abbr_secondary_colour_text != '' ) { t += ev.abbr_secondary_colour_text; }
                else if( ev.secondary_colour_text != null && ev.secondary_colour_text != '' ) { t += ev.secondary_colour_text; }
                else { t = '<span class="colourswatch" style="background-color:' + ev.secondary_colour + '; color: ' + ev.secondary_colour + '">m'; }
                t += '</span> ';
            }
            t += ((ev.abbr_subject!=null&&ev.abbr_subject!='')?ev.abbr_subject:ev.subject);
            return t;
        };
        this.mwschedule.appointmentAbbrSecondary = function(ev) {
            if( ev.abbr_secondary_text != null && ev.abbr_secondary_text != '' ) {
                return ev.abbr_secondary_text;
            } else if( ev.secondary_text != null && ev.secondary_text != '' ) {
                return ev.secondary_text;
            }
            return '';
        };
        this.mwschedule.appointmentEventAbbrText = function(ev) { 
            var t = '';
            if( ev.secondary_colour != null && ev.secondary_colour != '' ) {
                t += '<span class="colourswatch" style="background-color:' + ev.secondary_colour + '">';
                if( ev.abbr_secondary_colour_text != null && ev.abbr_secondary_colour_text != '' ) { t += ev.abbr_secondary_colour_text; }
                else if( ev.secondary_colour_text != null && ev.secondary_colour_text != '' ) { t += ev.secondary_colour_text; }
                else { t += '&nbsp;'; }
                t += '</span> ';
            }
            t += ((ev.abbr_subject!=null&&ev.abbr_subject!='')?ev.abbr_subject:ev.subject);
            return t;
        };
        this.mwschedule.appointmentColour = function(ev) {
            if( ev != null && ev.colour != null && ev.colour != '' ) {
                return ev.colour;
            }
            return '#77ddff';
        };
        this.mwschedule.newFn = function(d) {
            if( M.curTenant.modules['ciniki.fatt'] != null ) {
                return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showMWSchedule(null,null);\',\'mc\',{\'add\':\'courses\',\'date\':\'' + d + '\',\'time\':\'\',\'allday\':\'1\'});';
            } else {
                return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null,null);\',\'mc\',{\'add\':\'appointment\',\'date\':\'' + d + '\',\'time\':\'00:00\',\'allday\':\'1\'});';
            }
        };
        this.mwschedule.appointmentTimeFn = function(d, t, ad) {
            return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null,null);\',\'mc\',{\'add\':\'appointment\',\'date\':\'' + d + '\',\'time\':\'' + t + '\',\'allday\':\'' + ad + '\'});';
        };
        this.mwschedule.appointmentFn = function(ev) {
            if( ev.app != null ) {
                return 'M.startApp(\'' + ev.app + '\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
            } else {
                if( ev.module == 'ciniki.wineproduction' ) {
                    return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
                } 
                if( ev.module == 'ciniki.atdo' ) {
                    return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + ev.id + '\'});';
                }
                if( ev.module == 'ciniki.fatt' ) {
                    return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
                }
            }
            return '';
        };
        this.mwschedule.liveSearchCb = function(s, i, value) {
            // Send the current selected date along, so search is based on that date
            if( value != '' ) {
                var date = encodeURIComponent(M.gE(this.panelUID + '_datepicker_field').innerHTML);
                M.api.getJSONBgCb('ciniki.calendars.search', {'tnid':M.curTenantID, 'start_needle':value, 'limit':'10', 'date':date}, 
                    function(rsp) { 
                        M.ciniki_calendars_main.mwschedule.liveSearchShow('datepicker', null, M.gE(M.ciniki_calendars_main.mwschedule.panelUID + '_' + i), rsp.appointments); 
                    });
            }
            return true;
        };
        this.mwschedule.liveSearchSubmitFn = function(s, search_str) {
            M.ciniki_calendars_main.searchAppointments('M.ciniki_calendars_main.showMWSchedule(null, null);', search_str);
        };
        this.mwschedule.liveSearchResultCellColour = function(s, f, i, j, d) {
            if( s == 'datepicker' && j == 1 ) { return this.appointmentColour(d); }
            return '';
        };
        this.mwschedule.liveSearchResultClass = function(s, f, i, j, d) {
            if( s == 'datepicker' && j == 1 ) {
                return 'schedule_appointment';
            }
            return 'multiline slice_0';
            return '';
        };
        this.mwschedule.liveSearchResultValue = function(s, f, i, j, d) {
            if( j == 0 ) {
                if( d.start_ts == 0 ) {
                    return 'unscheduled';
                } 
                if( d.allday == 'yes' ) {
                    return d.start_date.split(/ [0-9]+:/)[0];
                }
                return '<span class="maintext">' + d.start_date.split(/ [0-9]+:/)[0] + '</span><span class="subtext">' + d.start_date.split(/, [0-9][0-9][0-9][0-9] /)[1] + '</span>';
            } else if( j == 1 ) {
                return this.appointmentEventText(d);
            }
            return '';
        }
        this.mwschedule.liveSearchResultCellFn = function(s, f, i, j, d) {
            if( j == 0 && d.start_ts > 0 ) {
                return 'M.ciniki_calendars_main.showMWSchedule(null, \'' + d.date + '\');'; 
            }
            if( d.app != null ) {
                return 'M.startApp(\'' + d.app + '\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
            } else {
                if( d.module == 'ciniki.wineproduction' ) {
                    return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.atdo' ) {
                    return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.fatt' ) {
                    return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showMWSchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
            }
            return '';
        };
        this.mwschedule.addClose('Back');
        this.mwschedule.addLeftButton('daycalendar', 'Day', 'M.ciniki_calendars_main.showDaySchedule();');

        //
        // The search panel will list all search results for a string.  This allows more advanced searching,
        // and will search the entire strings, not just start of the string like livesearch
        //
        this.search = new M.panel('Search Results',
            'ciniki_calendars_main', 'search',
            'mc', 'xlarge', 'sectioned', 'ciniki.calendars.main.search');
        this.search.sections = {
            'results':{'label':'', 'num_cols':2, 'type':'simplegrid', 'class':'dayschedule',
                'headerValues':null,
                },
        };
        this.search.data = {};
        this.search.noData = function() { return 'No appointments found'; }
        this.search.cellClass = function(s, i, j, d) {
            if( s == 'results' && j == 0 ) { return 'multiline slice_0';}
            if( s == 'results' && j == 1 ) { return 'schedule_appointment';}
        };
        this.search.searchResultValue = this.dayschedule.liveSearchResultValue;
        this.search.appointmentEventText = this.dayschedule.appointmentEventText;
        this.search.cellValue = function(s, i, j, d) {
            if( s == 'results' ) { return this.searchResultValue(s, null, i, j, d); }
        };
        this.search.appointmentColour = this.dayschedule.appointmentColour;
        this.search.cellColour = function(s, i, j, d) {
            if( s == 'results' && j == 1 ) { return this.appointmentColour(d); }
            return '';
        };
        this.search.cellFn = function(s, i, j, d) {
            if( s == 'results' && j == 1 ) {
                if( d.module == 'ciniki.wineproduction' ) {
                    return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.searchAppointments(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.atdo' ) {
                    return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + d.id + '\'});';
                }
                if( d.module == 'ciniki.fatt' ) {
                    return 'M.startApp(\'ciniki.fatt.offerings\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.id + '\'});';
                }
            }
            return '';
        };
        this.search.sectionData = function(s) { 
            if( s == 'results' ) { return this.data; }
            return null;
        };
        this.search.addClose('Back');
    }

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_calendars_main', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.cb = cb;
        this.mwschedule.cb = cb;
        this.dayschedule.cb = cb;
        if( args.appointment_id != null && args.appointment_id != '' ) {
            this.showAppointment(cb, args.appointment_id);
        } else if( args.date != null ) {
            this.showSelectedDay(cb, args.date);
        } else if( args.search != null && args.search != '' ) {
            this.searchAppointments(cb, args.search);
        } else if( args.add != null && args.add == 'yes' ) {
            this.showAdd(cb);
        } else {
            this.showSelectedDay(cb);
        }
    }

    this.showDaySchedule = function(cb, scheduleDate) {
        this.selectedPanel = 'dayschedule';
        if( scheduleDate != null ) {
            this.dayschedule.date = scheduleDate;
        } else if( this.dayschedule.date == null || (M.userSettings['ui-calendar-remember-date'] != null && M.userSettings['ui-calendar-remember-date'] != 'yes') ) {
            var dt = new Date();
            this.dayschedule.date = dt.toISOString().substring(0,10);
        }
        M.api.getJSONCb('ciniki.calendars.appointments', 
            {'tnid':M.curTenantID, 'date':this.dayschedule.date}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_calendars_main.dayschedule;
                p.data = {'schedule':rsp.appointments};
                p.refresh();
                p.show(cb);
            });
    };

    this.mwDayCb = function(i, scheduleDate) {
        this.selectedPanel = 'dayschedule';
        this.showSelectedDay(null, scheduleDate);
    };

    this.showSelectedDayCb = function(i, scheduleDate) {
        this.showSelectedDay(null, scheduleDate);
    };
    
    this.showSelectedDay = function(cb, scheduleDate) {
        if( this.selectedPanel == 'mwschedule' ) {
            this.showMWSchedule(cb, scheduleDate);
        } else {
            this.showDaySchedule(cb, scheduleDate);
        }
    };

    this.showMWSchedule = function(cb, scheduleDate) {
        // Set the start and end dates
        if( scheduleDate != null ) {
            this.mwschedule.date = scheduleDate;
        } else if( this.mwschedule.date == null || (M.userSettings['ui-calendar-remember-date'] != null && M.userSettings['ui-calendar-remember-date'] != 'yes') ) {
            // Reset the date to current date if not currently set, or option requires it
            var dt = new Date();
            this.mwschedule.date = dt.toISOString().substring(0,10);
        }
        // Setup the start and end date for the current schedule
        var dt = new Date(this.mwschedule.date);
        dt.setHours(0);
        dt.setMinutes(0);
        dt.setSeconds(0);
        if( dt.getDay() < 7 ) {
            dt.setDate(dt.getDate() - dt.getDay());
        }
        this.mwschedule.start_date = new Date(dt.getTime());
        dt.setDate(dt.getDate() + this.mwnumdays);  // Add 5 weeks
        this.mwschedule.end_date = new Date(dt.getTime());

        this.selectedPanel = 'mwschedule';
        M.api.getJSONCb('ciniki.calendars.appointments', 
            {'tnid':M.curTenantID, 
                'start_date':this.mwschedule.start_date.getFullYear() + '-' + (this.mwschedule.start_date.getMonth()+1) + '-' + this.mwschedule.start_date.getDate(),
                'end_date':this.mwschedule.end_date.getFullYear() + '-' + (this.mwschedule.end_date.getMonth()+1) + '-' + this.mwschedule.end_date.getDate(),
                }, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_calendars_main.mwschedule;
                p.data = {'mwschedule':{}};
                // Divide appointments into each day.
                if( rsp.appointments != null ) {
                    var dt = null;
                    var apts = {};
                    for(var i in rsp.appointments) {
                        if( apts[rsp.appointments[i].date] == null ) {
                            apts[rsp.appointments[i].date] = {};
                        }
                        apts[rsp.appointments[i].date][i] = rsp.appointments[i];
                    }
                    p.data.mwschedule = apts;
                }
                p.refresh();
                p.show(cb);
            });
        
    };

    this.searchAppointments = function(cb, search_str) {
        if( cb != null ) {
            this.search.cb = cb;
        }
        if( search_str == null || search_str == 'null' ) {
            if( this.search.last_search_str != null ) {
                search_str = this.search.last_search_str;
            } else {
                search_str = '';
            }
        } else {
            this.search.last_search_str = search_str;
        }
        var rsp = M.api.getJSONCb('ciniki.calendars.search', 
            {'tnid':M.curTenantID, 'start_needle':search_str, 'limit':100, 'full':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_calendars_main.search.data = rsp.appointments;
                M.ciniki_calendars_main.search.refresh();
                M.ciniki_calendars_main.search.show(cb);
            });
    }
}
