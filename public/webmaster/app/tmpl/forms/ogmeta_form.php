<script type="text/javascript">
$(document).ready(function(){
    "use strict";
	$("#generate").on("click", ogpropertiesgenerator);
	$("#resetform").on("click", function(){
		$('#ogmeta_form')[0].reset();
		$("#ogmeta_table tbody tr:not(:first)").remove();
		$('#result').val('').hide();
	});
	
	$("#add").on("click", function(){
		$("#ogmeta_table tbody").append('<tr><td>' +
			'<input type="text" name="property[]" placeholder="<?= escape_html(t("ogproperties_property")) ?>" class="form-control"/>' +
			'</td><td>' +
			'<input type="text" name="content[]" placeholder="<?= escape_html(t("ogproperties_content")) ?>" class="form-control"/>' +
			'</td></tr>');
	});
});
</script>



    <div class="error alert alert-danger"></div>

<form method="post" id="ogmeta_form" class="mb-3">
    <table class="table table-striped" id="ogmeta_table">
    <thead>
    <tr>
    <th><?= t("ogproperties_property") ?></th>
    <th><?= t("ogproperties_content") ?></th>
    </tr>
    </thead>
    <tbody>

    <tr>
    <td>
    <input type="text" name="property[]" placeholder="property" class="form-control"/>
    </td>
    <td>
    <input type="text" name="content[]" placeholder="content" class="form-control"/>
    </td>
    </tr>

    </tbody>
    </table>

    <button id="add" class="btn btn-lg btn-success" type="button"><?= t("ogproperties_add") ?></button>
    <button id="generate" class="btn btn-lg btn-primary" type="button"><?= t("ogproperties_generate") ?></button>
    <button id="resetform" class="btn btn-lg btn-secondary" type="button"><?= t("ogproperties_reset") ?></button>
</form>

<textarea id="result" class="mb-3 form-control" rows="10" onclick="this.focus();this.select()" readonly="readonly"></textarea>
