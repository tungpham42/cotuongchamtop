<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>">
            <?= navbar_brand(); ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item<?= navbar_active_class("main"===$controller) ?>">
                    <a class="nav-link" href="<?= base_url() ?>"><?= t("nav_home") ?></a>
                </li>

                <li class="nav-item dropdown<?= navbar_active_class(controller_belongs_to_statistics($controller)) ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarStatistics" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= t("nav_statistics") ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarStatistics">
                        <?php /*
                        <a class="dropdown-item<?= navbar_active_class('alexa'===$controller); ?>" href="<?= controller_url('alexa') ?>">
                            <?= t("alexa_nav_title") ?>
                        </a>
                        */ ?>
                        <a class="dropdown-item<?= navbar_active_class('social'===$controller); ?>" href="<?= controller_url('social') ?>">
                            <?= t("social_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('diagnostic'===$controller); ?>" href="<?= controller_url('diagnostic') ?>">
                            <?= t("diagnostic_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('location'===$controller); ?>" href="<?= controller_url('location') ?>">
                            <?= t("location_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('search'===$controller); ?>" href="<?= controller_url('search') ?>">
                            <?= t("search_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('backlinks'===$controller); ?>" href="<?= controller_url('backlinks') ?>">
                            <?= t("backlinks_nav_title") ?>
                        </a>
                    </div>
                </li>

                <li class="nav-item dropdown<?= navbar_active_class(controller_belongs_to_seo($controller)) ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarSeo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= t("nav_seo") ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarSeo">
                        <a class="dropdown-item<?= navbar_active_class('suggest'===$controller); ?>" href="<?= controller_url('suggest') ?>">
                            <?= t("suggest_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('antispam'===$controller); ?>" href="<?= controller_url('antispam') ?>">
                            <?= t("antispam_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('metatags'===$controller); ?>" href="<?= controller_url('metatags') ?>">
                            <?= t("metatags_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('ogproperties'===$controller); ?>" href="<?= controller_url('ogproperties') ?>">
                            <?= t("ogproperties_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('password'===$controller); ?>" href="<?= controller_url('password') ?>">
                            <?= t("password_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('hash'===$controller); ?>" href="<?= controller_url('hash') ?>">
                            <?= t("hash_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('duplicate'===$controller); ?>" href="<?= controller_url('duplicate') ?>">
                            <?= t("duplicate_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('htmlencoder'===$controller); ?>" href="<?= controller_url('htmlencoder') ?>">
                            <?= t("htmlencoder_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('timeconverter'===$controller); ?>" href="<?= controller_url('timeconverter') ?>">
                            <?= t("timeconverter_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('textlength'===$controller); ?>" href="<?= controller_url('textlength') ?>">
                            <?= t("textlength_nav_title") ?>
                        </a>
                    </div>
                </li>

                <li class="nav-item dropdown<?= navbar_active_class(controller_belongs_to_domainer($controller)) ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDomainer" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= t("nav_domainer") ?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDomainer">
                        <a class="dropdown-item<?= navbar_active_class('whois'===$controller); ?>" href="<?= controller_url('whois') ?>">
                            <?= t("whois_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('dns'===$controller); ?>" href="<?= controller_url('dns') ?>">
                            <?= t("dns_nav_title") ?>
                        </a>
                        <a class="dropdown-item<?= navbar_active_class('headers'===$controller); ?>" href="<?= controller_url('headers') ?>">
                            <?= t("headers_nav_title") ?>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>