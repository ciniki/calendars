//
function ciniki_calendars_main() {
	//
	// Panels
	//
	this.dayschedule = null;
	this.add = null;

	this.cb = null;
	this.toggleOptions = {'off':'Off', 'on':'On'};
	this.durationOptions = {'1440':'All day', '15':'15', '30':'30', '45':'45', '60':'60', '90':'1:30', '120':'2h'};
	this.durationButtons = {'-30':'-30', '-15':'-15', '+15':'+15', '+30':'+30', '+2h':'+120'};
	this.repeatOptions = {'10':'Daily', '20':'Weekly', '30':'Monthly by Date', '31':'Monthly by Weekday','40':'Yearly'};
	this.repeatIntervals = {'1':'1', '2':'2', '3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};


	this.init = function() {
		//
		// The panel to display the Calendars, which include any business appointments
		//
		this.dayschedule = new M.panel('Calendar',
			'ciniki_calendars_main', 'dayschedule',
			'mc', 'xlarge', 'sectioned', 'ciniki.calendars.main.dayschedule');
		this.dayschedule.data = {};
		this.dayschedule.appointments = null;
		var dt = new Date();
		this.dayschedule.date = dt.getFullYear() + '-' + (dt.getMonth()+1) + '-' + dt.getDate();
		this.dayschedule.datePickerValue = function(s, d) { return this.date; }
		this.dayschedule.sections = {
			'datepicker':{'label':'', 'type':'datepicker', 'livesearch':'yes', 'livesearchtype':'appointments', 
				'livesearchempty':'no', 'livesearchcols':2, 'fn':'M.ciniki_calendars_main.showSelectedDay',
				'hint':'Search',
				'headerValues':null,
				'noData':'No appointments found',
				},
			'schedule':{'label':'', 'type':'dayschedule', 'calloffset':0,
				'start':'8:00',
				'end':'20:00',
				'notimelabel':'All Day',},
			};
//		this.dayschedule.sectionData = function(i, d) {
//			if( i == 'schedule' ) { return this.appointments; }
//			return M.ciniki_calendars_main.dayschedule.data;
//		};
		this.dayschedule.scheduleDate = function(s, d) {
			return this.date;
		};
//		this.dayschedule.appointmentDayEvents = function(i, d, day) {
//			var rsp = M.api.getJSON('ciniki.calendars.appointments', {'business_id':M.curBusinessID, 'date':day});
//			if( rsp.stat == 'ok' ) {
//				return rsp.appointments;
//			}
//			return null;
//		};
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
			return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null,null);\',\'mc\',{\'add\':\'appointment\',\'date\':\'' + d + '\',\'time\':\'' + t + '\',\'allday\':\'' + ad + '\'});';
		};
		this.dayschedule.appointmentFn = function(ev) {
			if( ev.module == 'ciniki.wineproduction' ) {
				return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + ev.id + '\'});';
			} 
			if( ev.module == 'ciniki.atdo' ) {
				return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + ev.id + '\'});';
				
			}
			return '';
		};
		this.dayschedule.liveSearchCb = function(s, i, value) {
			// Send the current selected date along, so search is based on that date
			if( value != '' ) {
				var date = encodeURIComponent(M.gE(this.panelUID + '_datepicker_field').innerHTML);
				M.api.getJSONBgCb('ciniki.calendars.search', {'business_id':M.curBusinessID, 'start_needle':value, 'limit':'10', 'date':date}, 
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
			if( s == 'datepicker' && j == 1 ) { return this.appointmentColour(d.appointment); }
			return '';
		};
		this.dayschedule.liveSearchResultClass = function(s, f, i, j, d) {
			if( s == 'datepicker' && j == 1 ) {
				return 'schedule_appointment';
			}
			return 'multiline slice_0';
//	if( s == 'search' && (this.sections[s].dataMaps[j] == 'order_date' 
//					|| this.sections[s].dataMaps[j] == 'start_date' 
//					|| this.sections[s].dataMaps[j] == 'racking_date' 
//					|| this.sections[s].dataMaps[j] == 'filtering_date' 
//					|| this.sections[s].dataMaps[j] == 'bottling_date' 
//					|| this.sections[s].dataMaps[j] == 'bottling_date_and_flags' 
//				)) {
//				return 'multiline aligncenter';
//			}
			return '';
		};
		this.dayschedule.liveSearchResultValue = function(s, f, i, j, d) {
			if( j == 0 ) {
				if( d.appointment.start_ts == 0 ) {
					return 'unscheduled';
				} 
				if( d.appointment.allday == 'yes' ) {
					return d.appointment.start_date.split(/ [0-9]+:/)[0];
				}
				return '<span class="maintext">' + d.appointment.start_date.split(/ [0-9]+:/)[0] + '</span><span class="subtext">' + d.appointment.start_date.split(/, [0-9][0-9][0-9][0-9] /)[1] + '</span>';
			} else if( j == 1 ) {
				return this.appointmentEventText(d.appointment);
			}
			return '';
		}
		this.dayschedule.liveSearchResultCellFn = function(s, f, i, j, d) {
			if( j == 0 && d.appointment.start_ts > 0 ) {
				return 'M.ciniki_calendars_main.showDaySchedule(null, \'' + d.appointment.date + '\');'; 
			}
			if( d.appointment.module == 'ciniki.wineproduction' ) {
				return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'appointment_id\':\'' + d.appointment.id + '\'});';
			}
			if( d.appointment.module == 'ciniki.atdo' ) {
				return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + d.appointment.id + '\'});';
			}
			return '';
		};
		this.dayschedule.addClose('Back');

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
			if( s == 'results' && j == 1 ) { return this.appointmentColour(d.appointment); }
			return '';
		};
		this.search.cellFn = function(s, i, j, d) {
			if( s == 'results' && j == 1 ) {
				if( d.appointment.module == 'ciniki.wineproduction' ) {
					return 'M.startApp(\'ciniki.wineproduction.main\',null,\'M.ciniki_calendars_main.searchAppointments(null, null);\',\'mc\',{\'appointment_id\':\'' + d.appointment.id + '\'});';
				}
				if( d.appointment.module == 'ciniki.atdo' ) {
					return 'M.startApp(\'ciniki.atdo.main\',null,\'M.ciniki_calendars_main.showDaySchedule(null, null);\',\'mc\',{\'atdo_id\':\'' + d.appointment.id + '\'});';
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
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_calendars_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		this.cb = cb;
		if( args.appointment_id != null && args.appointment_id != '' ) {
			this.showAppointment(cb, args.appointment_id);
		} else if( args.date != null ) {
			this.showDaySchedule(cb, args.date);
		} else if( args.search != null && args.search != '' ) {
			this.searchAppointments(cb, args.search);
		} else if( args.add != null && args.add == 'yes' ) {
			this.showAdd(cb);
		} else {
			this.showDaySchedule(cb, null);
		}
	}

	this.showDaySchedule = function(cb, scheduleDate) {
		if( scheduleDate != null ) {
			this.dayschedule.date = scheduleDate;
		}
		var rsp = M.api.getJSONCb('ciniki.calendars.appointments', 
			{'business_id':M.curBusinessID, 'date':this.dayschedule.date}, function(rsp) {
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

	this.showSelectedDay = function(i, scheduleDate) {
		this.showDaySchedule(null, scheduleDate);
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
			{'business_id':M.curBusinessID, 'start_needle':search_str, 'limit':100, 'full':'yes'}, function(rsp) {
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
