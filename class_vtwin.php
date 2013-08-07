<?
class vtwin {
	function __construct() { 
		$this->params = $_REQUEST;

		$this->messages = array(
            "required"=> 'The %s field is required.',
            "matches"=> 'The %s field does not match the %s field.',
            "valid_email"=> 'The %s field must contain a valid email address.',
            "min_length"=> 'The %s field must be at least %s characters in length.',
            "max_length"=> 'The %s field must not exceed %s characters in length.',
            "exact_length"=> 'The %s field must be exactly %s characters in length.',
            "greater_than"=> 'The %s field must contain a number greater than %s.',
            "less_than"=> 'The %s field must contain a number less than %s.',
            "alpha"=> 'The %s field must only contain alphabetical characters.',
            "alpha_numeric"=>'The %s field must only contain alpha-numeric characters.',
            "alpha_dash"=> 'The %s field must only contain alpha-numeric characters, underscores, and dashes.',
            "numeric"=> 'The %s field must contain only numbers.',
            "integer"=> 'The %s field must contain an integer.',
            "decimal"=> 'The %s field must contain a decimal number.',
            "is_natural"=> 'The %s field must contain only positive numbers.',
            "is_natural_no_zero"=> 'The %s field must contain a number greater than zero.',
            "valid_ip"=> 'The %s field must contain a valid IP.',
            "valid_base64"=> 'The %s field must contain a base64 string.',
            "valid_credit_card"=> 'The %s field must contain a vaild credit card number',
            "is_file_type"=> 'The %s field must contain only %s files.',
            "valid_url"=> 'The %s field must contain a valid URL'
		);

		$this->regex = array(
			"alpha"    => "/^[a-z]+$/i",
			"numeric"  => "/^[0-9]+$/",
	        "integer"  => "/^\-?[0-9]+$/",
        	"decimal" => "/^\-?[0-9]*\.?[0-9]+$/",
        	"valid_email" => "/^[a-zA-Z0-9.!#$%&amp;'*+\-\/=?\^_`{|}~\-]+@[a-zA-Z0-9\-]+(?:\.[a-zA-Z0-9\-]+)*$/",
	        "alpha_numeric" => "/^[a-z0-9]+$/i",
        	"alpha_dash" => "/^[a-z0-9_\-]+$/i",
        	"is_natural" => "/^[0-9]+$/i",
        	"is_natural_no_zero" => "/^[1-9][0-9]*$/i",
        	"valid_ip" => "/^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})$/i",
        	"valid_base64" => "/[^a-zA-Z0-9\/\+=]/i",
        	"numeric_dash" => "/^[\d\-\s]+$/",
        	"valid_url" => "/^((http|https):\/\/(\w+:{0,1}\w*@)?(\S+)|)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/",
        	"brackets"  => "/\[(.*?)\]/"
		);

		$this->rules = array();
	}

	function rules($a) { $this->rules = $a; }


	function addMessage($id, $message) {
		$this->messages[$id] = $message;
	}

	function getMessage($id, $compare=false, $name="") {
		$message = $this->messages[$id];

		if ($compare) {
			$find    = strpos($message, "%s");
			$message = substr_replace($message, $name, $find, 2);
			$message = str_replace("%s", $compare, $message);
		} else {
			$message = str_replace("%s", $name, $message);
		}

		return($message);
	}

	function getRuleParam($text) {
		$a_rules = array(
			"matches","min_length","max_length","exact_length","greater_than","less_than"
		);

		$a_result = false;

		foreach ($a_rules as $set) {
			if (substr_count($text, $set)) {
				preg_match($this->regex["brackets"], $text, $matches);

				$a_result = array(
					"compare"=>$matches[1], 
					"name"=>$set
				);

				break;
			}
		}

		
		return($a_result);
	}

	function validateForm($skipFrontServer=false) {
		$errors = array();
		$fields = array();
		
		if ($this->params["fd"]) {
			$a_toValidate = $this->params["fd"];
		} else {
			foreach ($_REQUEST as $k => $v) {
				$a_toValidate[] = array("name"=>$k, "value"=>$v);
			}
		}


		foreach ($a_toValidate as $a_field) {
			$fields[$a_field["name"]] = $a_field["value"];
		}

		// Get each field in the form data
		foreach ($a_toValidate as $a_field) {
			$isValid = true;

			// Loop through each rule
			foreach ($this->rules as $a_rule) {
				if ($a_rule["name"] == $a_field["name"]) { // Find matching field
					$a_rules      = explode("|", $a_rule["rules"]);
					$a_server     = explode("|", $a_rule["server"]);
					$regexCompare = false;



					// Run regex rules against field value
					foreach ($a_rules as $regexName) {
						$complex = $this->getRuleParam($regexName);
						
						if ($complex) {
							$regexName    = $complex["name"];
							$regexCompare = $complex["compare"];
						}



						$message = $this->getMessage($regexName, $regexCompare, $a_rule["display"]);


						switch ($regexName) {
							default:
								$isValid = preg_match($this->regex[$regexName], $a_field["value"]);
								break;

							case "required":
								if (strlen($a_field["value"]) <= 0) {
									$isValid = false;
								}
								break;

							case "min_length":
							
								$size = strlen($a_field["value"]);
								if ($size < $regexCompare) { $isValid = false; }
								break;

							case "max_length":
								$size = strlen($a_field["value"]);
								if ($size > $regexCompare) { $isValid = false; }
								break;

							case "exact_length":
								$size = strlen($a_field["value"]);
								if ($size != $regexCompare) { $isValid = false; }
								break;

							case "greater_than":
								if ($a_field["value"] <= $regexCompare) { $isValid = false; }
								break;

							case "less_than":
								if ($a_field["value"] >= $regexCompare) { $isValid = false; }
								break;

							case "matches":
								if ($a_field["value"] != $fields[$regexCompare]) { $isValid = false; }
								break;

						}

						if (!$isValid) {
							break;
						}
					}

					// Run custom server rules against value, only if others have passed
					if ($isValid and !$skipFrontServer) {
						foreach ($a_server as $regexName) {

							if ($regexName) {
								$regexName = "custom_".$regexName;

								if (!$this->$regexName($a_field["value"])) {
									$message = $this->getMessage($regexName);
									$isValid = false;
								} else {
									$isValid = true;
								}
							}
						}
					}


					// Build return array for fails only
					if (!$isValid) {
						$errors[] = array(
							"id"      => $a_field["name"],
							"name"    => $a_field["name"],
							"rule"    => $regexName,
							"message" => $message
						);
						break;
					}					

					break;
				}
			}
		}

		return($errors);
	}


	function json($s) {
		echo json_encode($s); 
	}

	function validate() {
		$a_deliver = array("status"=>"No data return");

		// Provide rules to JS
		if ($this->params["load"]) {
			$a_deliver = array(
				"rules" => $this->rules,
				"messages" => $this->messages
			);
			$this->json($a_deliver);
			exit();
		
		} else {
			if ($this->params["verify"]) {

				if ($this->params["noserver"]) { $skipFrontServer = true; }
				
				$a_deliver = $this->validateForm($skipFrontServer);

				$this->json($a_deliver);
				exit();
			} else {
				$a_deliver = $this->validateForm();
				if (count($a_deliver) <= 0) {
					return(true);
				} else {
					$this->errors = $a_deliver;
					return(false);
				}
			}
		}
		
	}
}
?>