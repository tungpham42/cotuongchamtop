function isset(variable) {
  if (
    typeof variable !== "undefined" &&
    variable !== "undefined" &&
    variable !== null
  ) {
    return !0;
  } else {
    return !1;
  }
}

function isEmptyOrSpaces(str) {
  return str === null || str.match(/^ *$/) !== null;
}

function removeTrailingSlash(str) {
  return str.replace(/\/+$/, "");
}

function isEmpty(selector) {
  if (!$.trim($(selector).val()).length) {
    return !0;
  } else if ($.trim($(selector).val()).length) {
    return !1;
  }
}

function isHovered(selector) {
  return $(selector + ":hover").length > 0;
}

function isClicked(selector) {
  return $(selector + ":active").length > 0;
}

function isFocused(selector) {
  return $(selector + ":focus").length > 0;
}

function isBlurred(selector) {
  return $(selector + ":not(:focus)").length > 0;
}

function isChild(parentSelector, childSelector) {
  if ($(parentSelector).find(childSelector).length > 0) {
    return !0;
  } else if (!($(parentSelector).find(childSelector).length > 0)) {
    return !1;
  }
}

function interval(duration, fn) {
  this.baseline = undefined;
  this.run = function () {
    if (this.baseline === undefined) {
      this.baseline = new Date().getTime();
    }
    fn();
    var end = new Date().getTime();
    this.baseline += duration;
    var nextTick = duration - (end - this.baseline);
    if (nextTick < 0) {
      nextTick = 0;
    }
    (function (i) {
      i.timer = setTimeout(function () {
        i.run(end);
      }, nextTick);
    })(this);
  };
  this.stop = function () {
    clearTimeout(this.timer);
  };
}

function accurateInterval(func, interval, opts) {
  if (!opts) opts = {};
  var clear, nextAt, timeout, wrapper, now;
  now = new Date().getTime();
  nextAt = now;
  if (opts.aligned) {
    nextAt += interval - (now % interval);
  }
  if (!opts.immediate) {
    nextAt += interval;
  }
  timeout = null;
  wrapper = function wrapper() {
    var scheduledTime = nextAt;
    nextAt += interval;
    timeout = setTimeout(wrapper, nextAt - new Date().getTime());
    func(scheduledTime);
  };
  clear = function clear() {
    return clearTimeout(timeout);
  };
  timeout = setTimeout(wrapper, nextAt - new Date().getTime());
  return {
    clear: clear,
  };
}
$.fn.timedDisable = function (time) {
  if (time == null) {
    time = 5000;
  }
  var seconds = Math.ceil(time / 1000); // Calculate the number of seconds
  return $(this).each(function () {
    $(this).attr("disabled", "disabled");
    var disabledElem = $(this);
    var originalText = this.innerHTML; // Remember the original text content

    // append the number of seconds to the text
    disabledElem.text(originalText + " (" + seconds + ")");

    // do a set interval, using an interval of 1000 milliseconds
    //     and clear it after the number of seconds counts down to 0
    var interval = setInterval(function () {
      // decrement the seconds and update the text
      disabledElem.text(originalText + " (" + seconds + ")");
      if (seconds === 0) {
        // once seconds is 0...
        disabledElem.removeAttr("disabled").text(originalText); //reset to original text
        clearInterval(interval); // clear interval
      }
    }, 1000);
  });
};
jQuery.fn.selectText = function () {
  var doc = document;
  var element = this[0];
  console.log(this, element);
  if (doc.body.createTextRange) {
    var range = document.body.createTextRange();
    range.moveToElementText(element);
    range.select();
  } else if (window.getSelection) {
    var selection = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(element);
    selection.removeAllRanges();
    selection.addRange(range);
  }
};

function selectText(element) {
  $(element).selectText();
}

