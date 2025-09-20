"use strict";

var TextUtils = {
	lengthWithoutSpaces: function(text) {
		var t = text.replace(/\s/g, '');
		return t.length;
	},

	lineCount: function(text) {
		if(text.length === 0)
			return 0;
		var t = text.split("\n");
		return t.length;
	},

	wordCount: function(text) {
		if(text.length === 0)
			return 0;
		var t = text.split(/\s+/);
		return t.length;
	},

	longestLine: function(text) {
		var t = text.split(/\n/);
		var max = 0;
		for(var i = 0; i < t.length; i++) {
			if(t[i].length > max)
				max = t[i].length;
		}
		return max;
	},
};