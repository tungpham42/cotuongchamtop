<!DOCTYPE html>
<html lang="<?= ConfigFactory::load("app")->app_html_lang; ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="<?= base_url() ?>static/js/jquery.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>static/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>static/js/base.js"></script>
<? HtmlHead::outputJs() ?>
<link href="<?= base_url() ?>static/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?= base_url() ?>static/css/style.css" rel="stylesheet" type="text/css" />
<? HtmlHead::outputCss() ?>
<meta name="keywords" content="<?= escape_html(HtmlHead::getKeywords()) ?>" />
<meta name="description" content="<?= escape_html(HtmlHead::getDescription()) ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<? HtmlHead::outputOg() ?>

<?php if(ConfigFactory::load("app")->app_cookie_consent_enable): ?>
    <style type="text/css">
        .cc_logo { display:  none !important; }
    </style>
    <script type="text/javascript">
        window.cookieconsent_options = {
            learnMore: '<?= escape_html(t("cookie_consent_learn_more")); ?>',
            dismiss: '<?= escape_html(t("cookie_consent_dismiss")); ?>',
            message: '<?= escape_html(t("cookie_consent_message")); ?>',
            theme:'<?= escape_single_quote(ConfigFactory::load("app")->app_cookie_consent_theme) ?>',
            link: '<?= escape_single_quote(ConfigFactory::load("app")->app_cookie_consent_link) ?>',
            path: '<?= escape_single_quote(cookie_consent_path()) ?>',
            expiryDays: <?= ConfigFactory::load("app")->app_cookie_consent_expiry_days ?>
        };
    </script>
    <script type="text/javascript" src="<?= base_url() ?>static/js/cookieconsent.latest.min.js"></script>
<?php endif; ?>
<script type="text/javascript">
var base_url = '<?= base_url() ?>';
var amp = '<?= $amp; ?>';
var UI = {
    form_submit: '<?= escape_html(t("form_submit"))?>',
    form_loading: '<?= escape_html(t("form_loading"))?>',
};
</script>
<?= ConfigFactory::load("app")->app_html_head ?>
<title><?= HtmlHead::getTitle() ?></title>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="mb-3">
        <? require APP."tmpl".DS."navbar.php" ?>
    </header>

    <? if(ConfigFactory::load("app")->app_banner_top && $controller !== "404"): ?>
        <div class="container mb-3">
            <div class="row">
                <div class="col">
                    <?= ConfigFactory::load("app")->app_banner_top; ?>
                </div>
            </div>
        </div>
    <? endif; ?>

    <content class="mb-3">
        <div class="container">
            <?= $content; ?>
        </div>
    </content>

    <? if(ConfigFactory::load("app")->app_banner_bottom && $controller !== "404"): ?>
        <div class="container mb-3">
            <div class="row">
                <div class="col">
                    <?= ConfigFactory::load("app")->app_banner_bottom; ?>
                </div>
            </div>
        </div>
    <? endif; ?>

    <footer class="mt-auto">
        <div class="container">
            <div class="row">
                <div class="col">
                    <?= ConfigFactory::load("app")->app_html_footer; ?>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>