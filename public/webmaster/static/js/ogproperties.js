"use strict";
function ogpropertiesgenerator() {
	var og = [];
	var content = $('input[name="content[]"]');
	
	var i = 0;
	$('input[name="property[]"]').each(function() {
    var property = $(this).val();
		var c = $(content.get(i)).val();
		og.push(ogproperty(property, c));
		i++;
	});
	
	var meta = og.join("");
	if(meta.length > 0)	
		$('#result').val(meta).show();
	
	function ogproperty(property, content) {
		return property.length > 0 ? '<meta property="og:'+ property +'" content="'+ content +'" />\n' : null;
	}
}