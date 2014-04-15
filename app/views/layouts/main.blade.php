<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>@yield('title', Config::get('app.name'))</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		{{ HTML::style('/css/bootstrap/bootstrap.min.css') }}
		{{ HTML::style('/css/application.css') }}
		@yield('stylesheets')

	    <!--[if lt IE 9]>
	      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	    <![endif]-->
	</head>
	<body>
		@include('layouts.navbar')
		<div class="container">
			@yield('content')
			<hr>
			<footer class="footer">
				<p class="pull-right"><a href="#">Back to top</a></p>
				<p>&copy; {{ date('Y') }} {{ Config::get('app.name') }}
				@if (Config::get('app.name') != 'LowEndPing' && Config::get('app.credits'))
				- Powered by <a href="https://github.com/nikkiii/lowendping">LowEndPing</a>
				@endif
				</p>
			</footer>
		</div> <!-- /container -->
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="{{ URL::asset("/js/bootstrap/bootstrap.min.js") }}"></script>
		<script src="{{ URL::asset("/js/application.js") }}"></script>
		@yield('scripts')
	</body>
</html>