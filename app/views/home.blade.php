@extends('layouts.main')

@section('content')
		<div class="content text-center">
			<form id="queryform">
				<div class="row">
					<div class="col-sm-offset-4 col-sm-4">
						<h4>Query (Host/IP)</h4>
						{{ Form::text('query', Request::getClientIp(), array('class' => 'form-control')) }}
					</div>
				</div>
				<br />
				<div class="row">
					<div class="col-sm-offset-5 col-sm-2">
					{{ Form::select('type', Config::get('lowendping.querytypes'), 'ping', array('class' => 'form-control')) }}
					</div>
				</div>
				<br />
				<a href="#servers" id="serverlink">Servers ({{ count($servers) }})</a>
				<div class="row">
					<div id="servers" class="col-sm-offset-4 col-sm-4" style="display: none;">
						<label class="checkbox">
							<input id="mastercheck" type="checkbox" checked="checked" /> <strong>All Servers</strong>
						</label>
@foreach ($servers as $id => $server)
						<label class="checkbox">
							<input type="checkbox" name="servers[{{ $id }}]" value="1" checked="checked" /> {{ $server['name'] }}
						</label>
@endforeach
					</div>
				</div>
				<br />
				<input class="btn btn-default" type="submit" name="submit" value="Submit" data-loading-text="Working" data-complete-text="Submit" />
			</form>
			<div id="resultcontainer">
@foreach ($servers as $id => $server)
				<div id="server_{{ $id }}" class="row hidden">
					<h4>{{{ $server['name'] }}}</h4>
					<div id="server_{{ $id }}_response" class="col-sm-offset-2 col-sm-8">
						
					</div>
				</div>
@endforeach
			</div>
		</div>
@endsection