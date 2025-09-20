<div class="container-fluid about px-0 font-weight-bold text-center py-0">
  <p class="w-100 text-center my-1">
    <a id="share-board" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/bodo/') }}"><i class="fad fa-abacus"></i> 解決ボード</a>
  <a class="w-25 btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal"><i class="fad fa-info-circle"></i> ガイドライン</a>
  </p>
</div>
<div class="modal fade text-dark" id="GuideModal" tabindex="-1" role="dialog" aria-label="Guide" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> ガイドライン</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h2>ボード</h2>
        <p>象棋盤は、9x10の線の矩形グリッドです。盤は「河」と呼ばれるもので二つの陣営を分けます。各プレーヤーの駒はそれぞれ自分たちの陣営に配置され、特定の駒でなければ河を渡ることはできません。</p>
        <h2>盤のセットアップの方法</h2>
        <p>チェス盤をセットアップするには、以下に説明する駒を覚えてから、下の画像に示すように配置するだけです。</p>
        <p class="text-center">
          <img alt="ボード" class="w-100" src="{{ $cdnUrl }}/img/ban-co-tuong.jpg" >
        </p>
        <h2>駒と動き</h2>
        <p>象棋には32個の駒があり、各プレーヤーに16個ずつあります。これらの駒は、西洋チェスのものと形状やサイズが似ていますが、異なるユニットを表しています。</p>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th scope="col" class="text-center">ピース</th>
              <th scope="col" class="text-center" colspan="2">シンボル</th>
              <th scope="col" class="text-center">量</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>帥</td>
              <td><img alt="Tướng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bK.svg" ></td>
              <td><img alt="Tướng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rK.svg" ></td>
              <td>1</td>
            </tr>
            <tr>
              <td>士</td>
              <td><img alt="Sĩ đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bA.svg" ></td>
              <td><img alt="Sĩ đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rA.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>象</td>
              <td><img alt="Tượng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bB.svg" ></td>
              <td><img alt="Tượng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rB.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>車</td>
              <td><img alt="Xe đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bR.svg" ></td>
              <td><img alt="Xe đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rR.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>砲</td>
              <td><img alt="Pháo đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bC.svg" ></td>
              <td><img alt="Pháo đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rC.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>馬</td>
              <td><img alt="Mã đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bN.svg" ></td>
              <td><img alt="Mã đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rN.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>兵</td>
              <td><img alt="Tốt đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bP.svg" ></td>
              <td><img alt="Tốt đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rP.svg" ></td>
              <td>5</td>
            </tr>
          </tbody>
        </table>
        <h2>ルール</h2>
        <p>チェスの駒は、以下のルールに従って動かします：</p>
        <ol>
          <li><u>将（帥）：</u> 盤上で最も重要な駒で、プレイヤー側の盤面中央にある3×3の「宮殿」内を、縦横斜めいずれかの方向に1マス進むことができます。</li>
          <li><u>士（仕）：</u> これらの駒も宮殿内に配置され、宮殿内で斜めに1マス進むことができます。</li>
          <li><u>象（相）：</u> これらの駒は、自分側の盤面にのみ動くことができ、斜めに2マス進むことができます。しかし、川を渡ることはできません。</li>
          <li><u>車：</u> これらの駒は、縦横いずれかの方向に何マスでも進むことができます。他の駒を飛び越えることはできません。</li>
          <li><u>馬：</u> これらの駒は、縦横に2マス進み、斜めに1マス進むことができます。他の駒を飛び越えることができます。</li>
          <li><u>砲：</u> これらの駒は、車と同じように動きますが、駒を飛び越えて相手の駒を取ることができます。</li>
          <li><u>兵（卒）：</u> これらの駒は、前方に1マス進むことができ、最初の一手では2マス進むことができます。川を渡ると、横に1マス進むこともできます。</li>
        </ol>
        <h2>オプション</h2>
        <p>4つのオプションがあります：一人で遊ぶ、AIで遊ぶ、部屋で遊ぶ、ボードをセットアップする</p>
        <ol>
          <li><u>一人で遊ぶ：</u> プレイヤーはフロントページの<a class="animate" target="_blank" href="{{ URL::to('/ichi-nin-de-asobu') }}">「一人で遊ぶ」</a>ボタンを押して、一人で練習します。</li>
          <li><u>パソコンでトレーニング:</u> プレイヤーはフロントページで直接プレイします。 5つのレベルがあります。</li>
          <li><u>部屋で遊ぶ:</u> プレイヤーは「今すぐプレイ」ボタンを押して、ランダムなルームコードで新しいルームをホストし、あなたとあなたの友達のパスワードを作成します。また、リンクを送信して友達をプレイに招待することもできます。</li>
          <li><u>パズルを組み立てる:</u> プレイヤーはリンク「パズル」を押します。このオプションでは、プレイヤーはチェスの駒を配置し、[パズルをキャプチャ] を押して友達に挑戦できます。</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pulse-red" data-dismiss="modal"><i class="fas fa-times"></i> 近い</button>
      </div>
    </div>
  </div>
</div>