<?
/*
	Example handler for form processing


*/

	require_once("class_vtwin.php"); // VTwin class


	// If you use a single file for all your handling, you can wrap each rule/check 
	if ($_REQUEST["submitdemo"]) {

		// Extend the vtwin class with custom validation actions and messages
		// Prefix each method with "custom_"
		class myRules extends vtwin {
			function __construct() { 
				parent::__construct();

				$this->addMessage("custom_uniqueMail", "That email address is not unique. Please login.");
			}

			function custom_uniqueMail($arg) {

				if (0 > 1) {
					return(true);
				} else {
					return(false);
				}
			}
		};


		$v = new myRules(); // Create class obj

		// Define rules for various form fields
		$v->rules(array(
			array(
				"name"    => "yourname",
				"display" => "username",
				"rules"   => "required|alpha"
			),
			array(
				"name"    => "favnumber",
				"display" => "favorite number",
				"rules"   => "numeric"
			),
			array(
				"name"    => "email",
				"display" => "name",
				"rules"   => "required|valid_email",
				"server"  => "uniqueMail"
			)
		));

		// Output changes based on what's asking
		// Ajax calls will return a JSON object; straight POST will return true/false and put error messages into $v->errors
		if ($v->validate()) {
			echo "Form is valid. Do something here.";
		} else {
			echo "Form validation failed<br />";

			foreach ($v->errors as $a_error) {
				echo "- ".$a_error["message"]."<br />";
			}
		}
	}
?>