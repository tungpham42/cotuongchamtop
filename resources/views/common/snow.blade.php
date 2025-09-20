<style>
span[id^='flake-'] > .fa-snowflake {
    color: #ffffff !important;
}
</style>
<script>
(function( $ ) {
    'use strict';

    $(document).on(
        'loadWPSnow',
        function( event, snowMax, snowColor, snowType, snowEntity, snowSpeed, snowMaxSize, snowMinSize, snowRefresh, snowZIndex, snowStyles ){

            var snow = [],
                i = 0,
                pos = [],
                coords = [],
                left = [],
                marginBottom,
                snowSize = snowMaxSize - snowMinSize,
                marginRight;

            function randomizeSnow(range) {
                return Math.floor(range * Math.random());
            }

            function moveSnow() {
                for (i = 0; i <= snowMax; i++) {
                    coords[i] += pos[i];
                    snow[i].posY += snow[i].sink;
                    snow[i].style.left = snow[i].posX + 2 * left[i] * Math.sin(coords[i]) + "px";
                    snow[i].style.top = snow[i].posY + "px";

                    if (snow[i].posY >= marginBottom + 2 * snow[i].size || parseInt(snow[i].style.left) > (marginRight - 3 * left[i])) {
                        snow[i].posX = randomizeSnow(marginRight - snow[i].size);
                        snow[i].posY = ( 2 * snow[i].size ) * -1;
                    }
                }

                setTimeout( moveSnow, snowRefresh );
            }

            function initSnow() {
                createSnow();
                resizeSnow();

                for (i = 0; i <= snowMax; i++) {
                    coords[i] = 0;
                    left[i] = Math.random() * 15;
                    pos[i] = 0.03 + Math.random() / 10;
                    snow[i] = document.getElementById("flake-" + i);
                    snow[i].size = randomizeSnow(snowSize) + snowMinSize;
                    snow[i].style.fontSize = snow[i].size + "px";
                    snow[i].style.fontFamily=snowType[randomizeSnow(snowType.length)];
                    snow[i].style.color = snowColor[randomizeSnow(snowColor.length)];
                    snow[i].style.position = 'absolute';
                    snow[i].style.zIndex = 2500;
                    snow[i].sink = snowSpeed * snow[i].size / 5;
                    snow[i].posX = randomizeSnow(marginRight - snow[i].size);
                    snow[i].posY = randomizeSnow( marginBottom + 2 * snow[i].size );
                    snow[i].style.left = snow[i].posX + "px";
                    snow[i].style.top = snow[i].posY + "px";
                }

                resizeSnow(); //Resize again

                moveSnow();

                $("body").css({"position":"relative"});
            }

            function resizeSnow() {
                marginBottom = document.body.scrollHeight + 0;
                marginRight = document.body.clientWidth - 0;
            }

            function createSnow(){
                var $flakes = '<div id="snow-container" style="pointer-events:none;position:absolute;height:100%;width:100%;top:0;bottom:0;right:0;z-index: 999999;overflow:hidden;">';

                for (i = 0; i <= snowMax; i++) {
                    $flakes += "<span id='flake-" + i + "' style='" + snowStyles + "top:-" + snowMaxSize + "'>" + snowEntity + "</span>";
                }

                $flakes += '</div>';

                $('body').append( $flakes );
            }

            window.addEventListener('resize', resizeSnow);
            window.addEventListener('load', initSnow);

        }
    );

})( jQuery );
(function( $ ) {
    var snowMax = 42;
    var snowColor = '#ffffff';
    var snowType = new Array('Font Awesome 5 Pro');
    // var snowEntity = "*";
    var snowEntity = '<i class="fad fa-snowflake"></i>';
    var snowSpeed = 1.2;
    var snowMaxSize = 18;
    var snowMinSize = 12;
    var snowRefresh = 120;
    var snowZIndex = 50;
    var snowStyles = "";

    jQuery(document).trigger( 'loadWPSnow', [ snowMax, snowColor, snowType, snowEntity, snowSpeed, snowMaxSize, snowMinSize, snowRefresh, snowZIndex, snowStyles ] );

})( jQuery );
</script>