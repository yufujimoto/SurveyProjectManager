var myCalendar;

function doOnLoad() {
	myCalendar = new dhtmlXCalendarObject(["date_from","date_to"]);
	myCalendar.setDate("2013-03-10");
	myCalendar.hideTime();
	
	// init values
	byId("date_from").value = getToday();
	byId("date_to").value = getToday();
}

function setSens(id, k) {
	// update range
	if (k == "min") {
		myCalendar.setSensitiveRange(byId(id).value, null);
	} else {
		myCalendar.setSensitiveRange(null, byId(id).value);
	}
}

function byId(id) {
	return document.getElementById(id);
}

function getToday() {
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth() +1;
	var yyyy = today.getFullYear();
	
	if(dd<10) {dd='0'+dd;}
	if(mm<10) {mm='0'+mm;}
	
	return yyyy+'-'+mm+'-'+dd;
}

function newDate(uuid) {
	myCalendar = new dhtmlXCalendarObject([uuid]);
}
