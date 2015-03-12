<?php namespace Dakshhmehta\LaravelValidation;

use Exception;
use Illuminate\Validation\Validator;

class ValidationException extends Exception {
	protected $validation;
	protected $input;

	public function __construct(Validator $validation, array $data = array(), string $message = null){
		$this->code = 400;
		$this->validation = $validation;
		$this->input = $data;

		if($message == null && count($data) > 0){
			foreach($this->validation->getData() as $key => $value){
				if($this->getErrors()->first($key) != null){
					$this->message = 'Input:'.$this->input[$key].'|'.$this->getErrors()->first($key);
					break;
				}
			}
		}
		else {
			$this->message = $this->getErrors()->first();
		}
	}

	public function getErrors(){
		return $this->validation->errors();
	}
}