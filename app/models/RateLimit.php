<?php

class RateLimit extends Eloquent {

	protected $table = 'ratelimit';
	
	protected $primaryKey = 'ip';
	
	public $timestamps = false;
}