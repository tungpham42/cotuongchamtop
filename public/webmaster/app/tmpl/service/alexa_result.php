<h5 class="mb-3 mt-3"><?= t("alexa_result_title", array("{domain}"=>$domain)) ?></h5>
<table class="table result-table">
<tbody>
<tr>
<td><?= t("alexa_global_rank") ?></td>
<td>
    <strong><?= f($data['rank']) ?></strong>
    <?php if(!empty($data['delta_direction']) AND !empty($data['delta'])): ?>
        <?php if($data['delta_direction'] === "up"): ?>
            &nbsp;
            <span class="badge badge-success badge-alexa-delta">
                +&nbsp;
                <?php echo f((int) $data['delta']); ?>
            </span>
        <?php else: ?>
            <span class="badge badge-danger badge-alexa-delta">
                -&nbsp;?
                <?php echo f((int) $data['delta']); ?>
            </span>
        <?php endif; ?>
    <?php endif; ?>
</td>
</tr>

<?php if(!empty($data['country_code']) AND !empty($data['country_name']) AND $data['country_rank'] > 0): ?>
<tr>
<td>
    <?= t("alexa_local_rank", array("{country}"=>$data['country_name'])) ?>
    <img src="<?= base_url() ?>static/img/flags/<?= strtolower($data['country_code']) ?>.png" /></td>
<td>
    <strong>
        <?= f($data['country_rank']) ?>
    </strong>
</td>
</tr>
<?php endif; ?>

<tr>
<td><?= t("alexa_links_in") ?></td>
<td>
    <strong>
        <?= f($data['linksin']) ?>
    </strong>
</td>
</tr>

</tbody>
</table>


<? if(!empty($data['similar_sites'])) : ?>
    <h5 class="mb-3"><?= t("alexa_similar_sites") ?></h5>
    <ul class="list-group">
        <?php foreach($data['similar_sites'] as $similar_site): ?>
            <li class="list-group-item"><?= html_special_chars($similar_site['name']) ?></li>
        <?php endforeach; ?>
    </ul>
<? endif; ?>

<? if(!empty($data['related_keywords'])) : ?>
    <h5 class="mt-3 mb-3"><?= t("alexa_related_keywords") ?></h5>
    <ul class="list-group">
        <?php foreach($data['related_keywords'] as $related_keyword): ?>
            <li class="list-group-item"><?= html_special_chars($related_keyword) ?></li>
        <?php endforeach; ?>
    </ul>
<? endif; ?>
