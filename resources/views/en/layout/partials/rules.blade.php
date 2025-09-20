<div class="container-fluid about px-0 font-weight-bold text-center py-0">
  <p class="w-100 text-center my-1">
    <a id="share-board" class="w-25 btn btn-dark btn-lg" href="{{ URL::to('/board/') }}"><i class="fad fa-abacus"></i> Solve board</a>
    <a class="w-25 btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal"><i class="fad fa-info-circle"></i> Guideline</a>
  </p>
</div>
<div class="modal fade text-dark" id="GuideModal" tabindex="-1" role="dialog" aria-label="Guide" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Guideline</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h2>Board</h2>
        <p>The Xiangqi board is a rectangular grid of 9 x 10 lines. The board is divided by a "river" that separates the two sides of the board. Each player's pieces start on their side of the board and cannot cross the river unless they are a certain type of piece.</p>
        <h2>How to set up the board</h2>
        <p>To set up the chess board, you just need to memorize the pieces described below and then arrange as shown in the image below.</p>
        <p class="text-center">
          <img alt="Board" class="w-100" src="{{ $cdnUrl }}/img/ban-co-tuong.jpg" >
        </p>
        <h2>Pieces and movement</h2>
        <p>There are 32 pieces in Xiangqi, 16 for each player. The pieces are similar in shape and size to those in Western Chess, but they represent different units:</p>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th scope="col" class="text-center">Piece</th>
              <th scope="col" class="text-center" colspan="2">Symbol</th>
              <th scope="col" class="text-center">Quantity</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>General (or King)</td>
              <td><img alt="Tướng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bK.svg" ></td>
              <td><img alt="Tướng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rK.svg" ></td>
              <td>1</td>
            </tr>
            <tr>
              <td>Advisors (or Guards)</td>
              <td><img alt="Sĩ đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bA.svg" ></td>
              <td><img alt="Sĩ đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rA.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>Elephants</td>
              <td><img alt="Tượng đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bB.svg" ></td>
              <td><img alt="Tượng đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rB.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>Chariots</td>
              <td><img alt="Xe đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bR.svg" ></td>
              <td><img alt="Xe đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rR.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>Cannons</td>
              <td><img alt="Pháo đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bC.svg" ></td>
              <td><img alt="Pháo đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rC.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>Horses</td>
              <td><img alt="Mã đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bN.svg" ></td>
              <td><img alt="Mã đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rN.svg" ></td>
              <td>2</td>
            </tr>
            <tr>
              <td>Soldiers (or Pawns)</td>
              <td><img alt="Tốt đen" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/bP.svg" ></td>
              <td><img alt="Tốt đỏ" src="{{ $cdnUrl }}/img/xiangqipieces/wiki/rP.svg" ></td>
              <td>5</td>
            </tr>
          </tbody>
        </table>
        <h2>Rules</h2>
        <p>The chess pieces are moved according to the following rules:</p>
        <ol>
          <li><u>General (or King):</u> The most important piece on the board, the general can move one space orthogonally (up, down, left, or right) within the "palace," a 3 x 3 area in the center of the player's side of the board.</li>
          <li><u>Advisors (or Guards):</u> These pieces also start in the palace and can move one space diagonally within the palace.</li>
          <li><u>Elephants:</u> These pieces can move two spaces diagonally and are restricted to their own side of the board. They are blocked by the river and cannot cross it.</li>
          <li><u>Chariots:</u> These pieces can move any number of spaces orthogonally. They cannot jump over other pieces.</li>
          <li><u>Horses:</u> These pieces can move two spaces orthogonally and then one space diagonally. They can jump over other pieces.</li>
          <li><u>Cannons:</u> These pieces move like chariots but can only capture by jumping over another piece.</li>
          <li><u>Soldiers (or Pawns):</u> These pieces move one space forward, and can move two spaces forward on their first move. Once they cross the river, they can also move one space sideways.</li>
        </ol>
        <h2>Options</h2>
        <p>There are 4 options: Play alone, Play with AI, Play in room, and Set up the board</p>
        <ol>
          <li><u>Play alone:</u> Players press on the button <a class="animate" target="_blank" href="{{ URL::to('/play-alone') }}">"Play alone"</a> on the front page and practice alone.</li>
          <li><u>Train with computer:</u> Players play directly on the front page. There are 5 levels: <a class="animate" target="_blank" href="{{ url('/newbie') }}">Newbie</a>, <a class="animate" target="_blank" href="{{ url('/easy') }}">Easy</a>, <a class="animate" target="_blank" href="{{ url('/normal') }}">Normal</a>, <a class="animate" target="_blank" href="{{ url('/hard') }}">Hard</a>, and <a class="animate" target="_blank" href="{{ url('/hardest') }}">Hardest</a>.</li>
          <li><u>Play in room:</u> Players press on the button "Play now", host a new room with random Room code, and create a password for you and your friend, also capable of Inviting friend to play by sending the link. Players can also access the page <a class="animate" target="_blank" href="{{ URL::to('/rooms') }}">"Rooms"</a> to enter a hosted room. Players can choose Red Side or Black Side, Red moves first.</li>
          <li><u>Set up the puzzle:</u> Players press on the link <a class="animate" target="_blank" href="{{ url('/puzzle') }}">"Puzzle"</a>. In this option, players can arrange the chess pieces and press "Capture the puzzle" to challenge friends.</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pulse-red" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
      </div>
    </div>
  </div>
</div>