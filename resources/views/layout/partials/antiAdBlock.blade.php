<div id="anti_ab" style="box-sizing: content-box;visibility: hidden">
<div class="anti_ab_background" style="box-sizing: content-box;width: 100%;height: 100%;background-color: black;opacity: 0.7;z-index: 999999998;position: fixed;top: 0%;left: 0%;"></div>
    <div class="anti_ab_window" style="box-sizing: content-box;width: 600px;height: 400px;background-color: white;border-radius: 30px;position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);z-index: 999999999;">
    <div class="circle" style="box-sizing: content-box;text-align: center;padding:10px;height: 60px;width: 60px;background-color: red;border-radius: 50%;position: absolute;left: 50%;top: 80px;transform: translate(-50%, -50%);">
        <i style="box-sizing: content-box;padding: 7px;font-size:50px;color: white" class="fa">&#xf12a;</i></div>
        
    <p style="box-sizing: content-box;width: 100%;text-align: center;font-size: 35px;font-family: Arial;font-weight: bold;color: red;padding: 120px 15px">Đã phát hiện AdBlock!</p>
        
    <p style="box-sizing: content-box;width: 100%;color: gray;text-align: center;font-family: Verdana;font-size: 18px;position: absolute;top: 210px;left: 50%;transform: translate(-50%, -50%);">Nếu bạn muốn sử dụng trang web của chúng tôi, vui lòng tắt AdBlock.</p>
            
    <a href="javascript: location.reload()" style="background-color:#ff0000;border-radius:28px;border:1px solid #ff0000;display:inline-block;cursor:pointer;color:#ffffff;font-family:Arial;font-size:20px;font-weight:bold;padding:13px 20px;text-decoration:none;text-shadow:0px 1px 0px #ff0000;position: absolute;top: 315px;left: 50%;transform: translate(-50%, -50%)">Tải lại trang</a>
    </div>
</div>
<img style="height: 0px;width: 0px" src="https://matystudios.github.io/banner_ad.png" onload="javascript: aabck()" onerror="javascript: document.getElementById('anti_ab').style.visibility = 'visible'">
<div id="anti-abck"><iframe style="position: absolute;width:0;height:0;border:0;" src="https://asacdn.com/"></iframe></div>
<script>
function aabck(){
    window.onload = function() {
        const box = document.getElementById('anti-abck');
        if (document.getElementById('anti-abck').innerHTML.indexOf('<iframe style="position: absolute;width:0;height:0;border:0;" src="https://asacdn.com/">') != -1) {
        document.getElementById('anti_ab').style.visibility = 'hidden';
        }else{
        document.getElementById('anti_ab').style.visibility = 'visible';
        }
    };
}	
</script>