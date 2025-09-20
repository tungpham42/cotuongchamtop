"use strict";

function loadingLine() {
	var i = 0;
	return setInterval(function () {
		i = ++i % 4;
		$("#go").html(UI.form_loading + Array(i + 1).join("."));
	}, 500);
}

function getStatistic() {
	$('#go').prop("disabled", true);
	var interval = loadingLine();
	var query = $('#main').serialize();
	$.getJSON(document.URL + amp + query, function(response) {
		clearInterval(interval);
		$('#go').prop("disabled", false);
		$('#go').html(UI.form_submit);
		if(typeof(response.error) != "undefined") {
			$(".error").show().html(response.error);
			return false;
		}
		refreshCaptcha();
		$(".error").html('').hide();
		$('#ajax_response').html(response.html).show();
		return false;
	});
}

function refreshCaptcha() {
	$('#captcha_img').attr("src", base_url + '?r=captcha&random=' + Math.random());
	$("#captcha").val('');
}

$(document).ready(function(){
	$('#captcha_img, .captcha-refresh').on("click", function(){
		refreshCaptcha();
		return false;
	});
	
	$('#go').on("click",function(){
		getStatistic();
	  return false;
	});
	
	$('#main input').on("keydown", function(e) {
		if (e.keyCode == 13) {
			getStatistic();
	  	return false;
		}
	});
});

