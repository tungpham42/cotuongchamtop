<script>
"use strict";
var passopts = {
	volves: '<?= ConfigFactory::load("password")->volves; ?>',
	hyphen: '<?= ConfigFactory::load("password")->hyphen; ?>',
	consonants: '<?= ConfigFactory::load("password")->consonants; ?>',
}
</script>

<div class="error alert alert-danger"></div>


<form method="post">
    <div class="form-group">
        <input type="text" id="password" onclick="this.focus();this.select()" class="form-control">
    </div>

    <button class="btn btn-lg btn-primary mb-3" id="regen"><?= t("password_form_generate")?></button>


    <h5 class="mt-3 mb-3"><?= t("password_form_settings")?></h5>
    <hr>
    <div class="form-group">
        <label for="password_length"><?= t("password_form_length")?></label>
        <input type="text" class="form-control" id="password_length" value="8">
    </div>

    <? if(ConfigFactory::load("password")->letters): ?>
        <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="use_letters" value="<?= ConfigFactory::load("password")->letters; ?>" checked="checked">
            <label for="use_letters" title="<?= escape_html(ConfigFactory::load("password")->letters); ?>">
                <?= t("password_form_letters")?>
            </label>
        </div>
    <? endif; ?>

    <? if(ConfigFactory::load("password")->numbers): ?>
        <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="use_numbers" value="<?= ConfigFactory::load("password")->numbers; ?>" checked="checked">
            <label for="use_numbers" title="<?= escape_html(ConfigFactory::load("password")->numbers); ?>">
                <?= t("password_form_numbers")?>
            </label>
        </div>
    <? endif; ?>


    <? if(ConfigFactory::load("password")->symbols): ?>
        <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="use_specs" value="<?= ConfigFactory::load("password")->symbols; ?>">
            <label for="use_specs" title="<?= escape_html(ConfigFactory::load("password")->symbols); ?>">
                <?= t("password_form_symbols")?>
            </label>
        </div>
    <? endif; ?>

    <? if(ConfigFactory::load("password")->special_chars): ?>
        <div class="form-group form-check">
            <input class="form-check-input" type="checkbox" id="use_specified" value="1">
            <label for="use_specified">
                <?= t("password_form_special_characters")?>
            </label>


            <input type="text" id="specified_chars" value="<?= ConfigFactory::load("password")->special_chars; ?>" disabled="disabled" class="form-control">
        </div>
    <? endif; ?>


    <div class="form-group form-check">
        <input class="form-check-input" type="checkbox" id="use_pronounceable" value="1">
        <label for="use_pronounceable">
            <?= t("password_form_pronounceable")?>
        </label>
    </div>


    <div class="form-group form-check">
        <input class="form-check-input" type="checkbox" id="use_hyphens" value="1">
        <label for="use_hyphens">
            <?= t("password_form_separate_each") ?>
        </label>

        <div class="input-group mb-3">
            <input class="form-control" type="text" id="hyphen_length" value="2" disabled="disabled">
            <div class="input-group-append">
                <label class="input-group-text" for="hyphen_length"><?= t("password_form_separate_symbols")?></label>
            </div>
        </div>
    </div>
</form>
