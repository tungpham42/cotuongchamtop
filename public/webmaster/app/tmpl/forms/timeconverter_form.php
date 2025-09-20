<script type="text/javascript">
$(document).ready(function() {
    "use strict";
	var date = new Date();
	now();
	$('input[name="unix-human"]').val(gettimestamp());
	$('input[name="month"]').val(date.getMonth() + 1);
	$('input[name="day"]').val(date.getDate());
	$('input[name="year"]').val(date.getFullYear());
	$('input[name="hour"]').val(date.getHours());
	$('input[name="min"]').val(date.getMinutes());
	$('input[name="sec"]').val(date.getSeconds());
	$('input[name="rfc-2822"]').val(date.toUTCString());
});
</script>



<div class="error alert alert-danger"></div>

<table class="table table-striped">
<thead>
<tr>
<th><?= t("timeconverter_human_readable_time") ?></th>
<th><?= t("timeconverter_seconds") ?></th>
</tr>
</thead>
<tbody>
<tr><td><?= t("timeconverter_1_min") ?></td><td><?= t("timeconverter_1_min_seconds") ?></td></tr>
<tr><td><?= t("timeconverter_1_hour") ?></td><td><?= t("timeconverter_1_hour_seconds") ?></td></tr>
<tr><td><?= t("timeconverter_1_day") ?></td><td><?= t("timeconverter_1_day_seconds") ?></td></tr>
<tr><td><?= t("timeconverter_1_week") ?></td><td><?= t("timeconverter_1_week_seconds") ?></td></tr>
<tr><td><?= t("timeconverter_1_month") ?></td><td><?= t("timeconverter_1_month_seconds") ?></td></tr>
<tr><td><?= t("timeconverter_1_year") ?></td><td><?= t("timeconverter_1_year_seconds") ?></td></tr></tbody></table>

<hr />

<p><strong><?= t("timeconverter_current_timestamp") ?></strong><span id="now"></span></p>
<button class="btn btn-sm btn-primary" type="button" id="start" onclick="startClock()">Start</button>
<button class="btn btn-sm btn-warning" type="button" id="stop" onclick="stopClock()">Stop</button>
<button class="btn btn-sm btn-secondary" type="button" id="refresh" onclick="now()">Refresh</button>

<hr/>

<p><strong><?= t("timeconverter_convert_to_human_readable") ?></strong></p>

<input class="form-control form-control-sm mb-3" type="text" name="unix-human" id="convert_to_human" value="">

<button class="btn btn-primary btn-sm mb" id="unix-human"  onclick="unixhuman()"><?= t("timeconverter_convert_btn") ?></button>

<div style="display:none;" id="unix-human-div" class="mt-3">
<p>
<?= t("timeconverter_utc") ?>: <strong><span id="gmt"></span></strong><br/>
<?= t("timeconverter_current_timezone") ?>: <strong><span id="timezone"></span></strong>
</p>
</div>

<hr />

<p><strong><?= t("timeconverter_convert_to_timestamp") ?></strong></p>

<div class="form-row">
    <div class="form-group col-md-2">
        <label for="to_timestamp_month"><?= t("timeconverter_month") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_month" name="month">
    </div>
    <div class="form-group col-md-2">
        <label for="to_timestamp_day"><?= t("timeconverter_day") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_day" name="day">
    </div>
    <div class="form-group col-md-2">
        <label for="to_timestamp_year"><?= t("timeconverter_year") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_year" name="year">
    </div>
    <div class="form-group col-md-2">
        <label for="to_timestamp_hour"><?= t("timeconverter_hour") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_hour" name="hour">
    </div>
    <div class="form-group col-md-2">
        <label for="to_timestamp_min"><?= t("timeconverter_min") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_min" name="min">
    </div>
    <div class="form-group col-md-2">
        <label for="to_timestamp_sec"><?= t("timeconverter_sec") ?></label>
        <input type="number" class="form-control form-control-sm" id="to_timestamp_sec" name="sec">
    </div>
</div>

<button class="btn btn-sm btn-primary mb-3" id="unix-human" onclick="humanunix()"><?= t("timeconverter_convert_btn") ?></button>

<div style="display:none;" id="human-unix-div">
<p>
<?= t("timeconverter_epoch_timestamp") ?>: <strong><span id="epoch-timestamp"></span></strong><br/>
<?= t("timeconverter_utc_date_time") ?>: <strong><span id="utc-date"></span></strong>
</p>
</div>

<hr/>


<p><strong><?= t("timeconverter_utc_formatted_date") ?></strong></p>

<input type="text" name="rfc-2822" class="form-control form-control-sm mb-3">
<button class="mb-3 btn btn-sm btn-primary" id="to-rfc-2822" onclick="torfc()"><?= t("timeconverter_convert_btn") ?></button>

<div style="display:none;" id="rfc-div" class="mb-3">
    <p>
        <?= t("timeconverter_epoch_timestamp") ?>: <strong><span id="rfc-timestamp"></span></strong>
    </p>
</div>

<hr/>


<p><strong><?= t("timeconverter_formatting_seconds_into_d_h_m") ?></strong></p>

<input type="text" name="sec-format" value="90061" class="form-control form-control-sm mb-3">
<button class="btn btn-sm btn-primary mb-3" id="sec-format" onclick="secformat()"><?= t("timeconverter_convert_btn") ?></button>


<div style="display:none;" id="sec-format-div">
<p>
<span id="dd"></span> <?= t("timeconverter_formatting_dd") ?>,
<span id="hh"></span> <?= t("timeconverter_formatting_hh") ?>,
<span id="mm"></span> <?= t("timeconverter_formatting_mm") ?>,
<span id="ss"></span> <?= t("timeconverter_formatting_ss") ?>.
</p>
</div>