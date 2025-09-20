<script type="text/javascript">
"use strict";

$(document).ready(function(){
    $('#background-color').ColorPicker({
        onChange: function (hsb, hex, rgb) {
            $('#background-color div').css('backgroundColor', '#' + hex);
            $('input[name="background-color"]').val(hex);
        }
    });
    $('#text-color').ColorPicker({
        onChange: function (hsb, hex, rgb) {
            $('#text-color div').css('backgroundColor', '#' + hex);
            $('input[name="text-color"]').val(hex);
        }
    });
});
</script>

<div class="error alert alert-danger"></div>

<form method="post" id="main">

    <div class="form-group">
        <label for="text"><?= t("antispam_text") ?></label>
        <textarea type="text" id="text" name="text" rows="3" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="font-size"><?= t("antispam_font_size") ?></label>
        <select name="font-size" id="font-size" class="form-control">
            <? foreach(ConfigFactory::load("antispam")->font_size as $size) : ?>
                <option value="<?= $size ?>"><?= $size ?></option>
            <? endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="font-family"><?= t("antispam_font_family") ?></label>
        <select name="font-family" id="font-family" class="form-control">
            <? foreach(ConfigFactory::load("antispam")->font_family as $font_family) : ?>
                <option value="<?= $font_family ?>"><?= ucfirst($font_family) ?></option>
            <? endforeach; ?>
        </select>
    </div>

    <div class="form-row mb-3">
        <div class="col">
            <label><?= t("antispam_background_color") ?></label>
            <div id="background-color">
                <div style="background-color: #<?= ConfigFactory::load("antispam")->background ?>"></div>
            </div>
        </div>
        <div class="col">
            <label><?= t("antispam_text_color") ?></label>
            <div id="text-color">
                <div style="background-color: #<?= ConfigFactory::load("antispam")->text_color ?>"></div>
            </div>
        </div>
    </div>


    <input type="hidden" name="background-color" value="<?= ConfigFactory::load("antispam")->background ?>"/>
    <input type="hidden" name="text-color" value="<?= ConfigFactory::load("antispam")->text_color ?>"/>

    <? require APP."tmpl".DS."forms".DS."_captcha.php"; ?>

    <button type="submit" id="go" class="btn btn-lg btn-primary"><?= t("antispam_submit") ?></button>
</form>