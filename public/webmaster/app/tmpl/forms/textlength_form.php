<script type="text/javascript">
$(document).ready(function(){
    "use strict";
	$('#text').on('paste keyup', function (){
		var t = $("#text").val();
		$("#ch_cnt_sp").html(t.length);
		$("#ch_cnt").html(TextUtils.lengthWithoutSpaces(t));
		$("#line_cnt").html(TextUtils.lineCount(t));
		$("#wrd_cnt").html(TextUtils.wordCount(t));
		$("#lgn_ln").html(TextUtils.longestLine(t));
	});
});
</script>

<table class="table table-striped mb-3">
<thead>
<tr class="no-top-border">
<th colspan="2"><?= t("textlength_text_analysis") ?></th>
</tr>
</thead>

<tbody>
<tr>
<td><?= t("textlength_char_count_space") ?></td>
<td id="ch_cnt_sp">0</td>
</tr>

<tr>
<td><?= t("textlength_char_count_no_space") ?></td>
<td id="ch_cnt">0</td>
</tr>

<tr>
<td><?= t("textlength_line_count") ?></td>
<td id="line_cnt">0</td>
</tr>

<tr>
<td><?= t("textlength_word_count") ?></td>
<td id="wrd_cnt">0</td>
</tr>

<tr>
<td><?= t("textlength_longest_line") ?></td>
<td id="lgn_ln">0</td>
</tr>

</tbody>
</table>


<div class="form-group mb-3">
    <label for="text"><?= t("textlength_input_text") ?></label>
    <textarea id="text" rows="5" class="form-control"></textarea>
</div>