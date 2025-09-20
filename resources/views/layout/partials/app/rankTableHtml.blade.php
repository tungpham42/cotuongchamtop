<div class="table-responsive">
    <table class="table" id="rankingTable">
        <thead>
            <tr>
                <th scope="col">Tên</th>
                <th scope="col">Email</th>
                <th scope="col">Ngày giờ gia nhập</th>
                <th scope="col">Điểm</th>
            </tr>
        </thead>
        <tbody>
            {{ $users->links('vendor.pagination.match') }}
            @foreach($users as $user)
            <tr data-user="{{ $user->id }}">
                <th scope="name"><a target="_blank" class="text-danger" href="{{ url('/ky-thu/') }}/{{ $user->id }}"># {{ $user->id }} {{ $user->name }}</a></th>
                <td class="email">{{ $user->email }}</td>
                <td class="date">{{ $user->created_at }}</td>
                <td class="points"></td>
            </tr>
            <script>
                $.ajax({
                    type: "POST",
                    url: '{{ url('/api') }}/getPoints',
                    data: {
                        'id': '{{ $user->id }}'
                    },
                    dataType: 'text'
                }).done(function(data){
                    $('tr[data-user="{{ $user->id }}"] > td.points').text(data);
                });
            </script>
            @endforeach
        </tbody>
    </table>
</div>