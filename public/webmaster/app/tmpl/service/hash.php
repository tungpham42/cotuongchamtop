<div class="row">
    <div class="col">
        <div class="card border-light mb-3">
            <div class="card-header"><?= t("hash_title") ?></div>
            <div class="card-body">
                <img class="service-thumb" src="<?= base_url() ?>static/img/hash_large.png" alt="<?= escape_html(t("hash_title")) ?>" />
                <p class="card-text">
                    <?= t("hash_description") ?>
                </p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <?php include APP."tmpl".DS."forms".DS."hash_form.php"; ?>
                    </div>
                </div>
                <div id="ajax_response"></div>
            </div>
        </div>
    </div>
</div>