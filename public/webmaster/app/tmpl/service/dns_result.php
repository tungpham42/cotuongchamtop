<h5 class="mb-3 mt-3"><?= t("dns_result_title", array("{domain}"=>$domain)) ?></h5>

<? if(!empty($data['dns'])): ?>

    <? foreach($data['dns'] as $record): ?>
    <h6 class="mb-3"><?= t("dns_record_type", array("{type}"=>$record['type'])) ?></h6>
    <table class="table result-table mb-3">
        <thead class="thead-light">
            <tr>
                <th><?= t("dns_name") ?></th>
                <th><?= t("dns_value") ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach($record as $name => $value): ?>
            <tr>
            <td>
            <?= html_special_chars($name) ?>
            </td>
            <td>
            <? if(is_array($value)): ?>
            <pre>
            <?= htmlentities(print_r($value, true)); ?>
            </pre>
            <? else: ?>
            <?= html_special_chars($value) ?>
            <? endif; ?>
            </td>
            </tr>
            <? endforeach; ?>
        </tbody>
    </table>
    <? endforeach; ?>
<? else: ?>
    <p class="mb-3"><?= t("dns_no_records_found") ?></p>
<? endif; ?>
