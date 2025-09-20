<div class="error alert alert-danger"></div>

<form method="post" id="main">

    <div class="form-group">
        <label for="suggest-keyword"><?= t("suggest_keyword_label") ?></label>
        <input type="text" id="suggest-keyword" name="keyword" placeholder="<?= escape_html(t("suggest_keywords_placeholder")) ?>" value="" class="form-control"/>
    </div>

    <div class="form-group">
        <label for="suggest-lg"><?= t("suggest_language_label") ?></label>
        <input type="text" id="suggest-lg" name="lg" value="en" class="form-control"/>
    </div>

    <? require APP."tmpl".DS."forms".DS."_captcha.php"; ?>

    <button type="submit" id="go" class="btn btn-lg btn-primary"><?= t("suggest_submit") ?></button>
</form>
