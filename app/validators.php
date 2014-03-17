<?php
class QueryValidator extends Illuminate\Validation\Validator {
	public function validateQuery($attribute, $value, $parameters) {
		$data = $this->getData();
		
		if (empty($data['type'])) {
			return false;
		}
		
		$iptype = endsWith($data['type'], '6') ? 6 : 4;
		
		// Check for IP address first
		if (!$this->checkAddressType($value, $iptype)) {
			$check = @dns_get_record($value, $iptype == 4 ? DNS_A : DNS_AAAA);
			
			$field = $iptype == 4 ? 'ip' : 'ipv6';
			
			if (empty($check) || empty($check[0]) || empty($check[0][$field])) {
				return false; // Failed to resolve
			}
			
			$check = $check[0][$field];
			
			if (!$check || !$this->checkAddressType($check, $iptype)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function validateServer($attribute, $value, $parameters) {
		return array_key_exists($value, Config::get('lowendping.servers'));
	}
	
	public function validateType($attribute, $value, $parameters) {
		return array_key_exists($value, Config::get('lowendping.querytypes'));
	}
	
	private function checkAddressType($query, $type = 4) {
		if ($type == 4 && filter_var($query, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) && $query != '255.255.255.255') {
			return true;
		} else if ($type == 6 && filter_var($query, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return true;
		}
		return false;
	}
}

Validator::resolver(function($translator, $data, $rules, $messages) {
	return new QueryValidator($translator, $data, $rules, $messages);
});

function endsWith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}