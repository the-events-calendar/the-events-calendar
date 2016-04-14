
<?php
//  The below is the Datepicker test.  There are a lot of commented lines to give examples of things
// that I have tried but that did not work.  The problem appears to be getting a hold of and
// manipulating the select box.  Any help/tips are greatly appreciated!
//
// Common errors are either "Element is not currently visible and so may not be interacted with"
// Or, no error but values are not passed


$I = new WpwebdriveracceptanceTester($scenario);
$I->wantTo('Test the datepicker cases');
//$I->resizeWindow(1280, 1024);  ?experimenting here to see if window size has to do with Element not visible error
//$I->resizeWindow(800, 600);

//Login to backend
$I->amOnPage("/wp-login.php");
$I->fillField('Username', 'test_user');
$I->fillField('Password','test_user');
//$I->fillField("#user_login", "admin");
//$I->fillField("#user_pass", "admin");
$I->click('Log In');

//Goto the Event Setting tab and click Display tab
//$I->amOnPage('/wp-admin/edit.php?page=tribe-common&tab=display&post_type=tribe_events');
//$I->click('#display');

//Get Date Format Backend
//$I->scrollTo(["css" => "#tribe-field-weekDayFormat"], 50, 200);
//$I->wait(5);
//$I->waitForElement(['name' => 'datepickerFormat'], 5); // secs
//$I->executeJS('jQuery("datepickerFormat").show()');
//$I->executeJS('jQuery("#datepickerFormat").css("display", "inline")');

//$I->click('#s2id_autogen3');
//$I->click('form select[name=datepickerFormat]');
//$format = $I->grabTextFrom(['name' => 'datepickerFormat']);
//$text = $I->grabTextFrom('option', ['value' => $format ]);

$I->wait(5);
//$text = $I->executeJS('jQuery("option", ["value" => $format ]).text()');



$output = '';




//for ($i=1; $i <=9 ; $i++) { 
//$I->grabTextFrom('select[name=datepickerFormat] option:nth-child(' . $i .')');
//$option = $I->select('select[name=datepickerFormat]', 'value ='.$i);
//$option = $I->grabTextFrom('select[name=datepickerFormat] option:nth-child(' . $i .')');
//$option = $I->executeJS('return $("select[name=datepickerFormat] option:nth-child(' . $i .')").val()');
$array = array("2016-01-15","1/15/2016","01/15/2016","15/1/2016","15/01/2016","1-15-2016","01-15-2016","15-1-2016","15-01-2016");

foreach($array as $ar){
	$option = $ar;
$I->amOnPage('/wp-admin/edit.php?page=tribe-common&tab=display&post_type=tribe_events');
$I->scrollTo(["css" => "#tribe-field-weekDayFormat"], 50, 200);
$I->wait(5);
$I->waitForElement(['name' => 'datepickerFormat'], 5); // secs

$I->click('#s2id_autogen3');

//script is failing here
$I->selectOption("select[name=datepickerFormat]", $option); 
//End fail

$I->click('Save Changes');


//$I->wantTo('Test the datepicker cases ' . $option . '' );

//Move to Frontend event date
$I->amOnPage('/events');

$frontdate = $I->grabValueFrom('#tribe-bar-date');

$result = '';

//For Date Format Y-m-d
if(validateDate($option, 'Y-m-d') == 1){
	$front_status = validateDate($frontdate, 'Y-m-d');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format n/d/Y
if(validateDate($option, 'n/d/Y') == 1){
	$front_status = validateDate($frontdate, 'n/d/Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format m/d/Y
if(validateDate($option, 'm/d/Y') == 1){
	$front_status = validateDate($frontdate, 'm/d/Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format d/n/Y
if(validateDate($option, 'd/n/Y') == 1){
	$front_status = validateDate($frontdate, 'd/n/Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format d/m/Y
if(validateDate($option, 'd/m/Y') == 1){
	$front_status = validateDate($frontdate, 'd/m/Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format n-d-Y
if(validateDate($option, 'n-d-Y') == 1){
	$front_status = validateDate($frontdate, 'n-d-Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format m-d-Y
if(validateDate($option, 'm-d-Y') == 1){
	$front_status = validateDate($frontdate, 'm-d-Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format d-n-Y
if(validateDate($option, 'd-n-Y') == 1){
	$front_status = validateDate($frontdate, 'd-n-Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}

//For Date Format d-m-Y
if(validateDate($option, 'd-m-Y') == 1){
	$front_status = validateDate($frontdate, 'd-m-Y');
	if($front_status == 1){
		$result = 'Matched!';
	}else{
		$result = 'Not Matched!!';
	}
}



$output .= ' ' . $option . ' and ' . $frontdate . ' => ' . $result . ' || ';

$I->amOnPage('/wp-admin/edit.php?page=tribe-common&tab=display&post_type=tribe_events');
$I->click('#display');
}

$I->wantTo('Testing: ' . $output . '' );



//Function for matching Date with Format
function validateDate($date, $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/*//Get Date Format Backend
$format = $I->grabValueFrom(['name' => 'dateWithYearFormat']);
//$I->wantTo('Test the datepicker cases ' . $format . '' );

//Get Date frontend
$I->amOnPage('/event/test-event/');
$date = $I->grabTextFrom('.tribe-event-date-start');
//$I->wantTo('Test the datepicker cases ' . $date . '' );

$status = validateDate($date, $format);

//Display Message according to conditions
if($status == 1){
	$I->wantTo('Format matched!!(' . $date . ' = ' . $format . ')' );
}else{
	$I->wantTo('Format not matched!(' . $date . ' != ' . $format . ')' );
}*/
?>
DatePickerCept.php
Displaying DatePickerCept.php.