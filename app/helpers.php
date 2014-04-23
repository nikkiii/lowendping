<?php
function websocket_url($path = '', $port = false, $tail = '', $scheme = 'ws://') {
	$root = Request::getHost();
	
	$start = starts_with($root, 'http://') ? 'http://' : 'https://';
	
	$root = $scheme . $root;
	
	$root .= ':' . ($port ? $port : Request::getPort());
	
	return trim($root.($path ? '/'.trim($path.'/'.$tail, '/') : ''), '/');
}