"use strict";

function randomize(from, to)
{
	from = typeof(from) != 'undefined' ? from : 0;
	to = typeof(to) != 'undefined' ? to : from + 1;
	return Math.round(from + Math.random()*(to - from));
}

function regenerate()
{
	var chars='', password='',
		password_length = Number($('#password_length').val()),
		hyphen_length = Number($('#hyphen_length').val())+1;


	if ($('#use_pronounceable').prop('checked'))
	{
		var consonants = passopts.consonants,
			vowels = passopts.volves;

		for (var i = 1; i <= password_length; i++)
			if($('#use_hyphens').prop('checked') && i%hyphen_length===0)
				password += passopts.hyphen;
			else
				password += i%2===0?consonants.charAt(randomize(0,consonants.length-1)):vowels.charAt(randomize(0,vowels.length-1));
	}
	else if ($('#use_specified').prop('checked'))
	{
		chars = $('#specified_chars').val();

		for (i=1; i<=password_length; i++)
		{
			if($('#use_hyphens').prop('checked') && i%hyphen_length===0)
				password += '-';
			else
				password += chars.charAt(randomize(0,chars.length-1));
		}
	}
	else
	{
		if($('#use_letters').prop('checked'))
			chars += $('#use_letters').val();
		if($('#use_numbers').prop('checked'))
			chars += $('#use_numbers').val();
		if($('#use_specs').prop('checked'))
			chars += $('#use_specs').val();

		for (var i=1; i<=password_length; i++)
		{
			if($('#use_hyphens').prop('checked') && i%hyphen_length===0)
				password += passopts.hyphen;
			else
				password += chars.charAt(randomize(0,chars.length-1));
		}
	}
	$("input[id='password']").val(password);

	return false;

}

$(document).ready(function() {
	$("#regen").on("click", regenerate);
	$("#password_length").on("paste keyup", regenerate);
	$("#specified_chars").on("paste keyup", regenerate);
	$("#use_letters").on("change keyup", regenerate);
	$("#use_numbers").on("change keyup", regenerate);
	$("#use_specs").on("change keyup", regenerate);
	$("#hyphen_length").on("paste keyup", regenerate);


	$("#use_pronounceable").on("change", function() {
		if ($(this).prop('checked'))
		{
			$('#use_letters').prop('disabled', true);
			$('#use_numbers').prop('disabled', true);
			$('#use_specs').prop('disabled', true);
			$("input[id='use_specified']").prop('disabled', true);
			$("input[id='specified_chars']").prop('disabled', true);
		}
		else
		{
			$('#use_letters').prop('disabled', false);
			$('#use_numbers').prop('disabled', false);
			$('#use_specs').prop('disabled', false);
			$("input[id='use_specified']").prop('disabled', false);
		}

		regenerate();
	});

	$("input[id='use_specified']").on("change", function(){

		if ($(this).prop('checked'))
		{
			$('#use_letters').prop('disabled', true);
			$('#use_numbers').prop('disabled', true);
			$('#use_specs').prop('disabled', true);
			$("input[id='use_pronounceable']").prop('disabled', true);
			$("input[id='specified_chars']").prop('disabled', false);
		}
		else
		{
			$('#use_letters').prop('disabled', false);
			$('#use_numbers').prop('disabled', false);
			$('#use_specs').prop('disabled', false);
			$("input[id='use_pronounceable']").prop('disabled', false);
			$("input[id='specified_chars']").prop('disabled', true);
		}

		regenerate();
	});

	$("input[id='use_hyphens']").on("click", function(){
		if ($(this).prop('checked'))
		{
			$('#hyphen_length').prop('disabled', false);
		}
		else
		{
			$('#hyphen_length').prop('disabled', true);
		}
		regenerate();
	});
});