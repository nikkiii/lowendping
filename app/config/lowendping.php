<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Servers
	|--------------------------------------------------------------------------
	|
	| Servers must be defined with a numeric key, as it is used in selection and responses.
	|
	*/
	'servers' => array(
		1 => array('name' => 'Example', 'host' => 'localhost', 'port' => 12337, 'auth' => 'SOME_SECURE_STRING')
	),

	/*
	|--------------------------------------------------------------------------
	| Rate limiting
	|--------------------------------------------------------------------------
	|
	| Rate limit IP addresses so they cannot spam queries.
	| Set 'queries' to 0 or false to disable
	|
	*/
	'ratelimit' => array(
		'queries' => 60,
		'timespan' => 1440
	),


	/*
	|--------------------------------------------------------------------------
	| Enabled query types
	|--------------------------------------------------------------------------
	|
	| Disable available query types by commenting out lines. By default, everything is enabled.
	|
	*/
	'querytypes' => array(
		'ping' => 'ping',
		'ping6' => 'ping6',
		'trace' => 'traceroute',
		'trace6' => 'traceroute6',
		'mtr' => 'mtr',
		'mtr6' => 'mtr6'
	)

);
