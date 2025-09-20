<h5 class="mb-3 mt-3"><?= t("backlinks_result_title", array("{domain}"=>$domain)) ?></h5>

<div class="row mb-3">
    <div class="col-lg-6">
        <table class="table result-table">
            <thead>
            <tr>
                <th><?= t("backlinks_search_engine") ?></th>
                <th><?= t("backlinks_number_of_links") ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <span class="google"></span>
                    <?= t("backlinks_google") ?>
                </td>
                <td>
                    <strong><?= f($data['Cnt']) ?></strong>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>