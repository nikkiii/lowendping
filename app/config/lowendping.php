<?php

return array(

	'servers' => array(
		1 => array('name' => 'Example', 'host' => 'localhost', 'port' => 12337, 'auth' => 'SOME_SECURE_STRING')
	),
	
	'querytypes' => array(
		'ping' => 'ping',
		'ping6' => 'ping6',
		'trace' => 'traceroute',
		'trace6' => 'traceroute6',
		'mtr' => 'mtr',
		'mtr6' => 'mtr6'
	)

);
