<h5 class="mb-3 mt-3"><?= t("diagnostic_result_title", array("{domain}"=>$domain)) ?></h5>

<div class="row">
    <div class="col-lg-6">
        <table class="table result-table mb-3">
            <thead>
            <tr>
                <th><?= t("diagnostic_antivirus") ?></th>
                <th><?= t("diagnostic_diagnose") ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><p><span class="norton"></span><?= t("diagnostic_norton") ?></p></td>
                <td><img src="<?= base_url() ?>static/img/<?= $data['norton'] ?>.png" alt="<?= escape_html(t("diagnostic_norton")) ?>"/></td>
            </tr>

            <tr>
                <td><p><span class="google"></span><?= t("diagnostic_google") ?></p></td>
                <td><img src="<?= base_url() ?>static/img/<?= $data['google'] ?>.png" alt="<?= escape_html(t("diagnostic_google")) ?>" /></td>
            </tr>

            </tbody>
        </table>
    </div>
</div>
