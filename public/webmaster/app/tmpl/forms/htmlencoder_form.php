<div class="form-group">
    <label for="source"><?= t("htmlencoder_form_text") ?></label>
    <textarea class="form-control" id="source" rows="5"></textarea>
</div>

<button onclick="encode()" class="btn btn-lg btn-primary mb-3"><?= t("htmlencoder_btn_encode") ?></button>


<div id="result" style="display:none;">
    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="unicode"><?= t("htmlencoder_form_unicode") ?></label>
                <textarea id="unicode" name="text" rows="5" class="form-control" onclick="this.focus();this.select()" readonly="readonly"></textarea>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="hex"><?= t("htmlencoder_form_hex") ?></label>
                <textarea id="hex" name="text" rows="5" class="form-control" onclick="this.focus();this.select()" readonly="readonly"></textarea>
            </div>
        </div>
    </div>
</div>