function copyToClipboard(element) {
  var $temp = $("<input>");
  var $tooltipInner = $(".tooltip-inner");
  $("body").append($temp);
  $temp.val($(element).val()).select();
  document.execCommand("copy");
  $temp.remove();
  switch ($("html").attr("lang")) {
    case "vi":
      $tooltipInner.html("Đã sao chép");
      // $.notify('Đã sao chép', 'success');
      break;
    case "en":
      $tooltipInner.html("Copied to clipboard");
      // $.notify('Copied', 'success');
      break;
    case "ja":
      $tooltipInner.html("コピーしました");
      // $.notify('Copied', 'success');
      break;
    case "ko":
      $tooltipInner.html("복사됨");
      // $.notify('Copied', 'success');
      break;
    case "zh":
      $tooltipInner.html("复制到剪贴板");
      // $.notify('Copied', 'success');
      break;
  }
}

function countChar(string, character) {
  var charRegex = new RegExp(character, "g");
  return (string.match(charRegex) || []).length;
}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function replaceAll(find, replace, str) {
  return str.replace(new RegExp(escapeRegExp(find), "g"), replace);
}

function getUrlVars() {
  var vars = [],
    hash;
  var hashes = window.location.href
    .slice(window.location.href.indexOf("?") + 1)
    .split("&");
  var hashesLength = hashes.length;
  for (var i = 0; i < hashesLength; ++i) {
    hash = hashes[i].split("=");
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
  }
  return vars;
}

function dateDiff(start, end) {
  var start_ts = new Date(start);
  var end_ts = new Date(end);
  var diff = end_ts.getTime() - start_ts.getTime();
  return Math.round(diff / 86400000);
}

function isDate(txtDate) {
  var currVal = txtDate;
  if (currVal === "") {
    return !1;
  }
  var rxDatePattern = /^(\d{4})(-)(\d{2})(-)(\d{2})$/;
  var dtArray = currVal.match(rxDatePattern);
  if (dtArray === null) {
    return !1;
  }
  dtMonth = dtArray[3];
  dtDay = dtArray[5];
  dtYear = dtArray[1];
  if (dtMonth < 1 || dtMonth > 12) {
    return !1;
  } else if (dtDay < 1 || dtDay > 31) {
    return !1;
  } else if (
    (dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) &&
    dtDay == 31
  ) {
    return !1;
  } else if (dtMonth == 2) {
    var isleap = dtYear % 4 === 0 && (dtYear % 100 !== 0 || dtYear % 400 === 0);
    if (dtDay > 29 || (dtDay == 29 && !isleap)) {
      return !1;
    }
  }
  return !0;
}

function pad(s) {
  return s < 10 ? "0" + s : s;
}

function dateFromISO(isostr) {
  var parts = isostr.match(/\d+/g);
  return new Date(parts[0], parts[1] - 1, parts[2]);
}

function convertDate(inputFormat) {
  var d =
    typeof inputFormat == "string"
      ? dateFromISO(inputFormat)
      : new Date(inputFormat);
  return [d.getFullYear(), pad(d.getMonth() + 1), pad(d.getDate())].join("-");
}

function maskInput(input, textbox, location, delimiter) {
  var locs = location.split(",");
  var locsLength = locs.length;
  var inputLength = input.length;
  for (var delimCount = 0; delimCount <= locsLength; ++delimCount) {
    for (
      var inputCharCount = 0;
      inputCharCount <= inputLength;
      ++inputCharCount
    ) {
      if (inputCharCount == locs[delimCount]) {
        if (input.substring(inputCharCount, inputCharCount + 1) != delimiter) {
          input =
            input.substring(0, inputCharCount) +
            delimiter +
            input.substring(inputCharCount, input.length);
        }
      }
    }
  }
  textbox.val(input);
}

function randomFromTo(from, to) {
  return Math.floor(Math.random() * (to - from + 1) + from);
}

function retDate(dateObj) {
  var formatted = "";
  var pm = !1;
  formatted = dateObj.getHours();
  if (dateObj.getMinutes() < 10) {
    formatted = formatted + ":0" + dateObj.getMinutes();
  } else {
    formatted = formatted + ":" + dateObj.getMinutes();
  }
  return formatted;
}

