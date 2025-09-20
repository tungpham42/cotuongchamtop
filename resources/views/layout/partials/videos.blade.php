<div style="background-color: #312e2b" class="container-fluid videos px-0">
    <div class="container mx-auto p-3">
        <div class="row my-5">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-video"></i> Video</h2>
            @foreach($videos as $video)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-lg rounded border-dark">
                    <a target="_blank" href="{{ url('/') }}/tin-tuc/{{ $video->post_name }}"><img class="card-img-top" src="{{ $video->getImageAttribute() }}" alt="{{ $video->post_title }}"></a>
                    <div class="card-body bg-dark p-2">
                        <a class="text-light" target="_blank" href="{{ url('/') }}/tin-tuc/{{ $video->post_name }}"><h5 class="card-title text-light m-0 font-weight-light">{{ $video->post_title }}</h5></a>
                        <p class="card-text">{{ $video->post_excerpt }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>