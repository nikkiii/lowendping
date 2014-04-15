@extends('layouts.main')

@section('title')
	{{ Config::get('app.name') }} - Result #{{ $query->id }}
@endsection

@section('content')
		<div class="content text-center">
			<h2>Query Results for {{{ $query->query }}}</h2>
			Queried on {{{ $query->created_at->format('F j, Y \a\t h:i:s a') }}}.<br />
			These results will expire {{{ $query->expire_at->diffForHumans() }}}.
			<div id="resultcontainer">
@foreach ($responses as $response)
				<div id="server_{{ $response->server_id }}" class="row">
					<h4>{{{ $response->server['name'] }}}</h4>
					<div id="server_{{ $response->server_id }}_response" class="col-sm-offset-2 col-sm-8">
						<div class="response">
							<pre style="text-align: left;">{{{ $response->response }}}</pre>
						</div>
					</div>
				</div>
@endforeach
			</div>
		</div>
@endsection