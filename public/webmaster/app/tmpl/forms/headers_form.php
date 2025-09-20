<div class="error alert alert-danger"></div>

<form method="post" id="main">
    <div class="form-group">
        <label for="domain"><?= t("headers_enter_domain") ?></label>
        <input type="text" id="domain" name="domain" placeholder="<?= escape_html(ConfigFactory::load("app")->app_domain_placeholder) ?>" value="" class="form-control"/>
    </div>

    <div class="form-group">
        <label for="user_agent"><?= t("headers_user_agent") ?></label>

        <select class="form-control" name="user-agent" id="user_agent">
            <? foreach(ConfigFactory::load("browser") as $id => $browser) : ?>
          <option value="<?= $id ?>"><?= $id ?></option>
            <? endforeach; ?>
        </select>

    </div>

    <div class="form-group">
        <label><?= t("headers_http_version") ?></label>
        <div class="clearfix"></div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="HTTP" value="1.1" id="http_1_1" checked>
            <label class="form-check-label" for="http_1_1"><?= t("headers_http_1_1") ?></label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="HTTP" value="1.0" id="http_1_0">
            <label class="form-check-label" for="http_1_0"><?= t("headers_http_1_0") ?></label>
        </div>
    </div>


    <div class="form-group">
        <label><?= t("headers_request_type") ?></label>
        <div class="clearfix"></div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="request" value="GET" id="request_get" checked>
            <label class="form-check-label" for="request_get"><?= t("headers_get") ?></label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="request" value="POST" id="request_post">
            <label class="form-check-label" for="request_post"><?= t("headers_post") ?></label>
        </div>
    </div>

    <div class="form-group">
        <label><?= t("headers_additional_settings") ?></label>
        <div class="clearfix"></div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="rawhtml" id="settings_raw">
            <label class="form-check-label" for="settings_raw"><?= t("headers_raw_html_view") ?></label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="gzip" id="settings_gzip">
            <label class="form-check-label" for="settings_gzip"><?= t("headers_accept_encoding_gzip") ?></label>
        </div>
    </div>

    <? require APP."tmpl".DS."forms".DS."_captcha.php"; ?>

    <button type="submit" id="go" class="btn btn-lg btn-primary"><?= t("headers_submit") ?></button>
</form>