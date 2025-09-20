<h5 class="mb-3 mt-3"><?= t("suggest_result_title", array("{keyword}"=>html_special_chars($keyword))) ?></h5>

<?php if(!empty($suggestions)): ?>
<table class="table table-striped table-bordered">
<tbody>
<?php foreach($suggestions as $suggestion): ?>
<tr>
<td>
    <?php echo html_special_chars($suggestion) ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p class="mb-3"><?= t("suggest_nothing_found") ?></p>
<?php endif; ?>