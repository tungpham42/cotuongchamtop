@extends('layout.mainlayout')
@inject('userPresenter', 'App\Presenters\UserPresenter')
@section('aboveContent')
<div class="container-fluid contact px-0">
  {!! __('terms_and_conditions_content') !!}
</div>
@endsection
