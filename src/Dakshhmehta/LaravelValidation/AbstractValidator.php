<?php namespace Dakshhmehta\LaravelValidation;

use Illuminate\Validation\Factory;
use Dakshhmehta\LaravelValidation\Exceptions\ValidationException;
use Dakshhmehta\Helpers\Template;

abstract class AbstractValidator {
	protected $input = array();
	protected $rules = array();
	protected $messages = array();
	public $validation;

	public function __construct(array $input = null){
		$this->validation = \App::make('validator');

		if($input != null)
			$this->input = $input;
	}

	public function getMessages(){
		return $this->messages;
	}

	public function getInputs(){
		return $this->input;
	}

	public function getRules(){
		return $this->rules;
	}

	public function isValid($data = null){
		if($data != null)
			$this->input = $data;

		$this->validation = $this->validation->make($this->input, $this->rules, $this->messages);

		if($this->validation->passes()){
			return true;
		}

		throw new ValidationException($this->validation, $this->validation->getData());
	}

	/**
	 * Mapping in an array of Laravel Validator class rules
	 * and jQuery validator rules in format as
	 * Laravel Rule => jQuery Rule"
	 */
	private static $mappedRules = array(
		'required'	=>	'required: true',
		//'remote'	=>	'Not implemented'
		'min:(.*)'	=>	'minlength: $1',
		'max:(.*)'	=>	'maxlength: $1',
		'between:(.*),(.*)'	=>	'rangelength: [$1, $2]',
		'email'	=>	'email: true',
		'url'	=>	'url: true',
		'date'	=> 'date: true',
		'integer'	=> 'digits: true',
		'numeric'	=> 'number: true',
		'same:(.*)'	=>	'equalTo: "#$1"',
	);

	/**
	 * Convert the Laravel $rules array to jQuery validate method.
	 *
	 * @param  string  $selector
	 * @param  array $rules optional Laravel validation rules array
	 * @return  string  JQuery code
	 */ 
	public function jQuery($selector, $rules = null)
	{
		Template::addJS(asset('plugins/jquery-validation/dist/jquery.validate.min.js'));

		if($rules == null){
			$this->rules = $this->getRules();
		}
		// Overrie the rules if specified
		else if(is_array($rules) && count($rules) > 0){
			$this->rules = $rules;
		}

		$js = '$("'.$selector.'").validate({';
		$js .= "
				ignore: '',
				errorElement: 'span',
				errorClass: 'help-block',
				highlight: function (element) {
					$(element).closest('.form-group').addClass('has-error');
				},
				unhighlight: function (element) {
					$(element).closest('.form-group').removeClass('has-error');
				},
				errorPlacement: function (error, element)
				{
					if(element.closest('.has-switch').length)
					{
						error.insertAfter(element.closest('.has-switch'));
					}
					else
					if(element.parent('.checkbox, .radio').length || element.parent('.input-group').length)
					{
						error.insertAfter(element.parent());
					} 
					else 
					{
						error.insertAfter(element);
					}
				},
				rules: {";

		$tags = array();
		foreach($this->rules as $field => $rule)
		{
			$tags[$field] = $field.': {';

			if(! is_array($rule))
			{
				$rule = explode('|', $rule);
			}

			$rolls = array();
			foreach($rule as $r)
			{
				$replaced = false;
				foreach(self::$mappedRules as $laravelRule => $jQueryRule)
				{
					$r = preg_replace('/'.$laravelRule.'/', $jQueryRule, $r);
				
					if($r == $jQueryRule)
					{
						$replaced = true;
					}
				}
				
				if($replaced == true)
				{
					$rolls[] = $r;
				}
			}

			$tags[$field] .= implode(', ', $rolls);
			$tags[$field] .= '}';
		}

		$js .= implode(', ', $tags);
		$js .= '}});';

		return $js;
	}
}