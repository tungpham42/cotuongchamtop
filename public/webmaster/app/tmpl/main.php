<div class="row">
    <div class="col">
        <div class="card border-light mb-3">
            <div class="card-header"><?= t("main_title") ?></div>
            <div class="card-body">
                <img class="service-thumb" src="<?= base_url() ?>static/img/webmaster.png" alt="<?= escape_html(t("main_title")) ?>"/>
                <p class="card-text">
                    <?= t("main_description") ?>
                </p>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="mb-3"><?= t("nav_statistics") ?></h5>
                        <ul>
                            <?php /*
                            <li>
                                <a href="<?= controller_url('alexa') ?>">
                                    <?= t("alexa_nav_title") ?>
                                </a>
                            </li>
                            */ ?>
                            <li>
                                <a href="<?= controller_url('social') ?>">
                                    <?= t("social_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('diagnostic') ?>">
                                    <?= t("diagnostic_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('location') ?>">
                                    <?= t("location_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('search') ?>">
                                    <?= t("search_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('backlinks') ?>">
                                    <?= t("backlinks_nav_title") ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-3"><?= t("nav_seo") ?></h5>
                        <ul>
                            <li>
                                <a href="<?= controller_url('suggest') ?>">
                                    <?= t("suggest_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('antispam') ?>">
                                    <?= t("antispam_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('metatags') ?>">
                                    <?= t("metatags_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('ogproperties') ?>">
                                    <?= t("ogproperties_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('password') ?>">
                                    <?= t("password_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('hash') ?>">
                                    <?= t("hash_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('duplicate') ?>">
                                    <?= t("duplicate_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('htmlencoder') ?>">
                                    <?= t("htmlencoder_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('timeconverter') ?>">
                                    <?= t("timeconverter_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('textlength') ?>">
                                    <?= t("textlength_nav_title") ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-3"><?= t("nav_domainer") ?></h5>
                        <ul>
                            <li>
                                <a href="<?= controller_url('whois') ?>">
                                    <?= t("whois_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('dns') ?>">
                                    <?= t("dns_nav_title") ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= controller_url('headers') ?>">
                                    <?= t("headers_nav_title") ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
