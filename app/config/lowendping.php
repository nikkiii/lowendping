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
	| API
	|--------------------------------------------------------------------------
	|
	| There is an 'api' which allows a list of servers to be served using json.
	| It is mainly used for programs which wish to interact with LowEndPing instances,
	| and does not provide any extra information.
	|
	*/
	'api' => array(
		'enabled' => false
	),
	
	/*
	|--------------------------------------------------------------------------
	| Result Archive
	|--------------------------------------------------------------------------
	|
	| You can archive LowEndPing results (keep them in the query response table) for a set amount of time.
	| This allows results to be shared and viewed by others.
	| Set 'enabled' to false to disable this and delete responses as soon as they are seen by the client.
	|
	*/
	'archive' => array(
		'enabled' => true,
		'days' => 7
	),
	
	/*
	|--------------------------------------------------------------------------
	| Query settings
	|--------------------------------------------------------------------------
	|
	| This is the timeout used when connecting to the LowEndPing servers, 5 is usually a good value to use.
	|
	*/
	'query' => array(
		'timeout' => 5
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
