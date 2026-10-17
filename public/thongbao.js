/*!
 * Popup thông báo — Cờ Tướng
 * Đọc nội dung từ /thongbao.json (cùng thư mục public). Sửa file JSON đó để đổi thông báo,
 * không cần sửa file này. Nếu JSON lỗi hoặc "bat": false thì không hiện gì.
 */
(function () {
    "use strict";
    var JSON_URL = "/thongbao.json";

    function esc(s) {
        return String(s == null ? "" : s).replace(/[&<>"']/g, function (m) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            }[m];
        });
    }

    function seen(key) {
        try {
            return sessionStorage.getItem(key) === "1";
        } catch (e) {
            return false;
        }
    }
    function mark(key) {
        try {
            sessionStorage.setItem(key, "1");
        } catch (e) {}
    }

    function show(cfg) {
        if (!cfg || cfg.bat !== true) return;
        var now = new Date();
        if (cfg.tuNgay && now < new Date(cfg.tuNgay + "T00:00:00")) return;
        if (cfg.denNgay && now > new Date(cfg.denNgay + "T23:59:59")) return;

        var key = "thongbao:" + (cfg.tieuDe || "") + "|" + (cfg.noiDung || "");
        if (cfg.moiLanVao !== true && seen(key)) return; // mặc định: mỗi phiên chỉ hiện 1 lần

        var giay = Math.max(2, Math.min(120, Number(cfg.soGiay) || 8));

        var css = document.createElement("style");
        css.textContent =
            ".tb-ov{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;" +
            "padding:16px;background:rgba(20,2,2,.62);opacity:0;transition:opacity .35s}" +
            ".tb-ov.on{opacity:1}" +
            ".tb-box{position:relative;width:100%;max-width:470px;color:#3a1d08;border-radius:10px;" +
            'padding:30px 30px 26px;text-align:center;font-family:"Noto Serif","Times New Roman",serif;' +
            "background:" +
            "radial-gradient(ellipse at 22% 30%,rgba(60,28,6,.35) 0 6px,transparent 7px)," +
            "radial-gradient(ellipse at 78% 72%,rgba(60,28,6,.3) 0 5px,transparent 6px)," +
            "repeating-linear-gradient(176deg,rgba(90,45,12,.0) 0 7px,rgba(90,45,12,.16) 7px 9px,rgba(255,220,160,.06) 9px 13px)," +
            "repeating-linear-gradient(183deg,rgba(70,32,8,.10) 0 2px,transparent 2px 23px)," +
            "linear-gradient(180deg,#d9a866 0%,#c48a49 35%,#b47a3c 65%,#a26a31 100%);" +
            "border:10px solid #5b3010;border-image:linear-gradient(135deg,#7a4318,#3d1d07 40%,#6b3712 70%,#2f1505) 1;" +
            "box-shadow:0 22px 55px rgba(0,0,0,.65),inset 0 0 40px rgba(60,25,5,.55),inset 0 0 0 2px rgba(255,214,150,.25);" +
            "transform:translateY(16px) scale(.97);transition:transform .35s}" +
            '.tb-box:before,.tb-box:after{content:"";position:absolute;top:10px;width:12px;height:12px;border-radius:50%;' +
            "background:radial-gradient(circle at 35% 35%,#e6c58a,#7a5524 55%,#3a2208);box-shadow:0 1px 2px rgba(0,0,0,.6)}" +
            ".tb-box:before{left:12px}.tb-box:after{right:52px}" +
            ".tb-ov.on .tb-box{transform:none}" +
            ".tb-seal{width:60px;height:60px;margin:-62px auto 10px;border-radius:8px;background:#a8170f;border:3px solid #6e0c07;" +
            "display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#ffe3b0;" +
            "box-shadow:0 4px 12px rgba(0,0,0,.5),inset 0 0 8px rgba(0,0,0,.35);transform:rotate(-4deg)}" +
            ".tb-title{margin:0 0 10px;font-size:24px;letter-spacing:.08em;color:#4a1f04;text-transform:uppercase;" +
            "text-shadow:0 1px 0 rgba(255,225,170,.6),0 -1px 0 rgba(40,15,0,.45)}" +
            ".tb-body{margin:0;font-size:17.5px;line-height:1.6;white-space:pre-line;color:#3a1d08;font-weight:600;" +
            "text-shadow:0 1px 0 rgba(255,225,170,.45)}" +
            ".tb-btn{display:inline-block;margin-top:18px;padding:10px 24px;border-radius:6px;background:#8c140c;color:#ffe3b0 !important;" +
            "border:2px solid #4a0b05;font:700 15px system-ui,sans-serif;letter-spacing:.04em;text-decoration:none;cursor:pointer;" +
            "box-shadow:0 3px 0 #4a0b05,0 5px 10px rgba(0,0,0,.35)}" +
            ".tb-btn:hover{background:#a8170f}" +
            ".tb-x{position:absolute;top:4px;right:6px;width:36px;height:36px;border:0;border-radius:50%;background:transparent;" +
            "color:#4a1f04;font-size:26px;line-height:1;cursor:pointer}" +
            ".tb-x:hover{background:rgba(60,25,5,.15)}" +
            ".tb-x:focus-visible,.tb-btn:focus-visible{outline:3px solid #8c140c;outline-offset:2px}" +
            ".tb-bar{position:absolute;left:14px;right:14px;bottom:8px;height:4px;border-radius:2px;background:rgba(60,25,5,.25);overflow:hidden}" +
            ".tb-bar i{display:block;height:100%;width:100%;background:#8c140c;transform-origin:left;animation:tbShrink linear forwards}" +
            ".tb-box:hover .tb-bar i{animation-play-state:paused}" +
            ".tb-sec{margin-top:12px;font:13px system-ui,sans-serif;color:rgba(58,29,8,.75)}" +
            "@keyframes tbShrink{from{transform:scaleX(1)}to{transform:scaleX(0)}}" +
            "@media (prefers-reduced-motion:reduce){.tb-ov,.tb-box{transition:none}}";
        document.head.appendChild(css);

        var ov = document.createElement("div");
        ov.className = "tb-ov";
        ov.setAttribute("role", "dialog");
        ov.setAttribute("aria-modal", "true");
        ov.setAttribute("aria-labelledby", "tb-title");
        ov.innerHTML =
            '<div class="tb-box">' +
            '<div class="tb-seal" aria-hidden="true">' +
            esc(cfg.kyTu || "帥") +
            "</div>" +
            '<button type="button" class="tb-x" aria-label="Đóng">&times;</button>' +
            (cfg.tieuDe
                ? '<h2 class="tb-title" id="tb-title">' +
                  esc(cfg.tieuDe) +
                  "</h2>"
                : "") +
            '<p class="tb-body">' +
            esc(cfg.noiDung || "") +
            "</p>" +
            (cfg.nutChu && cfg.nutLink
                ? '<a class="tb-btn" href="' +
                  esc(cfg.nutLink) +
                  '">' +
                  esc(cfg.nutChu) +
                  "</a>"
                : "") +
            '<div class="tb-sec">Tự đóng sau <b class="tb-n">' +
            giay +
            "</b> giây</div>" +
            '<div class="tb-bar"><i style="animation-duration:' +
            giay +
            's"></i></div>' +
            "</div>";
        document.body.appendChild(ov);
        mark(key);

        var box = ov.querySelector(".tb-box"),
            n = ov.querySelector(".tb-n");
        var left = giay,
            paused = false,
            closed = false;

        function close() {
            if (closed) return;
            closed = true;
            clearInterval(timer);
            ov.classList.remove("on");
            document.removeEventListener("keydown", onKey);
            setTimeout(function () {
                ov.remove();
                css.remove();
            }, 380);
        }
        function onKey(e) {
            if (e.key === "Escape") close();
        }

        var timer = setInterval(function () {
            if (paused) return;
            left -= 1;
            if (n) n.textContent = Math.max(0, left);
            if (left <= 0) close();
        }, 1000);

        box.addEventListener("mouseenter", function () {
            paused = true;
        });
        box.addEventListener("mouseleave", function () {
            paused = false;
        });
        ov.querySelector(".tb-x").addEventListener("click", close);
        ov.addEventListener("click", function (e) {
            if (e.target === ov) close();
        });
        document.addEventListener("keydown", onKey);

        requestAnimationFrame(function () {
            ov.classList.add("on");
            ov.querySelector(".tb-x").focus();
        });
    }

    function start() {
        var delay = 0;
        fetch(JSON_URL + "?v=" + Date.now(), { cache: "no-store" })
            .then(function (r) {
                return r.ok ? r.json() : null;
            })
            .then(function (cfg) {
                if (!cfg) return;
                delay = Math.max(0, Math.min(30, Number(cfg.choSauGiay) || 1));
                setTimeout(function () {
                    show(cfg);
                }, delay * 1000);
            })
            .catch(function () {
                /* JSON lỗi hoặc không có file: không hiện gì */
            });
    }

    if (document.readyState === "loading")
        document.addEventListener("DOMContentLoaded", start);
    else start();
})();
