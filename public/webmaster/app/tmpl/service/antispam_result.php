<h5 class="mb-3 mt-3"><?= t("antispam_result_title") ?></h5>

<div class="row mb-3">
    <div class="col">
        <img src="<?= $antispam_img ?>" alt="<?= escape_html(t("antispam_title")) ?>" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-6">
        <h6><?= t("antispam_website_link") ?></h6>
        <div class="form-group">
            <textarea rows="5" class="form-control" onclick="this.focus();this.select()" readonly="readonly">
<a href="<?= controller_url("antispam"); ?>"><img src="<?= $antispam_img ?>" alt="<?= escape_html(t("antispam_title")) ?>" /></a>
            </textarea>
        </div>
    </div>
    <div class="col-lg-6">
        <h6><?= t("antispam_forum_link") ?></h6>
        <div class="form-group">
            <textarea rows="5" class="form-control" onclick="this.focus();this.select()" readonly="readonly">
[url=<?= controller_url("antispam"); ?>][img]<?= $antispam_img ?>[/img][/url]
            </textarea>
        </div>
    </div>
</div>