<h5 class="mb-3 mt-3"><?= t("headers_result_title", array("{domain}"=>$domain)) ?></h5>

<table class="table table-striped">
<thead>
<tr>
<th><?= t("headers_name") ?></th>
<th><?= t("headers_value") ?></th>
</tr>
</thead>
<tbody>
<? foreach($data['headers'] as $name => $value): ?>
<tr>
<td>
<?= html_special_chars($name) ?>
</td>
<td>
<?= html_special_chars($value) ?>
</td>
</tr>
<? endforeach; ?>
</tbody>
</table>
<? if(_v($_GET, 'rawhtml')): ?>
    <h5 class="mb-3 mt-3"><?= t("headers_result_raw", array("{domain}"=>$domain, "{size}"=>$data['size'] . " KB")) ?></h5>
    <pre>
    <?= html_special_chars($data['body']) ?>
    </pre>
<? endif; ?>
