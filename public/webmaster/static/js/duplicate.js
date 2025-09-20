"use strict";

function rmdp() {
	var separator = $("#separator").val() || " ",
			text = $("#text").val();
	if(text.length === 0)
		return false;

	var arrTracemd = text.split(separator);

	var arrNewTracemd = [];
	var seenTracemd = {};
	for(var i=0; i<arrTracemd.length; i++) {
		arrTracemd[i] = $.trim(arrTracemd[i]);
		if (!seenTracemd[arrTracemd[i]]) {
			seenTracemd[arrTracemd[i]]=true;
			arrNewTracemd.push(arrTracemd[i]);
		}
	}
	$("#res").val(arrNewTracemd.join(separator));
}