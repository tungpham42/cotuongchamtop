<div class="row">
    <div class="col">
        <div class="card border-light mb-3">
            <div class="card-header"><?= t("textlength_title") ?></div>
            <div class="card-body">
                <img class="service-thumb" src="<?= base_url() ?>static/img/text_large.png" alt="<?= escape_html(t("textlength_title")) ?>" />
                <p class="card-text">
                    <?= t("textlength_description") ?>
                </p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col mb-3">
                        <?php include APP."tmpl".DS."forms".DS."textlength_form.php"; ?>
                    </div>
                </div>
                <div id="ajax_response"></div>
            </div>
        </div>
    </div>
</div>