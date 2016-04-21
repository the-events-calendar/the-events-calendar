
<?php
//  The below is the Datepicker test.  There are a lot of commented lines to give examples of things
// that I have tried but that did not work.  The problem appears to be getting a hold of and
// manipulating the select box.  Any help/tips are greatly appreciated!
//
// Common errors are either "Element is not currently visible and so may not be interacted with"
// Or, no error but values are not passed


$I = new WpwebdriveracceptanceTester($scenario);
$I->wantTo('Test the datepicker cases');

//Login to backend
$I->amOnPage("/wp-login.php");
$I->fillField('Username', 'test_user');
$I->fillField('Password','test_user');
//$I->fillField("#user_login", "admin");
//$I->fillField("#user_pass", "admin");
$I->click('Log In');

$I->wait(5);

$output = '';

$result = '';

$array = array("2016-01-15","1/15/2016","01/15/2016","15/1/2016","15/01/2016","1-15-2016","01-15-2016","15-1-2016","15-01-2016");
$i = 0;

foreach($array as $ar){
	$option = $ar;

	//Goto the Event Setting tab and click Display tab
	$I->amOnPage('/wp-admin/edit.php?page=tribe-common&tab=display&post_type=tribe_events');
	$I->waitForElement(['name' => 'datepickerFormat'], 5); // secs
	$I->scrollTo(["css" => "#tribe-field-weekDayFormat"]);
	$jQueryDatepicker = 'jQuery(\'select[name="datepickerFormat"]\').val(' . $i  . ').prop("selected","selected").change();';
	$I->executeJS($jQueryDatepicker);
	$I->wait(2);
	$I->click('Save Changes');

	//Increment $i
	$i = $i + 1;

	//Move to Frontend event date
	$I->amOnPage('/events/today/');

	$jQueryDay = 'jQuery(\'#tribe-bar-date\').val()';

	$frontdate = $I->executeJS('return jQuery("#tribe-bar-date").val()');
	
	$I->wait(2);

	//For Date Format Y-m-d
	if(validateDate($option, 'Y-m-d') == 1){
		$front_status = validateDate($frontdate, 'Y-m-d');
		
		if($front_status == 1){
			$result = '1. Y-m-d Matched!';
			$I->comment('1. Frontdate is format Matched');
		}else{
			$result = '1. Y-m-d Not Matched!!';
			throw new \InvalidArgumentException('1. Not Matched!!');
		}
	}

	//For Date Format n/d/Y
	if(validateDate($option, 'n/d/Y') == 1){
		$front_status = validateDate($frontdate, 'n/d/Y');
		if($front_status == 1){
			$result = '2. n/d/Y Matched!';
			$I->comment('2. Frontdate is format Matched');
		}else{
			$result = '2. n/d/Y Not Matched!!';
			throw new \InvalidArgumentException('2. Not Matched!!');
		}
	}

	//For Date Format m/d/Y
	if(validateDate($option, 'm/d/Y') == 1){
		
		$I->comment('Frontdate is format m/d/Y');
		if($front_status == 1){
			$result = '3. m/d/Y Matched!';
			$I->comment('3. Frontdate is format Matched');
		}else{
			$result = '3. m/d/Y Not Matched!!';
			throw new \InvalidArgumentException('3. Not Matched!!');
		}
	}

	//For Date Format d/n/Y
	if(validateDate($option, 'd/n/Y') == 1){
	
		$I->comment('Frontdate is format d/n/Y');
		if($front_status == 1){
			$result = '4. d/n/Y Matched!';
			$I->comment('4. Frontdate is format Matched');
		}else{
			$result = '4. d/n/Y Not Matched!!';
			throw new \InvalidArgumentException('4. Not Matched!!');
		}
	}

	//For Date Format d/m/Y
	if(validateDate($option, 'd/m/Y') == 1){
		$front_status = validateDate($frontdate, 'd/m/Y');
		if($front_status == 1){
			$result = '5. d/m/Y Matched!';
			$I->comment('5. Frontdate is format Matched');
		}else{
			$result = '5. d/m/Y Not Matched!!';
			throw new \InvalidArgumentException('5. Not Matched!!');
		}
	}

	//For Date Format n-d-Y
	if(validateDate($option, 'n-d-Y') == 1){
		$front_status = validateDate($frontdate, 'n-d-Y');
		if($front_status == 1){
			$result = '6. Matched!';
			$I->comment('6. Frontdate is format Matched');
		}else{
			$result = '6. Not Matched!!';
			throw new \InvalidArgumentException('6. Not Matched!!');
		}
	}

	//For Date Format m-d-Y
	if(validateDate($option, 'm-d-Y') == 1){
		$front_status = validateDate($frontdate, 'm-d-Y');
		if($front_status == 1){
			$result = '7. Matched!';
			$I->comment('7. Frontdate is format Matched');
		}else{
			$result = '7. Not Matched!!';
			throw new \InvalidArgumentException('7. Not Matched!!');
		}
	}

	//For Date Format d-n-Y
	if(validateDate($option, 'd-n-Y') == 1){
		$front_status = validateDate($frontdate, 'd-n-Y');
		if($front_status == 1){
			$result = '8. Matched!';
			$I->comment('8. Frontdate is format Matched');
		}else{
			$result = '8. Not Matched!!';
			throw new \InvalidArgumentException('8. Not Matched!!');
		}
	}

	//For Date Format d-m-Y
	if(validateDate($option, 'd-m-Y') == 1){
		$front_status = validateDate($frontdate, 'd-m-Y');
		if($front_status == 1){
			$result = '9. Matched!';
			$I->comment('9. Frontdate is format Matched');
		}else{
			$result = '9. Not Matched!!';
			throw new \InvalidArgumentException('9. Not Matched!!');
		}	
	}

$output .= ' ' . $option . ' and ' . $frontdate . ' => ' . $result . ' || ';

}

$I->wantTo('Testing: ' . $output . '' );



//Function for matching Date with Format
function validateDate($date, $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}


?>
