<script type="text/javascript">
"use strict";
$(document).ready(function() {
	$("#generate").on("click", metataggenerator);
	$("#resetform").on("click", function(){
		$('#metatags_form')[0].reset();
		$('#result').val('').hide();
	});
});
</script>

<div class="error alert alert-danger"></div>

<form method="post" id="metatags_form" class="mb-3">
<table class="table table-striped metatags-table">
<thead>
<tr>
<th><?= t("metatags_meta_tag") ?></th>
<th><?= t("metatags_meta_value") ?></th>
</tr>
</thead>
<tbody>

<tr>
<td>
<label for="meta_keywords"><?= t("metatags_form_keywords") ?></label>
</td>
<td>
<input type="text" id="meta_keywords" name="keywords" value="" class="form-control"/>
</td>
</tr>

<tr>
<td>
<label for="meta_description"><?= t("metatags_form_description") ?></label>
</td>
<td>
<input type="text" id="meta_description" name="description" value="" class="form-control"/>
</td>
</tr>

<tr>
<td>
<label for="meta_author"><?= t("metatags_form_author") ?></label>
</td>
<td>
<input type="text" id="meta_author" name="author" value="" class="form-control"/>
</td>
</tr>

<tr>
<td>
<label for="meta_copyright"><?= t("metatags_form_copyright") ?></label>
</td>
<td>
<input type="text" id="meta_copyright" name="copyright" value="" class="form-control"/>
</td>
</tr>

<tr>
<td>
<label for="meta_robots"><?= t("metatags_form_robots") ?></label>
</td>
<td>
<select id="meta_robots" name="robots" class="form-control">
<? foreach(ConfigFactory::load("metagen")->robots as $robot) : ?>
<option value="<?= $robot ?>"><?= $robot ?></option>
<? endforeach; ?>
</select>
</td>
</tr>

<tr>
<td>
<label for="meta_charset"><?= t("metatags_form_charset") ?></label>
</td>
<td>
<select id="meta_charset" name="charset" class="form-control">
<? foreach(ConfigFactory::load("metagen")->charset as $charset) : ?>
<option value="<?= $charset ?>"><?= $charset ?></option>
<? endforeach; ?>
</select>
</td>
</tr>

<tr>
<td>
<label for="meta_chache"><?= t("metatags_form_cache") ?></label>
</td>
<td>
<select id="meta_chache" name="cache" class="form-control">
<? foreach(ConfigFactory::load("metagen")->cache as $cache) : ?>
<option value="<?= $cache ?>"><?= $cache ?></option>
<? endforeach; ?>
</select>
</td>
</tr>

<tr>
<td>
<label for="meta_language"><?= t("metatags_form_language") ?></label>
</td>
<td>
<select id="meta_language" name="language" class="form-control">
<? foreach(ConfigFactory::load("metagen")->language as $id => $language) : ?>
<option value="<?= $id ?>"><?= $language ?></option>
<? endforeach; ?>
</select>
</td>
</tr>

<tr>
<td>
<label><?= t("metatags_form_refresh_to") ?></label>
</td>
<td>
    <div class="input-group mb-3">
        <input id="meta-refresh-sec" type="text" class="form-control" name="after">
        <div class="input-group-append">
            <label for="meta-refresh-sec" class="input-group-text"><?= t("metatags_form_sec") ?></label>
        </div>
    </div>

    <div class="input-group">
        <input id="meta-refresh-url" type="text" class="form-control" name="refresh">
        <div class="input-group-append">
            <label for="meta-refresh-url" class="input-group-text"><?= t("metatags_form_url") ?></label>
        </div>
    </div>
</td>
</tr>

<tr>
<td>
<label for="meta_expires"><?= t("metatags_form_expires") ?></label>
</td>
<td>
    <div class="input-group">
        <input type="text" id="meta_expires" name="expires" placeholder="<?= ConfigFactory::load("metagen")->expires ?>" class="form-control"/>
        <div class="input-group-append">
            <span class="input-group-text">
                <a href="<?= controller_url("timeconverter") ?>" target="_blank">
                    <?= t("timeconverter_nav_title") ?>
                </a>
            </span>
        </div>
    </div>

</td>
</tr>

<tr>
<td>
<label for="meta_revisist"><?= t("metatags_form_revisit_after") ?></label>
</td>
<td>
<input type="text" id="meta_revisist" class="form-control mb-3" name="revisist">
<select name="period" class="form-control">
<? foreach(ConfigFactory::load("metagen")->revisit as $revisit) : ?>
<option value="<?= $revisit ?>"><?= $revisit ?></option>
<? endforeach; ?>
</select>
</td>
</tr>
</tbody>
</table>
<button id="generate" class="btn btn-lg btn-primary" type="button"><?= t("metatags_form_generate") ?></button>
<button id="resetform" class="btn btn-lg btn-secondary" type="button"><?= t("metatags_form_reset") ?></button>

</form>

<textarea id="result" rows="10" class="form-control mb-3" onclick="this.focus();this.select()" readonly="readonly"></textarea>