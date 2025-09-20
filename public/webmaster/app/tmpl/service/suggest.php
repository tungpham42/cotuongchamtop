<div class="row">
    <div class="col">
        <div class="card border-light mb-3">
            <div class="card-header"><?= t("suggest_title") ?></div>
            <div class="card-body">
                <img class="service-thumb" src="<?= base_url() ?>static/img/suggest.png" alt="<?= escape_html(t("suggest_title")) ?>" />
                <p class="card-text">
                    <?= t("suggest_description") ?>
                </p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <?php include APP."tmpl".DS."forms".DS."suggest_form.php"; ?>
                    </div>
                </div>
                <div id="ajax_response"></div>
            </div>
        </div>
    </div>
</div>
