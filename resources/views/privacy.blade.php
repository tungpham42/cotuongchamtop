@extends('layout.mainlayout')
@inject('userPresenter', 'App\Presenters\UserPresenter')

@section('aboveContent')
<div class="container-fluid contact px-0">
    {!! __('privacy_policy_content') !!}
</div>
@endsection
