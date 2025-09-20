<h5 class="mb-3 mt-3"><?= t("location_result_title", array("{domain}"=>$domain)) ?></h5>

<div class="row mb-3">
    <div class="col-lg-6">
        <table class="table result-table">
            <tbody>
                <tr>
                    <td>
                        <?= t("location_ip_address") ?>
                    </td>
                    <td>
                        <strong><?= $data['ip'] ?></strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("location_country_name") ?>
                    </td>
                    <td>
                        <strong>
                            <?= !empty($data['country_name']) ? $data['country_name'] : t("location_unknown") ?>
                        </strong>
                        <? if($data['country_code'] !== 'XX'): ?>
                            <img src="<?= base_url() ?>static/img/flags/<?= strtolower($data['country_code']) ?>.png" alt="<?= escape_html($data['country_name']) ?>"/>
                        <? endif; ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("location_region_name") ?>
                    </td>
                    <td>
                        <strong>
                            <?= !empty($data['region_name']) ? $data['region_name'] : t("location_unknown") ?>
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("location_city_name") ?>
                    </td>
                    <td>
                        <strong>
                            <?= !empty($data['city']) ? $data['city'] : t("location_unknown") ?>
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("location_latitude") ?>
                    </td>
                    <td>
                        <strong>
                            <?= $data['latitude'] ?>
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("location_longitude") ?>
                    </td>
                    <td>
                        <strong>
                            <?= $data['longitude'] ?>
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <? if(ConfigFactory::load('app')->google_browser_key): ?>
        <div class="col-lg-6">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                        class="border-less"
                        loading="lazy"
                        allowfullscreen
                        src="https://www.google.com/maps/embed/v1/place?key=<?= ConfigFactory::load('app')->google_browser_key ?>&q=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>&zoom=8">
                </iframe>
            </div>
        </div>
    <? endif; ?>
</div>