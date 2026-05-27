<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Lỗi 403 - Cờ tướng</title>

	<link href="https://fonts.googleapis.com/css?family=Quicksand:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese" rel="stylesheet">
	<link rel="icon" sizes="32x32" href="{{ url('/') }}/img/favicon-32x32-game.png" />

	<style>
	* {
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	}

	body {
		padding: 0;
		margin: 0;
	}

	#notfound {
		position: relative;
		height: 100vh;
	}

	#notfound .notfound {
		position: absolute;
		left: 50%;
		top: 50%;
		-webkit-transform: translate(-50%, -50%);
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
	}

	.notfound {
		max-width: 520px;
		width: 100%;
		line-height: 1.4;
		text-align: center;
	}

	.notfound .notfound-404 {
		position: relative;
		height: 200px;
		margin: 0px auto 20px;
		z-index: -1;
	}

	.notfound .notfound-404 h1 {
		font-family: 'Quicksand', sans-serif;
		font-size: 236px;
		font-weight: 200;
		margin: 0px;
		color: #211b19;
		text-transform: uppercase;
		position: absolute;
		left: 50%;
		top: 50%;
		-webkit-transform: translate(-50%, -50%);
		-ms-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
	}

	.notfound .notfound-404 h2 {
		font-family: 'Quicksand', sans-serif;
		font-size: 28px;
		font-weight: 400;
		text-transform: uppercase;
		color: #211b19;
		background: #fff;
		padding: 10px 5px;
		margin: auto;
		display: inline-block;
		position: absolute;
		bottom: 0px;
		left: 0;
		right: 0;
	}

	.notfound p {
		font-family: 'Quicksand', sans-serif;
		color: #211b19;
		font-size: 16px;
		margin-bottom: 25px;
		font-weight: 500;
	}

	.notfound a {
		font-family: 'Quicksand', sans-serif;
		display: inline-block;
		font-weight: 700;
		text-decoration: none;
		color: #fff;
		text-transform: uppercase;
		padding: 13px 23px;
		background: #ff6300;
		font-size: 18px;
		-webkit-transition: 0.2s all;
		transition: 0.2s all;
		margin: 0 5px;
	}

	.notfound a:hover {
		color: #ff6300;
		background: #211b19;
	}

	@media only screen and (max-width: 767px) {
		.notfound .notfound-404 h1 {
			font-size: 148px;
		}
	}

	@media only screen and (max-width: 480px) {
		.notfound .notfound-404 {
			height: 148px;
			margin: 0px auto 10px;
		}
		.notfound .notfound-404 h1 {
			font-size: 86px;
		}
		.notfound .notfound-404 h2 {
			font-size: 16px;
		}
		.notfound p {
			font-size: 14px;
		}
		.notfound a {
			padding: 7px 15px;
			font-size: 14px;
			margin: 0 2px;
		}
	}
	</style>

	</head>

<body>

	<div id="notfound">
		<div class="notfound">
			<div class="notfound-404">
				<h1>Oops!</h1>
				<h2>Lỗi 403 - Từ chối truy cập</h2>
			</div>

			<p>
				{{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Bạn không có quyền truy cập vào khu vực này.' }}
			</p>

			<a href="javascript:history.back()">Quay lại</a>
			<a href="{{ url('/') }}">Về trang chủ</a>
		</div>
	</div>

</body>

</html>
