<span style="background-color: transparent" class="d-block w-100 pb-3"></span>
<div style="background-color: transparent" class="container-fluid insuranceArticles px-0">
    <div class="container mx-auto p-3">
        <div class="row my-5">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-heartbeat"></i> Bảo hiểm</h2>
            @foreach($insuranceArticles as $article)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-lg rounded border-dark">
                    <a class="showPromotion" target="_blank" href="{{ url('/') }}/tin-tuc/{{ $article->post_name }}"><img class="card-img-top" src="{{ $article->getImageAttribute() }}" alt="{{ $article->post_title }}"></a>
                    <div class="card-body bg-dark p-2">
                        <a class="text-light showPromotion" target="_blank" href="{{ url('/') }}/tin-tuc/{{ $article->post_name }}"><h5 class="card-title text-light m-0 font-weight-light">{{ $article->post_title }}</h5></a>
                        <p class="card-text">{{ $article->post_excerpt }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>