<div class="container-fluid about px-0 font-weight-bold text-center pb-5">
  <button type="button" class="btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal">
    <i class="fad fa-info-circle"></i> Guideline
  </button>
</div>
<div class="modal fade" id="GuideModal" tabindex="-1" role="dialog" aria-label="Guide" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">Guideline</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
{{--         <p>There are 4 options: Play alone, Play with AI, Play in room, and Set up the board</p> --}}
        <p>There are 2 options: Play alone and Play with AI</p>
        <ol>
          <li><u>Play alone:</u> Players press on the button <a target="_blank" href="{{ URL::to('/amp/play-alone') }}">"PLAY ALONE"</a> on the front page and practice alone.</li>
          <li><u>Play with AI:</u> Players play directly on the front page. There are 5 levels: <a target="_blank" href="{{ url('/amp/newbie') }}">Newbie</a>, <a target="_blank" href="{{ url('/amp/easy') }}">Easy</a>, <a target="_blank" href="{{ url('/amp/normal') }}">Normal</a>, <a target="_blank" href="{{ url('/amp/hard') }}">Hard</a>, and <a target="_blank" href="{{ url('/amp/hardest') }}">Hardest</a>.</li>
{{--           <li><u>Play in room:</u> Players press on the button "HOST A ROOM", host a new room with random Room code, and create a password for you and your friend, also capable of Inviting friend to play by sending the link. Players can also access the page <a target="_blank" href="{{ URL::to('/amp/rooms') }}">"Rooms"</a> to enter a hosted room. Players can choose Red Side or Black Side, Red moves first.</li>
          <li><u>Set up the board:</u> Players press on the link <a target="_blank" href="{{ url('/amp/set-up') }}">"Set Up"</a>. In this option, players can arrange the chess pieces and press "SAVE BOARD" to challenge friends.</li> --}}
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>