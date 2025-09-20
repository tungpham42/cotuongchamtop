"use strict";

var Encoder = {
	toUnicode: function(source) {
		var result = '';
		for (var i=0; i<source.length; i++) {
			result += '&#' + source.charCodeAt(i);
		}
		return result;
	},

	toHexHTML: function(source) {
		var hexhtml = '';
		for (var i=0;i<source.length;i++) {
			if (source.charCodeAt(i).toString(16).toUpperCase().length < 2) {
				hexhtml += "&#x0" + source.charCodeAt(i).toString(16).toUpperCase() + ";";
			} else {
				hexhtml += "&#x" + source.charCodeAt(i).toString(16).toUpperCase() + ";";
			}
		}
		return hexhtml;
	},
};

function encode() {
	var source = $("#source").val();
	if(source.length === 0)
		return false;
	var unicode = Encoder.toUnicode(source);
	var hex = Encoder.toHexHTML(source);
	$("#unicode").val(
		"<script type=\"text/javascript\">//<![CDATA[\n" +
		"document.write(unescape('"+ unicode +"'));\n" +
		"//]]></script\>"
	);
	$("#hex").val(
		"<script type=\"text/javascript\">//<![CDATA[\n" +
		"document.write(unescape('"+ hex +"'));\n" +
		"//]]></script\>"
	);
	$("#result").slideDown();
}