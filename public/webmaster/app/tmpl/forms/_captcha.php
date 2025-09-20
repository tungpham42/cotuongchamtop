<? if(ConfigFactory::load("captcha")->$controller): ?>
    <div class="form-group">
        <label for="captcha_img"><?= t("form_captcha") ?></label>
        <div class="input-group">
            <input type="text" id="captcha" name="captcha" class="form-control" autocomplete="off" />
            <div class="input-group-append">
                <input type="image" src="<?= controller_url('captcha'); ?>" id="captcha_img" name="captcha_img" class="captcha-image" alt="<?= escape_html(t("form_captcha")) ?>"/>
                <span class="captcha-refresh input-group-text pointer"><?= t("form_refresh") ?></span>
            </div>
        </div>

    </div>
<? endif; ?>