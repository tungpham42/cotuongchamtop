"use strict";

var clockActive = 1;
var timerID = 0;

function gettimestamp() {
	return Math.round(new Date().getTime() / 1000.0);
}

function now(){
	$('#now').html(gettimestamp());
	if(clockActive){
		timerID = setTimeout("now()", 1000);
	}
	return false;
}
function startClock() { clockActive = 1; now(); }
function stopClock() { clockActive = 0; clearTimeout(timerID); }

function unixhuman(){
	var datum = new Date($('input[name="unix-human"]').val() * 1000);
	$('#gmt').html(datum.toUTCString());
	$('#timezone').html(datum.toLocaleString());
	$('#unix-human-div').show();
}

function humanunix() {
	var month = parseInt($('input[name="month"]').val() - 1) || 0,
			day = parseInt($('input[name="day"]').val())  || 1,
			year = parseInt($('input[name="year"]').val()) || 1970,
			hours = parseInt($('input[name="hour"]').val()) || 0,
			minutes = parseInt($('input[name="min"]').val()) || 0,
			seconds = parseInt($('input[name="sec"]').val()) || 0;
	var datum = new Date(year, month, day, hours, minutes, seconds);
	$('#epoch-timestamp').html(datum.getTime()/1000.0);
	$('#utc-date').html(datum.toUTCString());
	$('#human-unix-div').show();
}

function torfc() {
	var rfc = $('input[name="rfc-2822"]').val();
	var datum = new Date(rfc);
	$('#rfc-timestamp').html(datum.getTime() / 1000.0);
	$('#rfc-div').show();
}

function secformat() {
	var t = parseInt($('input[name="sec-format"]').val()) || 0;
	var days = parseInt(t / 86400);
	t = t-(days*86400);
	var hours = parseInt(t/3600);
	t = t-(hours*3600);
	var minutes = parseInt(t/60);
	t = t-(minutes*60);
	$('#dd').html(days);
	$('#hh').html(hours);
	$('#mm').html(minutes);
	$('#ss').html(t);
	$('#sec-format-div').show();
}