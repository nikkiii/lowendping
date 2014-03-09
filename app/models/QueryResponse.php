<?php

class QueryResponse extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'responses';
	
	public function scopeUnsent($query) {
		return $query->where('sent', '=', '0');
	}
	
	public function serverQuery() {
		return $this->belongsTo('Query');
	}
}