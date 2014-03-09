		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">{{ Config::get('app.name') }}</a>
				</div>
				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li{{ Request::is('/') ? ' class="active"' : '' }}><a href="{{ action('HomeController@showHome') }}">Home</a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</div>