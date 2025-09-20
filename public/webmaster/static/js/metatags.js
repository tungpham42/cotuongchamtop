"use strict";

function metataggenerator() {
	this.tags = [];
	//Initialization
	this.tags.push(namecontent('keywords', input('keywords')));
	this.tags.push(namecontent('description', input('description')));
	this.tags.push(namecontent('author', input('author')));
	this.tags.push(namecontent('copyright', input('copyright')));
	this.tags.push(namecontent('robots', select('robots')));
	this.tags.push(charset(select('charset')));
	this.tags.push(httpequiv('cache-control', select('cache')));
	this.tags.push(httpequiv('content-language', select('language')));
	this.tags.push(refresh(input('after'), input('refresh')));
	this.tags.push(httpequiv('expires', input('expires')));	
	this.tags.push(revisit(input('revisist'), select('period')));
	
	//Display meta tags
	var meta = this.tags.join("");
	if(meta.length > 0)	
		$('#result').val(meta).show();
	
	function namecontent(name, content) {
		return content.length > 0 ? '<meta name="'+ name +'" content="'+ content +'">\n' : null;
	}
	
	function httpequiv(name, content) {
		return content.length > 0 ? '<meta http-equiv="'+ name +'" content="'+ content +'">\n' : null;
	}
	
	function revisit(num, period) {
		num = parseInt(num) || 0;
		return num > 0 ? '<meta http-equiv="revisit-after" content="'+ num +' ' + period +'">\n' : null;
	}
	
	function refresh(after, to) {
		after = parseInt(after) || 0;
		return to.length > 0 ? '<meta http-equiv="refresh" content="'+ after +';'+ to +'">\n' : null;
	}
	
	function charset(content) {
		return content.length > 0 ? '<meta http-equiv="content-type" content="text/html;'+ content +'">\n' : null;
	}
	
	function input(name) {
		return $('input[name="'+ name + '"]').val();
	}
	function select(name) {
		return $('select[name="'+ name + '"]').val();
	}
}