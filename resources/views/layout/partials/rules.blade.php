<div class="container-fluid about px-0 font-weight-bold text-center py-0">
  <p class="w-100 text-center my-1">
    <a id="share-board" class="w-25 btn btn-dark btn-lg" href="{{ url(__('/ban-co/')) }}"><i class="fad fa-abacus"></i> {{ __("Giải bàn cờ") }}</a>
    <button type="button" class="w-25 btn btn-dark btn-lg" data-toggle="modal" data-target="#GuideModal"><i class="fad fa-info-circle"></i> {{ __("Hướng dẫn") }}</button>
  </p>
</div>

<div class="modal fade text-dark" id="GuideModal" tabindex="-1" role="dialog" aria-labelledby="GuideModalLabel" aria-hidden="true" data-backdrop="false" style="z-index: 9999;">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="GuideModalLabel"><i class="fas fa-info-circle"></i> {{ __("Hướng dẫn") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left">
        <h2>{{ __("Bàn cờ tướng") }}</h2>
        <p>{{ __("guide_board_desc_1") }}</p>
        <p>{{ __("guide_board_desc_2") }}</p>

        <h2>{{ __("Cách xếp bàn cờ tướng") }}</h2>
        <p>{{ __("guide_setup_desc") }}</p>
        <p class="text-center">
          <img alt="Bàn cờ" class="w-100" src="{{ $cdnUrl ?? '' }}/img/ban-co-tuong.jpg" >
        </p>

        <h2>{{ __("Loại quân và cách di chuyển") }}</h2>
        <p>{{ __("guide_pieces_desc") }}</p>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th scope="col" class="text-center">{{ __("Quân") }}</th>
              <th scope="col" class="text-center" colspan="2">{{ __("Ký hiệu") }}</th>
              <th scope="col" class="text-center">{{ __("Số lượng") }}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="align-middle text-center">{{ __('piece_k') }}</td>
              <td class="text-center"><img alt="{{ __('piece_k') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bK.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_k') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rK.svg" ></td>
              <td class="align-middle text-center">1</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_a') }}</td>
              <td class="text-center"><img alt="{{ __('piece_a') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bA.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_a') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rA.svg" ></td>
              <td class="align-middle text-center">2</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_b') }}</td>
              <td class="text-center"><img alt="{{ __('piece_b') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bB.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_b') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rB.svg" ></td>
              <td class="align-middle text-center">2</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_r') }}</td>
              <td class="text-center"><img alt="{{ __('piece_r') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bR.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_r') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rR.svg" ></td>
              <td class="align-middle text-center">2</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_c') }}</td>
              <td class="text-center"><img alt="{{ __('piece_c') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bC.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_c') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rC.svg" ></td>
              <td class="align-middle text-center">2</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_n') }}</td>
              <td class="text-center"><img alt="{{ __('piece_n') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bN.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_n') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rN.svg" ></td>
              <td class="align-middle text-center">2</td>
            </tr>
            <tr>
              <td class="align-middle text-center">{{ __('piece_p') }}</td>
              <td class="text-center"><img alt="{{ __('piece_p') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/bP.svg" ></td>
              <td class="text-center"><img alt="{{ __('piece_p') }}" src="{{ $cdnUrl ?? '' }}/img/xiangqipieces/wiki/rP.svg" ></td>
              <td class="align-middle text-center">5</td>
            </tr>
          </tbody>
        </table>

        <h2>{{ __("Luật Cờ tướng") }}</h2>
        <p>{{ __("Quân cờ được di chuyển theo luật sau:") }}</p>
        <ol>
          <li><u>{{ __('piece_k') }}:</u> {{ __("guide_rule_king") }}</li>
          <li><u>{{ __('piece_a') }}:</u> {{ __("guide_rule_advisor") }}</li>
          <li><u>{{ __('piece_b') }}:</u> {{ __("guide_rule_elephant") }}</li>
          <li><u>{{ __('piece_r') }}:</u> {{ __("guide_rule_chariot") }}</li>
          <li><u>{{ __('piece_n') }}:</u> {{ __("guide_rule_horse") }}</li>
          <li><u>{{ __('piece_c') }}:</u> {{ __("guide_rule_cannon") }}</li>
          <li><u>{{ __('piece_p') }}:</u> {{ __("guide_rule_pawn") }}</li>
          <li><u>{{ __("Ăn quân") }}:</u> {{ __("guide_rule_capture") }}</li>
          <li><u>{{ __("Chống tướng") }}:</u> {{ __("guide_rule_flying_general") }}</li>
          <li><u>{{ __("An toàn của tướng") }}:</u> {{ __("guide_rule_safety") }}</li>
        </ol>

        <h2>{{ __("Chế độ chơi") }}</h2>
        <p>{{ __("guide_mode_desc") }}</p>
        <ol>
          <li><u>{{ __("Chơi một mình") }}:</u> {!! __('guide_mode_alone_html', ['url' => url(__('/choi-mot-minh'))]) !!}</li>
          <li><u>{{ __("Luyện với máy") }}:</u> {!! __('guide_mode_bot_html', ['url1' => url(__('/moi-choi')), 'url2' => url(__('/de')), 'url3' => url(__('/binh-thuong')), 'url4' => url(__('/kho')), 'url5' => url(__('/kho-nhat'))]) !!}</li>
          <li><u>{{ __("Chơi online") }}:</u> {!! __('guide_mode_online_html', ['url' => url(__('/sanh-cho'))]) !!}</li>
          <li><u>{{ __("Cờ thế") }}:</u> {!! __('guide_mode_puzzle_html', ['url' => url(__('/co-the'))]) !!}</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pulse-red" data-dismiss="modal"><i class="fas fa-times"></i> {{ __("Đóng") }}</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.body.appendChild(document.getElementById('GuideModal'));
  });
</script>
