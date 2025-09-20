<div class="container-fluid about px-0 font-weight-bold text-center py-0">
  <p class="w-100 text-center my-1">
    <a id="share-board" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/bodeu/') }}"><i class="fad fa-abacus"></i> 해결 보드</a>
    <a class="w-25 btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal"><i class="fad fa-info-circle"></i>  가이드라인</a>
  </p>
</div>
<div class="modal fade text-dark" id="GuideModal" tabindex="-1" role="dialog" aria-label="Guide" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i>  가이드라인</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h2>게시판</h2>
        <p>향기 체스판은 가로 9줄, 세로 10줄의 직사각형 격자입니다. 이 판은 "강"에 의해 두 가지 면으로 나뉘어집니다. 각 플레이어의 기물은 판의 한쪽에 시작하며, 일부 특정 유형의 기물이 아닌 한 강을 건너지 못합니다.</p>
        <h2>판을 어떻게 설정하는가</h2>
        <p>체스판을 설정하려면 아래에 설명 된 기물을 기억한 다음 아래 이미지와 같이 배치하면 됩니다.</p>
        <p class="text-center">
          <img alt="게시판" class="w-100" src="{{ $cdnUrl }}/img/ban-co-tuong.jpg" >
        </p>
        <h2>기물과 이동</h2>
        <p>향기 체스에는 각각 16개의 총 32개의 기물이 있습니다. 기물은 서양 체스의 것과 형태와 크기가 유사하지만 다른 유닛을 나타냅니다:</p>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th scope="col" class="text-center">기물</th>
              <th scope="col" class="text-center" colspan="2">심볼</th>
              <th scope="col" class="text-center">수량</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>왕</td>
              <td><img alt="Tướng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bK.svg" ></td>
              <td><img alt="Tướng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rK.svg" ></td>
              <td>1</td>
            </tr>
            <tr>
              <td>대군</td>
              <td><img alt="Sĩ đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bA.svg" ></td>
              <td><img alt="Sĩ đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rA.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>상</td>
              <td><img alt="Tượng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bB.svg" ></td>
              <td><img alt="Tượng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rB.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>차</td>
              <td><img alt="Xe đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bR.svg" ></td>
              <td><img alt="Xe đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rR.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>포</td>
              <td><img alt="Pháo đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bC.svg" ></td>
              <td><img alt="Pháo đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rC.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>마</td>
              <td><img alt="Mã đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bN.svg" ></td>
              <td><img alt="Mã đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rN.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>병</td>
              <td><img alt="Tốt đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bP.svg" ></td>
              <td><img alt="Tốt đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rP.svg" ></td>
              <td>5</td>
            </tr>
          </tbody>
        </table>
        <h2>규칙</h2>
        <p>체스 기물은 다음 규칙에 따라 움직입니다:</p>
        <ol>
          <li><u>장 (또는 왕):</u> 보드에서 가장 중요한 기물로, 장은 "궁"이라는 판의 중앙에있는 3 x 3 영역 내에서 수직적으로 (위, 아래, 왼쪽 또는 오른쪽) 한 칸 움직일 수 있습니다.</li>
          <li><u>사 (또는 대군):</u> 이 기물들은 궁에서 시작하며 궁 내에서 대각선으로 한 칸씩 움직일 수 있습니다.</li>
          <li><u>상:</u> 이 기물들은 대각선으로 두 칸 이동할 수 있으며 자신의 판면에서만 이동할 수 있습니다. 강에 막혀 강을 건널 수 없습니다.</li>
          <li><u>차:</u> 이 기물들은 수평 또는 수직으로 모든 칸으로 이동할 수 있습니다. 다른 기물을 뛰어 넘을 수 없습니다.</li>
          <li><u>마:</u> 이 기물들은 수평으로 두 칸, 그리고 대각선으로 한 칸 움직일 수 있습니다. 다른 기물을 뛰어 넘을 수 있습니다.</li>
          <li><u>포:</u> 이 기물들은 차와 같이 움직일 수 있지만 다른 기물을 뛰어 넘어서만 캡쳐할 수 있습니다.</li>
          <li><u>병 (또는 졸):</u> 이 기물들은 한 칸 앞으로 움직일 수 있으며 첫 번째 움직임에서는 두 칸 앞으로 이동할 수 있습니다. 강을 건너면 측면으로도 한 칸 움직일 수 있습니다.</li>
        </ol>
        <h2>옵션</h2>
        <p>4가지 옵션이 있습니다. 혼자 놀기, AI로 놀기, 방에서 놀기, 보드 설치하기</p>
        <ol>
          <li><u>혼자 놀기:</u> 선수들은 1면에 있는 "혼자 놀기" 버튼을 누르고 혼자 연습한다.</li>
          <li><u>컴퓨터로 교육하기:</u> 선수들은 1면에서 직접 경기를 한다. 5가지 레벨이 있습니다. 신입, 이지, 노멀, 하드, 하드.</li>
          <li><u>방에서 놀기:</u> 플레이어는 "온라인 재생" 버튼을 누르고 랜덤 룸 코드로 새 룸을 호스트하며 링크를 전송하여 친구를 초대할 수 있는 암호를 만듭니다.</li>
          <li><u>퍼즐을 설정합니다.:</u> 플레이어는 "퍼즐" 링크를 누릅니다. 이 옵션에서 플레이어는 체스 조각을 배열하고 "퍼즐 다운로드"를 눌러 친구에게 도전할 수 있습니다.</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pulse-red" data-dismiss="modal"><i class="fas fa-times"></i> 가까운</button>
      </div>
    </div>
  </div>
</div>