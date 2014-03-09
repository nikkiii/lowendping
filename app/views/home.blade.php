@extends('layouts.main')

@section('content')
		<div class="page-header">
			<h1>{{ Config::get('app.name') }}</h1>
		</div>
		<div class="content text-center">
			<form id="queryform">
				<h4>Query (Host/IP)</h4>
				<input id="querytype" type="hidden" name="type" value="" />
				<input type="text" name="query" value="" />
				<br />
				<a href="#servers" id="serverlink">Servers ({{ count($servers) }})</a>
				<div class="row">
					<div id="servers" class="col-sm-offset-4 col-sm-4" style="display: none;">
						<label class="checkbox">
							<input id="mastercheck" type="checkbox" checked="true" /> <strong>All Servers</strong>
						</label>
@foreach ($servers as $id => $server)
						<label class="checkbox">
							<input type="checkbox" name="servers[{{ $id }}]" value="1" checked="true" /> {{ $server['name'] }}
						</label>
@endforeach
					</div>
				</div>
				<br />
				<input id="ping" class="btn btn-info" type="submit" name="ping" value="Ping" data-loading-text="Working" data-complete-text="Ping" />
				<input id="trace" class="btn btn-default" type="submit" name="trace" value="Trace" data-loading-text="Working" data-complete-text="Trace" />
			</form>
			<div id="resultcontainer">
@foreach ($servers as $id => $server)
				<div id="server_{{ $id }}" class="row hidden">
					<h4>{{{ $server['name'] }}}</h4>
					<div id="server_{{ $id }}_response" class="col-sm-offset-3 col-sm-6">
						
					</div>
				</div>
@endforeach
			</div>
		</div>
@endsection