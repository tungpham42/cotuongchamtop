<p class="w-100 text-center mt-0 mb-1">
    <i class="fad fa-external-link-alt"></i> {{ __("Mời bạn bè chơi bằng cách gửi liên kết bên dưới") }}.
</p>
<div id="copy-url" class="input-group my-1 w-50 mx-auto" data-toggle="tooltip" data-placement="bottom" data-original-title="Ấn để sao chép">
    <div class="input-group-prepend">
        <span class="input-group-text" id="url-addon"><i class="fal fa-copy"></i></span>
    </div>
    <input data-step="{{ __("6\" data-intro=\"Ấn vào đây để mời bạn bè cùng chơi") }}" type="text" class="form-control" id="url" value="{{ url()->current() }}">
</div>
<script>
    $('#copy-url').on('click', function() {
        copyToClipboard('#url');
        selectText('#url');
        $(this).tooltip('update');
    });
</script>
