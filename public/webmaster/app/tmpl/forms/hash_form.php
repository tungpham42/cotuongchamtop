<table class="table result-table">
    <tbody>
    <tr class="no-top-border">
        <td><?= t("hash_string") ?></td>
        <td><textarea id="input-string" class="form-control" rows="5"></textarea></td>
    </tr>
    <tr>
        <td class="vert-align"><?= t("hash_type") ?></td>
        <td>
            <button onclick="document.getElementById('hash-string').value = hex_md5(document.getElementById('input-string').value)" class="btn btn-primary">
                <?= t("hash_md5") ?>
            </button>
            <button onclick="document.getElementById('hash-string').value = hex_sha1(document.getElementById('input-string').value)" class="btn btn-success">
                <?= t("hash_sha1") ?>
            </button>
        </td>
    </tr>
    <tr>
        <td><?= t("hash_hash") ?></td>
        <td><input type="text" id="hash-string" class="form-control"/></td>
    </tr>
    </tbody>
</table>