@extends('amp.layout.mainlayout')
@section('aboveContent')
<div class="container-fluid about px-0">
  <div class="container p-3">
    <h2 class="h1-responsivefooter text-center my-4">Giới thiệu</h2>
    <div class="row">
      <p>Cờ tướng (Tiếng Trung: 象棋), hay còn gọi là cờ Trung Hoa (Tiếng Trung: 中國象棋), là một trò chơi trí tuệ dành cho hai người. Đây là loại cờ phổ biến nhất tại các nước như Trung Hoa, Việt Nam, Đài Loan và Singapore và nằm trong cùng một thể loại cờ với cờ vua, shogi, janggi.</p>
      <p>Trò chơi này mô phỏng cuộc chiến giữa hai quốc gia, với mục tiêu là bắt được Tướng của đối phương. Các đặc điểm khác biệt của cờ tướng so với các trò chơi cùng họ là: các quân đặt ở giao điểm các đường thay vì đặt vào ô, quân Pháo phải nhảy qua 1 quân khi ăn quân, các khái niệm sông và cung nhằm giới hạn các quân Tướng, Sĩ và Tượng.</p>
      <h3 class="w-100">Bàn cờ</h3>
      <p>Bàn cờ là hình chữ nhật do 9 đường dọc và 10 đường ngang cắt nhau vuông góc tại 90 điểm hợp thành. Một khoảng trống gọi là sông nằm ngang giữa bàn cờ, chia bàn cờ thành hai phần đối xứng bằng nhau. Mỗi bên có một cung Tướng hình vuông do 4 ô hợp thành tại các đường dọc 4, 5, 6 kể từ đường ngang cuối của mỗi bên, trong 4 ô này có vẽ hai đường chéo.</p>
      <p>Theo quy ước, khi bàn cờ được quan sát chính diện, phía dưới sẽ là quân Trắng (hoặc Đỏ), phía trên sẽ là quân Đen (hoặc Xanh). Các đường dọc bên Trắng (Đỏ) được đánh số từ 1 đến 9 từ phải qua trái. Các đường dọc bên Đen (Xanh) được đánh số từ 9 tới 1 từ phải qua trái.</p>
      <h3 class="w-100">Luật chơi</h3>
      <p>Ván cờ được tiến hành giữa hai người, một người cầm quân Trắng (hay Đỏ), một người cầm quân Đen (hay Xanh). Mục đích của mỗi người là tìm mọi cách đi quân trên bàn cờ theo đúng luật để chiếu bí hay bắt Tướng (hay Soái) của đối phương.</p>
      <h3 class="w-100">Các quân cờ</h3>
      <h4 class="w-100">Tướng (Soái)</h4>
      <p>Quân Tướng đi từng ô một, đi ngang hoặc dọc và luôn luôn ở trong phạm vi cung, không được ra ngoài. Tính theo khả năng chiến đấu thì Tướng là quân yếu nhất do chỉ đi nước một và bị giới hạn trong cung. Tuy nhiên trong nhiều tình huống, đặc biệt khi cờ tàn đòn "lộ mặt tướng" lại tỏ ra rất mạnh.</p>
      <p>Tướng được chốt chặt trong cung và có tới 2 Sĩ và Tượng canh gác hai bên. Chính điều này làm cho ván cờ trở nên khó phân thắng bại, cơ may hòa cờ rất lớn.</p>
      <h4 class="w-100">Sĩ</h4>
      <p>Quân Sĩ đi chéo 1 ô mỗi nước và luôn luôn phải ở trong cung. Như vậy, quân Sĩ có 5 giao điểm có thể đứng hợp lệ và là quân cờ yếu nhất.</p>
      <p>Sĩ có chức năng trong việc bảo vệ Tướng, mất Sĩ được cho là nguy hiểm khi đối phương còn đủ 2 Xe hoặc dùng Xe Mã Tốt tấn công. Bỏ Pháo ăn Sĩ rồi dùng 2 Xe hoặc dùng Xe Mã Tốt tấn công tấn công là đòn chiến thuật thường thấy. Khi cờ tàn còn Pháo thì phải chú ý giữ Sĩ để làm ngòi cho Pháo tấn công.</p>
      <h4 class="w-100">Tượng</h4>
      <p>Quân Tượng đi chéo 2 ô mỗi nước và không được vượt sang sông. Vì vậy trên bàn cờ, mỗi bên ta có 7 vị trí mà quân Tượng có thể đi được.</p>
      <p>Nước đi của Tượng không hợp lệ khi có một quân cờ nằm chặn giữa đường đi. Ta gọi là Tượng bị cản và vị trí cản được gọi là "mắt Tượng". Tượng được tính là mạnh hơn Sĩ một chút. Một Tốt qua hà được đổi lấy 1 Sĩ hay 1 Tượng. Tuy nhiên khả năng phòng thủ của Tượng nhỉnh hơn nên nếu Sĩ là 2 thì Tượng là 2,5.</p>
      <h4 class="w-100">Xe</h4>
      <p>Quân Xe đi ngang hoặc dọc trên bàn cờ miễn là đừng bị quân khác cản đường từ điểm đi đến điểm đến. Xe được coi là quân cờ mạnh nhất. Giá trị của Xe thường tính là bằng 2 Pháo hoặc Pháo Mã.</p>
      <p>Khai cuộc thường tranh đưa các quân Xe ra các đường dọc thông thoáng, dễ phòng thủ và tấn công.</p>
      <h4 class="w-100">Pháo</h4>
      <p>Quân Pháo đi ngang và dọc giống như Xe. Điểm khác biệt là Pháo muốn ăn quân thì nó phải nhảy qua đúng 1 quân nào đó. Khi không ăn quân, tất cả những điểm từ điểm đi đến điểm đến cũng phải không có quân cản.</p>
      <p>Cờ tướng cổ đại không có quân Pháo. Các nhà nghiên cứu đều nhất trí là quân Pháo được bổ sung từ thời nhà Đường. Đây là quân cờ ra đời muộn nhất trên bàn cờ tướng vì tới thời đó, pháo được sử dụng trong chiến tranh với hình thức là máy bắn đá. Bấy giờ, từ Pháo (砲) trong chữ Hán được viết với bộ "thạch", nghĩa là đá. Cho đến đời nhà Tống, khi loại pháo mới mang thuốc nổ được phát minh thì từ Pháo (炮) được viết với bộ "hỏa".</p>
      <p>Do đặc điểm phải có ngòi khi tấn công, Pháo thường dùng Tốt của quân mình trong khai cuộc, hoặc dùng chính Sĩ hay Tượng của mình làm ngòi để chiếu hết tướng đối phương trong tàn cuộc.</p>
      <p>Trên thực tế thì có tới 70% khai cuộc là dùng Pháo đưa vào giữa dọa bắt tốt đầu của đối phương, gọi là thế Pháo đầu. Đối phương có thể dùng Pháo đối lại cũng vào giữa. Nếu bên đi sau đưa Pháo cùng bên với bên đi trước thì khai cuộc gọi là trận Thuận Pháo, đi Pháo vào ngược bên nhau gọi là trận Nghịch Pháo (hay Liệt Pháo).</p>
      <h4 class="w-100">Mã</h4>
      <p>Quân Mã đi ngang 2 ô và dọc 1 ô (hay dọc 2 ô và ngang 1 ô). Nếu có quân cờ nào đó nằm ngay bên cạnh thì Mã bị cản, không được đi đường đó.</p>
      <p>Mã do không đi thẳng, lại có thể bị cản nên mức độ cơ động của quân này kém hơn Xe và Pháo. Khi khai cuộc, Mã kém hơn Pháo. Khi tàn cuộc, Mã trở nên mạnh hơn Pháo.</p>
      <h4 class="w-100">Tốt (Binh)</h4>
      <p>Quân Tốt đi 1 ô mỗi nước. Nếu Tốt chưa qua sông, nó chỉ được tiến. Nếu Tốt đã qua sông thì được đi ngang hay tiến, không được đi lùi.</p>
      <p>Khi đi đến đường biên ngang bên phần sân đối phương, lúc này, chúng được gọi là Tốt lụt.</p>
      <p>Trong khai cuộc, việc thí Tốt là chuyện tương đối phổ biến. Ngoại trừ việc phải bảo vệ Tốt đầu, các quân Tốt khác thường xuyên bị xe pháo mã ăn mất. Việc mất mát một vài Tốt ngay từ đầu cũng được xem như việc thí quân.</p>
      <p>Đến cờ tàn, giá trị của Tốt tăng nhanh và số lượng Tốt khi đó có thể đem lại thắng lợi hoặc chỉ hòa cờ. Khi đó việc đưa được Tốt qua sông và tới gần cung Tướng của đối phương trở nên rất quan trọng. Tốt khi đến tuyến áp đáy, ép sát cung Tướng thì Tốt mạnh như Xe.</p>
    </div>
  </div>
</div>
@endsection
@section('belowContent')
@endsection