<style>
span[id^='flake'] {
    color: #ffd700 !important;
}
</style>
<script>
(function( $ ) {
    'use strict';

    $(document).on(
        'loadWPFlower',
        function( event, flowerMax, flowerColor, flowerType, flowerEntity, flowerSpeed, flowerMaxSize, flowerMinSize, flowerRefresh, flowerZIndex, flowerStyles ){

            var flower = [],
                i = 0,
                pos = [],
                coords = [],
                left = [],
                marginBottom,
                flowerSize = flowerMaxSize - flowerMinSize,
                marginRight;

            function randomizeFlower(range) {
                return Math.floor(range * Math.random());
            }

            function moveFlower() {
                for (i = 0; i <= flowerMax; i++) {
                    coords[i] += pos[i];
                    flower[i].posY += flower[i].sink;
                    flower[i].style.left = flower[i].posX + 2 * left[i] * Math.sin(coords[i]) + "px";
                    flower[i].style.top = flower[i].posY + "px";

                    if (flower[i].posY >= marginBottom + 2 * flower[i].size || parseInt(flower[i].style.left) > (marginRight - 3 * left[i])) {
                        flower[i].posX = randomizeFlower(marginRight - flower[i].size);
                        flower[i].posY = ( 2 * flower[i].size ) * -1;
                    }
                }

                setTimeout( moveFlower, flowerRefresh );
            }

            function initFlower() {
                createFlower();
                resizeFlower();

                for (i = 0; i <= flowerMax; i++) {
                    coords[i] = 0;
                    left[i] = Math.random() * 15;
                    pos[i] = 0.03 + Math.random() / 10;
                    flower[i] = document.getElementById("flake" + i);
                    flower[i].size = randomizeFlower(flowerSize) + flowerMinSize;
                    flower[i].style.fontSize = flower[i].size + "px";
                    flower[i].style.fontFamily=flowerType[randomizeFlower(flowerType.length)];
                    flower[i].style.color = flowerColor[randomizeFlower(flowerColor.length)];
                    flower[i].style.position = 'absolute';
                    flower[i].style.zIndex = 2500;
                    flower[i].sink = flowerSpeed * flower[i].size / 5;
                    flower[i].posX = randomizeFlower(marginRight - flower[i].size);
                    flower[i].posY = randomizeFlower( marginBottom + 2 * flower[i].size );
                    flower[i].style.left = flower[i].posX + "px";
                    flower[i].style.top = flower[i].posY + "px";
                }

                resizeFlower(); //Resize again

                moveFlower();

                $("body").css({"position":"relative"});
            }

            function resizeFlower() {
                marginBottom = document.body.scrollHeight + 0;
                marginRight = document.body.clientWidth - 0;
            }

            function createFlower(){
                var $flakes = '<div id="flower-container" style="pointer-events:none;position:absolute;height:100%;width:100%;top:0;bottom:0;right:0;z-index: 999999;overflow:hidden;">';

                for (i = 0; i <= flowerMax; i++) {
                    $flakes += "<span id='flake" + i + "' style='" + flowerStyles + "top:-" + flowerMaxSize + "'>" + flowerEntity + "</span>";
                }

                $flakes += '</div>';

                $('body').append( $flakes );
            }

            window.addEventListener('resize', resizeFlower);
            window.addEventListener('load', initFlower);

        }
    );

})( jQuery );
(function( $ ) {
    var flowerMax = 42;
    var flowerColor = '#ffd700';
    var flowerType = new Array('sans-serif');
    // var flowerEntity = "*";
    var flowerEntity = '🌸';
    var flowerSpeed = 1.2;
    var flowerMaxSize = 21;
    var flowerMinSize = 14;
    var flowerRefresh = 120;
    var flowerZIndex = 50;
    var flowerStyles = "";

    jQuery(document).trigger( 'loadWPFlower', [ flowerMax, flowerColor, flowerType, flowerEntity, flowerSpeed, flowerMaxSize, flowerMinSize, flowerRefresh, flowerZIndex, flowerStyles ] );

})( jQuery );
</script>