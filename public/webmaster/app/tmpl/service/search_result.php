<h5 class="mb-3 mt-3"><?= t("search_result_title", array("{domain}"=>$domain)) ?></h5>

<div class="row mb-3">
    <div class="col-lg-6">
        <table class="table result-table">
            <thead>
            <tr>
                <th><?= t("search_engine") ?></th>
                <th><?= t("search_number_of_pages") ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <span class="google"></span>
                    <?= t("search_google") ?>
                </td>
                <td>
                    <strong><?= f($data['google']) ?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="yahoo"></span>
                    <?= t("search_yahoo") ?>
                </td>
                <td>
                    <strong><?= f($data['yahoo']) ?></strong>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bing"></span>
                    <?= t("search_bing") ?>
                </td>
                <td>
                    <strong><?= f($data['bing']) ?></strong>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>