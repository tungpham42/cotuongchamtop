<div class="error alert alert-danger"></div>

<form method="post" id="main">
    <div class="form-group">
        <label for="domain"><?= t("form_domain_name") ?></label>
        <input type="text" id="domain" name="domain" placeholder="<?= escape_html(ConfigFactory::load("app")->app_domain_placeholder) ?>" value="" class="form-control" />
    </div>

    <? require APP."tmpl".DS."forms".DS."_captcha.php"; ?>

    <button type="submit" id="go" class="btn btn-lg btn-primary"><?= t("form_submit") ?></button>
</form>
