<div class="row">
    <div class="col">
        <div class="card border-light mb-3">
            <div class="card-header"><?= t("htmlencoder_title") ?></div>
            <div class="card-body">
                <img class="service-thumb" src="<?= base_url() ?>static/img/encoder_large.png" alt="<?= escape_html(t("htmlencoder_title")) ?>" />
                <p class="card-text">
                    <?= t("htmlencoder_description") ?>
                </p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col mb-3">
                        <?php include APP."tmpl".DS."forms".DS."htmlencoder_form.php"; ?>
                    </div>
                </div>
                <div id="ajax_response"></div>
            </div>
        </div>
    </div>
</div>