function analogClock() {}
analogClock.prototype.run = function () {
  var date = new Date();
  var second = date.getSeconds() * 6;
  var minute = date.getMinutes() * 6 + second / 60;
  var hour = ((date.getHours() % 12) / 12) * 360 + 90 + minute / 12;
  $("#hour").css("transform", "rotate(" + hour + "deg)");
  $("#minute").css("transform", "rotate(" + minute + "deg)");
  $("#second").css("transform", "rotate(" + second + "deg)");
};
$.fn.textWidth = function () {
  var htmlOrg = $(this).html();
  var htmlCalc = "<span>" + htmlOrg + "</span>";
  $(this).html(htmlCalc);
  var width = $(this).find("span:first").width();
  $(this).html(htmlOrg);
  return width;
};
$.fn.longTap = function (options) {
  options = $.extend(
    {
      delay: 1000,
      onRelease: null,
    },
    options
  );
  var eventType = {
    mousedown: "ontouchstart" in window ? "touchstart" : "mousedown",
    mouseup: "ontouchend" in window ? "touchend" : "mouseup",
  };
  return this.each(function () {
    $(this)
      .on(eventType.mousedown + ".longtap", function () {
        $(this).data("touchstart", +new Date());
      })
      .on(eventType.mouseup + ".longtap", function (e) {
        var now = +new Date(),
          than = $(this).data("touchstart");
        now - than >= options.delay &&
          options.onRelease &&
          options.onRelease.call(this, e);
      });
  });
};
lang = $("body").attr("lang");
dobText = "";
fullnameText = "";
dobTexts = {
  vi: "Ngày sinh",
  en: "Date of birth",
  ru: "Дата рождения",
  es: "Fecha de nacimiento",
  zh: "出生日期",
  ja: "生まれた日",
};
fullnameTexts = {
  vi: "Họ tên",
  en: "Full name",
  ru: "Полное имя",
  es: "Nombre",
  zh: "全名",
  ja: "フルネーム",
};

function disableInput(fieldName) {
  $("#" + fieldName)
    .on("keypress", function (e) {
      return !1;
    })
    .on("keyup", function (e) {
      return !1;
    })
    .on("keydown", function (e) {
      return !1;
    });
}

function disableHyphen(fieldName) {
  $("#" + fieldName)
    .on("keypress", function (e) {
      if (e.which == 45) {
        return !1;
      }
    })
    .on("keyup", function (e) {
      if (e.which == 189 || e.which == 173 || e.which == 109) {
        return !1;
      }
    })
    .on("keydown", function (e) {
      if (e.which == 189 || e.which == 173 || e.which == 109) {
        return !1;
      }
    });
}

function updateHeadTitle(langCode) {
  ajaxData = {
    lang: langCode,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-p"))) {
    ajaxData.p = $("span#variables").attr("data-p");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/head_title.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("head").find("title").html(data);
  });
}

function updateHeadTitleBirthday(langCode, time) {
  ajaxData = {
    lang: langCode,
    time: time,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/head_title_birthday.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("head").find("title").html(data);
  });
}

function updateHeadingH1(langCode) {
  ajaxData = {
    lang: langCode,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-p"))) {
    ajaxData.p = $("span#variables").attr("data-p");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/heading_h1.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("h1#heading").html(data);
  });
}

function updateHeadingH1Birthday(langCode, time) {
  ajaxData = {
    lang: langCode,
    time: time,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/heading_h1_birthday.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("h1#heading").html(data);
  });
}

function updateHeadDescription(langCode) {
  ajaxData = {
    lang: langCode,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/head_description.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("head").find('meta[name="description"]').attr("content", data);
  });
}

