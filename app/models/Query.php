<?php

class Query extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'queries';
	
	public function responses() {
		return $this->hasMany('QueryResponse');
	}
}