<div class="container-fluid about px-0 font-weight-bold text-center py-0">
  <p class="w-100 text-center my-1">
    <a id="share-board" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/ban/') }}"><i class="fad fa-abacus"></i> 解决板</a>
    <a class="w-25 btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal"><i class="fad fa-info-circle"></i> 指南</a>
  </p>
</div>
<div class="modal fade text-dark" id="GuideModal" tabindex="-1" role="dialog" aria-label="Guide" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> 指南</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h2>棋盘</h2>
        <p>象棋棋盘是一个9 x 10行的矩形网格。棋盘被一条“河”分成两侧。每个玩家的棋子都开始放置在他们的棋盘一侧，除非它们是某种类型的棋子，否则不能越过河流。</p>
        <h2>如何设置棋盘</h2>
        <p>要设置象棋棋盘，您只需记忆下面描述的棋子，然后按照下面的图像排列即可。</p>
        <p class="text-center">
          <img alt="棋盘" class="w-100" src="{{ $cdnUrl }}/img/ban-co-tuong.jpg" >
        </p>
        <h2>棋子与移动</h2>
        <p>象棋有32个棋子，每个玩家有16个。这些棋子形状和大小与西洋棋中的棋子类似，但它们代表不同的单位：</p>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th scope="col" class="text-center">棋子</th>
              <th scope="col" class="text-center" colspan="2">符号</th>
              <th scope="col" class="text-center">数量</th>
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
        <h2>规则</h2>
        <p>象棋的棋子移动遵循以下规则：</p>
        <ol>
          <li><u>将：</u>在棋盘上最重要的棋子，将只能在“宫”中（棋盘中央玩家一侧的3×3区域）直线上下左右移动一个格子。</li>
          <li><u>士：</u>这些棋子也起始于宫中，只能在宫内对角线上移动一个格子。</li>
          <li><u>象：</u>这些棋子可以在自己的一侧斜着移动两个格子，但是它们被河流阻挡，不能越过河流。</li>
          <li><u>车：</u>这些棋子可以沿直线向前、向后、向左或向右移动任意格数，但是不能越过其他棋子。</li>
          <li><u>马：</u>这些棋子可以沿着直线向前或向后移动两个格子，然后向左或向右移动一个格子，它们可以跳过其他棋子。</li>
          <li><u>炮：</u>这些棋子像车一样移动，但只能通过跳过另一个棋子来进行攻击。</li>
          <li><u>兵：</u>这些棋子只能向前移动一个格子，第一次移动可以向前移动两个格子。一旦过河，它们也可以向左或向右移动一个格子。</li>
        </ol>
        <h2>选项</h2>
        <p>有4个选项：单独玩、与AI玩、在房间玩和设置棋盘</p>
        <ol>
          <li><u>独处：</u>玩家按下按钮<a class="animate" target="_blank" href="{{ URL::to('/duchu') }}">“独处”</a>在头版，独自练习。</li>
          <li><u>带计算机的列车：</u>玩家直接在头版播放。有5个级别：<a class="animate" target="_blank" href="{{ url('/xinshou') }}">新手</a>,<a class="animate" target="_blank" href="{{ url('/rongyide') }}">容易的</a>,<a class="animate" target="_blank" href="{{ url('/dianxingde') }}">典型的</a>,<a class="animate" target="_blank" href="{{ url('/jiangude') }}">坚固的</a>,和<a class="animate" target="_blank" href="{{ url('/zuinande') }}">最难的</a>。</li>
          <li><u>室内游戏：</u>玩家按下“立即播放”按钮，用随机房间代码主持新房间，并为您和您的朋友创建密码，还可以通过发送链接邀请朋友播放。玩家也可以访问该页面<a class="animate" target="_blank" href="{{ URL::to('/fangjianliebiao') }}">“房间列表”</a>进入托管房间。玩家可以选择红方或黑方，红方先移动。</li>
          <li><u>设置拼图：</u>玩家按下链接<a class="animate" target="_blank" href="{{ url('/mi') }}">“谜”</a>在这个选项中，玩家可以排列棋子并按下“捕获谜题”来挑战朋友。</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pulse-red" data-dismiss="modal"><i class="fas fa-times"></i> 关</button>
      </div>
    </div>
  </div>
</div>