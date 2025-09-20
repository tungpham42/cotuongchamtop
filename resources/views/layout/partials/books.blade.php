<span style="background-color: transparent; margin-top: -70px;" class="d-block w-100 pb-5 mb-5" id="sach"></span>
<div style="background-color: transparent" class="container-fluid puzzles px-0">
    <div class="container mx-auto px-3 pt-0">
        <div class="row my-0">
            <h2 class="d-block w-100 text-light ml-3 mb-4"><i class="fas fa-book-open"></i> {{ numberToWordsVi($books->count()) }} quyển sách hay</h2>
            @foreach($books as $book)
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card shadow-lg rounded border-dark">
                    <a class="showPromotion" target="_blank" href="https://blog.cotuong.top/thu-vien-sach/{{ $book->post_name }}"><img style="height: 200px !important;" class="card-img-top" src="{{ $book->getImageAttribute() }}" alt="{{ $book->post_title }}"></a>
                    <div class="card-body bg-dark p-2">
                        <a class="text-light showPromotion" target="_blank" href="https://blog.cotuong.top/thu-vien-sach/{{ $book->post_name }}"><h5 class="card-title text-light m-0 font-weight-light text-center">{{ $book->post_title }}</h5></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>