function updateHeadDescriptionBirthday(langCode, time) {
  ajaxData = {
    lang: langCode,
    time: time,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  $.ajax({
    url: "/triggers/text/head_description_birthday.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $("head").find('meta[name="description"]').attr("content", data);
  });
}

function updateInputInterface(inputId, langCode) {
  ajaxData = {
    input_id: inputId,
    lang: langCode,
  };
  $.ajax({
    url: "/triggers/text/input_interface.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $('input[id="' + inputId + '"].translate').attr("placeholder", data);
  });
}

function updateButtonInterface(buttonId, langCode) {
  ajaxData = {
    button_id: buttonId,
    lang: langCode,
  };
  $.ajax({
    url: "/triggers/text/button_interface.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $('a[id="' + buttonId + '"].translate')
      .find("span")
      .html(data);
  });
}

function updateChromeWebstoreItem(langCode) {
  ajaxData = {
    lang: langCode,
  };
  $.ajax({
    url: "/triggers/text/chrome_webstore_item.php",
    type: "GET",
    cache: !1,
    data: ajaxData,
    dataType: "text",
  }).done(function (data) {
    $('link[rel="chrome-webstore-item"]').attr("href", data);
  });
}

function updateExplanation(langCode) {
  if ($("#explanation").length) {
    $.ajax({
      url: "/triggers/html/explanation.php",
      type: "GET",
      cache: !1,
      data: {
        lang: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      $("#explanation").html(data);
    });
  }
}

function updateIntroduction(langCode) {
  if ($("#introduction").length) {
    $.ajax({
      url: "/triggers/html/intro.php",
      type: "GET",
      cache: !1,
      data: {
        lang: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      $("#introduction").html(data);
    });
  }
}

function updateProverbsList(langCode) {
  if ($("#proverbs_wrapper").length) {
    $.ajax({
      url: "/triggers/html/proverbs_list.php",
      type: "GET",
      cache: !1,
      data: {
        page: "1",
        lang: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      $("#proverbs_wrapper").html(data);
    });
  }
}

function updateTextInterface(langCode) {
  ajaxData = {
    lang: langCode,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-p"))) {
    ajaxData.p = $("span#variables").attr("data-p");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  var headTitleAjaxCall = $.ajax({
      url: "/triggers/text/head_title.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    headingH1AjaxCall = $.ajax({
      url: "/triggers/text/heading_h1.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    headDescriptionAjaxCall = $.ajax({
      url: "/triggers/text/head_description.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    chromeWebstoreItemAjaxCall = $.ajax({
      url: "/triggers/text/chrome_webstore_item.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    youtubeAjaxCall = $.ajax({
      url: "/triggers/text/youtube.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    });
  $.when(
    headTitleAjaxCall,
    headingH1AjaxCall,
    headDescriptionAjaxCall,
    chromeWebstoreItemAjaxCall,
    youtubeAjaxCall
  ).done(function (
    headTitleAjaxResponse,
    headingH1AjaxResponse,
    headDescriptionAjaxResponse,
    chromeWebstoreItemAjaxResponse,
    youtubeAjaxResponse
  ) {
    $("head").find("title").html(headTitleAjaxResponse[0]);
    $("h1#heading").html(headingH1AjaxResponse[0]);
    $("head")
      .find('meta[name="description"]')
      .attr("content", headDescriptionAjaxResponse[0]);
    $('link[rel="chrome-webstore-item"]').attr(
      "href",
      chromeWebstoreItemAjaxResponse[0]
    );
    $("#youtube")
      .find("iframe")
      .attr("src", "https://www.youtube.com/embed/" + youtubeAjaxResponse[0]);
  });
}

function updateBirthdayTextInterface(langCode, time) {
  ajaxData = {
    lang: langCode,
    time: time,
  };
  if (isset($("span#variables").attr("data-q"))) {
    ajaxData.q = $("span#variables").attr("data-q");
  }
  if (isset($("span#variables").attr("data-dob"))) {
    ajaxData.dob = $("span#variables").attr("data-dob");
  }
  if (isset($("span#variables").attr("data-fullname"))) {
    ajaxData.fullname = $("span#variables").attr("data-fullname");
  }
  var headTitleBirthdayAjaxCall = $.ajax({
      url: "/triggers/text/head_title_birthday.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    headingH1BirthdayAjaxCall = $.ajax({
      url: "/triggers/text/heading_h1_birthday.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    }),
    headDescriptionBirthdayAjaxCall = $.ajax({
      url: "/triggers/text/head_description_birthday.php",
      type: "GET",
      cache: !1,
      data: ajaxData,
      dataType: "text",
    });
  $.when(
    headTitleBirthdayAjaxCall,
    headingH1BirthdayAjaxCall,
    headDescriptionBirthdayAjaxCall
  ).done(function (
    headTitleBirthdayAjaxResponse,
    headingH1BirthdayAjaxResponse,
    headDescriptionBirthdayAjaxResponse
  ) {
    $("head").find("title").html(headTitleBirthdayAjaxResponse[0]);
    $("h1#heading").html(headingH1BirthdayAjaxResponse[0]);
    $("head")
      .find('meta[name="description"]')
      .attr("content", headDescriptionBirthdayAjaxResponse[0]);
  });
}

function updateElementLanguage(langCode) {
  $("#pham_tung")
    .find("span.translate")
    .attr(
      "data-title",
      $("#pham_tung")
        .find("span.translate")
        .attr("data-lang-" + langCode)
    );
  $("span.translate").each(function () {
    if ($(this).parent().hasClass("html")) {
      $(this).html($(this).attr("data-lang-" + langCode));
    } else {
      $(this).text($(this).attr("data-lang-" + langCode));
    }
  });
  $("input.translate").each(function () {
    $(this)
      .text($(this).attr("data-lang-" + langCode))
      .attr("placeholder", $(this).attr("data-lang-" + langCode));
  });
  $('input[type="submit"].translate').each(function () {
    $(this).val($(this).attr("data-lang-" + langCode));
  });
  $("label.placeholder").each(function () {
    $(this).text(
      $(this)
        .next()
        .attr("data-lang-" + langCode)
    );
  });
}

function updateInterfaceLanguage(langCode) {
  var langCodes = ["vi", "en", "ru", "es", "zh", "ja"];
  var langCodeIndex = $.inArray(langCode, langCodes);
  if ($("#lang_bar").length && langCodeIndex != -1) {
    langCodes.splice(langCodeIndex, 1);
    remainingLangCodeIds = "";
    remainingLangCodesCount = langCodes.length;
    for (i = 0; i < remainingLangCodesCount; ++i) {
      remainingLangCodeIds += "#" + langCodes[i] + "_toggle";
      if (i < remainingLangCodesCount - 1) {
        remainingLangCodeIds += ", ";
      }
    }
    $("#" + langCode + "_toggle")
      .addClass("clicked")
      .addClass("disabled");
    $(remainingLangCodeIds).removeClass("clicked").removeClass("disabled");
    updateTextInterface(langCode);
    updateExplanation(langCode);
    updateIntroduction(langCode);
    updateProverbsList(langCode);
    updateElementLanguage(langCode);
    $("#explanation").attr("data-lang", langCode);
    $("html, body").attr("lang", langCode);
    $.cookie("BIO:lang", langCode);
    $(document).ready(function () {
      $.datepicker.setDefaults($.datepicker.regional[langCode]);
      $(".hasDatepicker").each(function () {
        $(this).datepicker("hide");
      });
    });
  }
}

function loadResults(dob, diff, isSecondary, dtChange, partnerDob, langCode) {
  if ($("#ajax-chart").length && $("#ajax-info").length) {
    ajaxData = {
      dob: dob,
      diff: diff,
      is_secondary: isSecondary,
      dt_change: dtChange,
      partner_dob: partnerDob,
      lang_code: langCode,
    };
    var chartAjaxCall = $.ajax({
        url: "/templates/chart.tpl.php",
        type: "GET",
        cache: false,
        data: ajaxData,
      }),
      infoAjaxCall = $.ajax({
        url: "/templates/info.tpl.php",
        type: "GET",
        cache: false,
        data: ajaxData,
      });
    $.when(chartAjaxCall, infoAjaxCall).done(function (
      chartAjaxResponse,
      infoAjaxResponse
    ) {
      $("#ajax-chart").html(chartAjaxResponse[0]);
      $("#ajax-info").html(infoAjaxResponse[0]);
      var date = new Date(dtChange);
      var dateString = moment(date);
      dateString.locale(langCode);
      $.notify(dateString.format("LL"), "info");
    });
  }
}

function loadExplanationChartResults(
  dob,
  diff,
  isSecondary,
  dtChange,
  partnerDob,
  langCode
) {
  if ($("#explanation_chart_results").length) {
    $.ajax({
      url: "/triggers/html/explanation_chart_results.php",
      type: "GET",
      cache: !1,
      data: {
        dob: dob,
        diff: diff,
        is_secondary: isSecondary,
        dt_change: dtChange,
        partner_dob: partnerDob,
        lang_code: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      var date = new Date(dtChange);
      var dateString = moment(date);
      dateString.locale(langCode);
      $.notify(dateString.format("LLLL"));
      $("#explanation_chart_results").html(data);
    });
  }
}

function loadEmbedChartResults(
  dob,
  diff,
  isSecondary,
  dtChange,
  partnerDob,
  langCode
) {
  if ($("#embed_chart_results").length) {
    $.ajax({
      url: "/triggers/html/embed_chart_results.php",
      type: "GET",
      cache: !1,
      data: {
        dob: dob,
        diff: diff,
        is_secondary: isSecondary,
        dt_change: dtChange,
        partner_dob: partnerDob,
        lang_code: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      var date = new Date(dtChange);
      var dateString = moment(date);
      dateString.locale(langCode);
      $.notify(dateString.format("LLLL"));
      $("#embed_chart_results").html(data);
    });
  }
}

function loadProverb(langCode) {
  if ($("#proverb").length) {
    $.ajax({
      url: "/templates/proverb_json.tpl.php",
      type: "GET",
      cache: !1,
      data: {
        lang_code: langCode,
      },
      dataType: "json",
    }).done(function (data) {
      $("#proverb")
        .find("#proverb_text")
        .children("span.content")
        .text(data.content);
      $("#proverb").find("#proverb_author").text(data.author);
      switch (langCode) {
        case "vi":
          $.notify("Thành ngữ mới", "success");
          break;
        case "en":
          $.notify("New proverb", "success");
          break;
      }
    });
  }
}

function loadNews(langCode) {
  if ($("#news").length) {
    $.ajax({
      url: "/triggers/html/news.php",
      type: "GET",
      cache: !1,
      global: !1,
      data: {
        lang_code: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      $("#news ul").html(data);
    });
  }
}

function loadComments(langCode) {
  if ($("#comments").length) {
    if (window.FB) {
      $("#facebook-jssdk, #fb-root").remove();
      delete window.FB;
    }
    $.ajax({
      url: "/triggers/html/comments.php",
      type: "GET",
      cache: !1,
      global: !1,
      data: {
        lang_code: langCode,
      },
      dataType: "html",
    }).done(function (data) {
      $("#comments").html(data);
    });
  }
}

function loadFeed(url, id) {
  if ($("#" + id).length) {
    $.ajax({
      url: "/triggers/html/feed.php",
      type: "GET",
      cache: !1,
      data: {
        url: url,
      },
      dataType: "html",
    }).done(function (data) {
      $("#" + id)
        .find("div.feed")
        .html(data);
    });
  }
}

function loadHash(password) {
  $.ajax({
    url: "/triggers/text/hash.php",
    type: "POST",
    cache: !1,
    data: {
      unhashed: password,
    },
    dataType: "text",
  }).done(function (data) {
    $("#hashed").val(data);
  });
}

function searchBirthdates(keyword) {
  if ($("#birthdates").length) {
    $.ajax({
      url: "/triggers/html/birthdates.php",
      type: "GET",
      cache: !1,
      data: {
        keyword: keyword,
      },
      dataType: "html",
    }).done(function (data) {
      $("#birthdates").html(data);
    });
  }
}

function showBirthdates() {
  if ($("#birthdates").length) {
    $.ajax({
      url: "/triggers/html/birthdates.php",
      type: "GET",
      cache: !1,
      dataType: "html",
    }).done(function (data) {
      $("#birthdates").html(data).show();
      if (!isEmpty("#user_birthdates_search")) {
        searchBirthdates($("#user_birthdates_search").val());
      }
    });
  }
}

function hideBirthdates() {
  $("#birthdates").html("").hide();
}

function toggleFade(selector, state) {
  switch (state) {
    case "show":
      $(selector).addClass("fade_toggle");
      break;
    case "hide":
      $(selector).removeClass("fade_toggle");
      break;
    default:
      break;
  }
}

function checkFade(selector) {
  if ($(selector).hasClass("fade_toggle")) {
    return "show";
  } else if (!$(selector).hasClass("fade_toggle")) {
    return "hide";
  }
}

function toggleCookie(
  cookieName,
  objectSelector,
  toggleSelector,
  containerSelector
) {
  if (!isset($.cookie(cookieName))) {
    if (
      checkFade(objectSelector) == "hide" &&
      !$(toggleSelector).hasClass("clicked")
    ) {
      $(toggleSelector).addClass("clicked");
      $.cookie(cookieName, "hide");
    } else if (
      checkFade(objectSelector) == "show" &&
      $(toggleSelector).hasClass("clicked")
    ) {
      $(toggleSelector).removeClass("clicked");
      $.cookie(cookieName, "show");
    }
  }
  if ($.cookie(cookieName) == "show") {
    toggleFade(objectSelector, "show");
    $(toggleSelector).addClass("clicked");
  } else if ($.cookie(cookieName) == "hide") {
    toggleFade(objectSelector, "hide");
    $(toggleSelector).removeClass("clicked");
  }
  $(containerSelector).on("click", toggleSelector, function () {
    if (
      checkFade(objectSelector) == "hide" &&
      !$(toggleSelector).hasClass("clicked")
    ) {
      toggleFade(objectSelector, "show");
      $(toggleSelector).addClass("clicked");
      $.cookie(cookieName, "show");
    } else if (
      checkFade(objectSelector) == "show" &&
      $(toggleSelector).hasClass("clicked")
    ) {
      toggleFade(objectSelector, "hide");
      $(toggleSelector).removeClass("clicked");
      $.cookie(cookieName, "hide");
    }
  });
}
function get_gravatar_image_url(
  email,
  size,
  default_image,
  allowed_rating,
  force_default
) {
  email = typeof email !== "undefined" ? email : "john.doe@example.com";
  size = size >= 1 && size <= 2048 ? size : 80;
  default_image =
    typeof default_image !== "undefined" ? default_image : "retro";
  allowed_rating = typeof allowed_rating !== "undefined" ? allowed_rating : "g";
  force_default = force_default === true ? "y" : "n";

  return (
    "https://secure.gravatar.com/avatar/" +
    CryptoJS.MD5(email.toLowerCase().trim()) +
    "?size=" +
    size +
    "&default=" +
    encodeURIComponent(default_image) +
    "&rating=" +
    allowed_rating +
    (force_default === "y" ? "&forcedefault=" + force_default : "")
  );
}
// function slugify(str) {
//   str = str.replace(/^\s+|\s+$/g, ""); // Trim leading/trailing white spaces
//   str = str.toLowerCase();

//   // Replace spaces and special characters with dashes
//   var from = "ąàáäâãåæćęèéëêìíïîłńòóöôõøśùúüûñçżź",
//     to = "aaaaaaaaceeeeeiiiilnoooooosuuuunczz",
//     i,
//     l;

//   for (i = 0, l = from.length; i < l; i++) {
//     str = str.replace(new RegExp(from.charAt(i), "g"), to.charAt(i));
//   }

//   str = str
//     .replace(/[^a-z0-9\-]/g, "-") // Replace non-alphanumeric characters with dashes
//     .replace(/-+/g, "-") // Replace multiple consecutive dashes with a single dash
//     .replace(/^-|-$/g, ""); // Remove leading/trailing dashes

//   return str;
// }
function getTitleFromUrl(url) {
  $.ajax({
    url: "/api/fetchTitle",
    method: "POST",
    data: {
      url: url,
    },
    dataType: "json",
  }).done(function (response) {
    console.log(response.title);
    return response.title;
  });
}
