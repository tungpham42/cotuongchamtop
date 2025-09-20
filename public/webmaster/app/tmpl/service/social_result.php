<h5 class="mb-3 mt-3"><?= t("social_result_title", array("{domain}"=>$domain)) ?></h5>

<div class="row">
    <div class="col-lg-6">
        <table class="table result-table">
            <thead>
            <tr>
                <th>
                    <span class="facebook"></span>
                    <?= t("social_facebook") ?>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= t("social_facebook_share_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['share_count']) ?></strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("social_facebook_comment_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['comment_count']) ?></strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("social_facebook_reaction_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['reaction_count']) ?></strong>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?= t("social_facebook_comment_plugin_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['comment_plugin_count']) ?></strong>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <?= t("social_facebook_total_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['total_count']) ?></strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="col-lg-6">
        <table class="table result-table">
            <thead>
            <tr>
                <th><span class="pinterest"></span><?= t("social_pinterest") ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= t("social_pinterest_count") ?>
                    </td>
                    <td>
                        <strong><?= f($data['pinterest']) ?></strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>