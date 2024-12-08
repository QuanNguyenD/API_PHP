<?php
/**
 * Special version of htmlspecialchars
 * @param  string $string 
 * @return string 
 */
function htmlchars($string = "")
{
    return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
}



/**
 * Truncate string
 * @param  string  $string     
 * @param  integer $max_length Max length of result
 * @param  string  $ellipsis   
 * @param  boolean $trim       
 * @return string              
 */
function truncate_string($string = "", $max_length = 50, $ellipsis = "...", $trim = true)
{
    $max_length = (int)$max_length;
    if ($max_length < 1) {
        $max_length = 50;
    }

    if (!is_string($string)) {
        $string = "";
    }

    if ($trim) {
        $string = trim($string);
    }

    if (!is_string($ellipsis)) {
        $ellipsis = "...";
    }

    $string_length = mb_strlen($string);
    $ellipsis_length = mb_strlen($ellipsis);
    if($string_length > $max_length){
        if ($ellipsis_length >= $max_length) {
            $string = mb_substr($ellipsis, 0, $max_length);
        } else {
            $string = mb_substr($string, 0, $max_length - $ellipsis_length)
                    . $ellipsis;
        }
    }

    return $string;
}



/**
 * Create SEO friendly url slug from string
 * @param  string $string 
 * @return string         
 */
function url_slug($string = "")
{
    if (!is_string($string)) {
        $string = "";
    }

    $s = trim(mb_strtolower($string));
    
    // Replace azeri characters
    $s = str_replace(
        array("ü", "ö", "ğ", "ı", "ə", "ç", "ş"), 
        array("u", "o", "g", "i", "e", "c", "s"), 
        $s);
    
    // Replace cyrilic characters
    $cyr = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м',
                 'н','о','п','р','с','т','у', 'ф','х','ц','ч','ш','щ','ъ', 
                 'ы','ь', 'э', 'ю','я');
    $lat = array('a','b','v','g','d','e','io','zh','z','i','y','k','l',
                 'm','n','o','p','r','s','t','u', 'f', 'h', 'ts', 'ch',
                 'sh', 'sht', 'a', 'i', 'y', 'e','yu', 'ya');
    $s = str_replace($cyr, $lat, $s);

    // Replace all other characters
    $s = preg_replace("/[^a-z0-9]/", "-", $s);

    // Replace consistent dashes
    $s = preg_replace("/-{2,}/", "-", $s);

    return trim($s, "-");
}


/**
 * Delete file or folder (with content)
 * @param string $path Path to file or folder
 */
function delete($path)
{
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            delete(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }

    return false;
}



/**
 * Format price
 * @param  decimal $price 
 * @param  boolean $zdc Defines the currency mod. TRUE for zero decimal 
 *                      currencies, FALSE for regular currencies 
 * @return string        
 */
function format_price($price, $zdc = false){
    if (!is_numeric($price)) {
        // Not a number, retunr as is
        return $price;
    }

    if ($zdc) {
        // Round the price until zero decimal precision
        return round($price);
    } else {
        $price = number_format($price, 2, ".", "");
        $parts = explode(".", $price, 2);
        return $parts[0] . ".<sup>" . $parts[1] . "</sup>";
    }
}



/**
 * Get an array of timezones
 * @return array
 */
function getTimezones()
{
    $timezoneIdentifiers = DateTimeZone::listIdentifiers();
    $utcTime = new DateTime('now', new DateTimeZone('UTC'));
 
    $tempTimezones = array();
    foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);
 
        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
    }
 
    // Sort the array by offset,identifier ascending
    usort($tempTimezones, function($a, $b) {
        return ($a['offset'] == $b['offset'])
            ? strcmp($a['identifier'], $b['identifier'])
            : $a['offset'] - $b['offset'];
    });
 
    $timezoneList = array();
    foreach ($tempTimezones as $tz) {
        $sign = ($tz['offset'] > 0) ? '+' : '-';
        $offset = gmdate('H:i', abs($tz['offset']));
        $timezoneList[$tz['identifier']] = '(UTC ' . $sign . $offset . ') ' .
            $tz['identifier'];
    }
 
    return $timezoneList;
}


/**
 * Validate date
 * @param  string  $date   date string
 * @param  string  $format 
 * @return boolean         
 */
function isValidDate($date, $format = 'Y-m-d H:i:s')
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}





/**
 * textInitials
 * @param  string  $text   
 * @param  integer $length result length
 * @return string          
 */
function textInitials($text, $length=1)
{
    $text = (string)$text;
    $length = (int)$length;

    if (mb_strlen($text) < $length || $length < 1) {
        return $text;
    }

    $parts = explode(" ", $text);
    foreach ($parts as &$p) {
        if (trim($p) == "") {
            unset($p);
        }
    }

    if (count($parts) >= $length) {
        $res = "";
        for ($i = 0; $i < $length; $i++) {
            $res .= mb_substr($parts[$i], 0, 1);
        }
    } else {
        if ($length == 1) {
            $res =  mb_substr($text, 0, 1);
        } else if ($length == 2) {
            $res =  mb_substr($text, 0, 1).mb_substr($text, -1, 1);
        } else {
            $res =  mb_substr($text, 0, $length);
        }
    }

    return $res;
}


/**
 * Generate human readable random text
 * @param  integer $length length of the returned string
 * @return string          Random string
 */
function readableRandomString($length = 6)
{  
    $string     = '';
    $vowels     = array("a","e","i","o","u");  
    $consonants = array(
        'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 
        'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
    );  
    // Seed it
    srand((double) microtime() * 1000000);
    $max = $length/2;
    for ($i = 1; $i <= $max; $i++)
    {
        $string .= $consonants[rand(0,19)];
        $string .= $vowels[rand(0,4)];
    }
    return $string;
}

/**
 * Convert size in byte to human readable text
 * 
 * @param  integer  $size      size in bytes
 * @param  integer $precision 
 * @return string|bool             
 */
function readableFileSize($size, $precision = 2) {
    if ($size < 0) {
        $size = 0;
    }

    $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $step = 1024;
    $i = 0;

    $max = count($units) - 1;

    while (($size / $step) > 0.9) {
        $size = $size / $step;
        $i++;

        if ($i > $max) {
            return false;
        }
    }

    return round($size, $precision). $units[$i];
}


/**
 * Convert numbers to human readable formats (Ex: 3K, 3.4M)
 * @param  integer $numbers Number to convert
 * @return string          
 */
function readableNumber($numbers, $precision = 2)
{
   $readable = ["",  "K", "M", "B"];
   $index = 0;

   while($numbers > 1000){
      $numbers /= 1000;
      $index++;
   }

   return round($numbers, $precision) ." ". $readable[$index];
}










/**
 * Get data from general data
 * @param  string $name  data identifier
 * @param  string $field 
 * @return string        
 */
function general_data($name, $field = null)
{
    if (!is_string($name)) {
        return null;
    }


    if (!isset($GLOBALS["General_Data"]) || !is_array($GLOBALS["General_Data"])) {
        $GLOBALS["General_Data"] = array();
    }

    if (isset($GLOBALS["General_Data"][$name])) {
        $settings = $GLOBALS["General_Data"][$name];
    } else {
        $settings = Controller::model("GeneralData", $name);
        $GLOBALS["General_Data"][$name] = $settings;
    }


    if (is_string($field)) {
        return htmlchars($settings->get("data.".$field));
    } 

    return $settings;
}


/**
 * Get settings 
 * @return string
 */
function site_settings($field = null)
{
    return general_data("settings", $field);
}


/**
 * Get integrations settings 
 * @return string
 */
function integrations($field = null)
{
    return general_data("integrations", $field);
}


/**
 * Check if the $currency is the zero decimal currency
 * @param  string  $currency Currency to be checked
 * @return boolean           
 */
function isZeroDecimalCurrency($currency)
{
    if (!is_string($currency)) {
        return false;
    }

    $zero_decimal_currencies = [
        "BIF", "CLP", "DJF", "GNF", "JPY", "KMF", "KRW",
        "MGA", "PYG", "RWF", "VND", "VUV", "XAF", "XOF", "XPF",

        "HUF", "TWD"
    ];

    return in_array(strtoupper($currency), $zero_decimal_currencies);
}







/**
 * Add a new option or update if it exists
 * @param string  $option_name  Name of the option
 * @param string|int  $option_value Value of the option
 */
function save_option($option_name, $option_value) 
{
    if (!is_string($option_name)) {
        // Option name must be string
        return false;
    }

    if ($option_value === false || $option_value === null) {
        $option_value = "";
    }

    // Save to the database
    $opt = \Controller::model("Option", $option_name);
    $opt->set("option_name", $option_name)
        ->set("option_value", $option_value)
        ->save();

    return true;
}





/**
 * Get/Set option values [code, shortcode, name, localname] for the ACTIVE_LANG
 * @param  string $option Name of the option
 * @param  string|null $value  value of the option to set. If null don't update the value
 * @return string|null         Value of the option or null (if not found)
 */
function active_lang($option, $value = null)
{
    $options = ["code", "shortcode", "name", "localname"];

    if (!in_array($option, $options)) {
        // Invalid option name
        return null;
    }

    if (!defined('ACTIVE_LANG')) {
        // Active lang is not defined,
        // It's too early to call this function yet
        return null;       
    }

    if (is_null($value)) {
        if (Config::get("active_lang_".$option)) {
            // Found the required value
            return Config::get("active_lang_".$option);
        }   

        // Search for the value of the option
        foreach (Config::get("applangs") as $al) {
            if ($al["code"] == ACTIVE_LANG) {
                // found, break loop
                foreach ($al as $key => $value) {
                    Config::set("active_lang_".$key, $value);
                }
                break;
            }
        }

        // Return the option value.
        // If the option is not found in the foreach loop above
        // then NULL value will be returned automatically. See Config::get()
        return Config::get("active_lang_".$option);
    } else {
        Config::set("active_lang_".$option, $value);
        return Config::get("active_lang_".$option);
    }
}

/**
 * Check json is invalid
 * @param $str is json string
 * @return boolean
 */
function isValidJSON($str) {
    json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
 }

 function isNumber($number){
    $result = preg_match("/^\\d+$/", $number);
    return $result;
}

function isVietnameseName($name){
    $regex = "/^[a-zA-Z_ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễếệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s]+$/u";
    $result = preg_match($regex, $name);
    return $result;
}

function isAddress($address){
    $regex = "/^[a-zA-Z0-9_ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễếệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s,-]+$/u";
    $result = preg_match($regex, $address);
    return $result;
}
function isBirthdayValid($date)
{
    $output = "";
    $year = (int)substr($date, 0,4);
    $month = (int)substr($date,5,8);
    $day = (int)substr($date,8,10);


    $yearToday = (int)date("Y");
    $monthToday = (int)date("m");
    $dayToday = (int)date("d");


    $yearDifference  = $yearToday - $year;
    $monthDifference = $monthToday - $month;
    $dayDifference   = $dayToday - $day;

    $today = date("D, d-m-Y");

    /*Step 3.3 - Case 1 -  is not valid*/
    $date_validation = checkdate($month, $day, $year);
    if( !$date_validation )
    {
        $output = "Your birthday - ".$date." - does not exist in Calendar !";
        return $output;
    }
    /*Step 3.3 - Case 2 - year(2023) > yearToday(2022)*/
    if( $yearDifference < 0)
    {
        $output = "Today is ".$today." so that this birthday is not valid !";
        return $output;
    }
    /*Step 3.3 - Case 3 - year == yearToday*/
    else if( $yearDifference == 0)
    {
        //Case 3.1. month > monthToday
        if( $monthDifference < 0  )
        {
            $output = "Today is ".$today." so that this birthday is not valid !";
            return $output;
        }
        //Case 3.2. month == monthToday
        else if( $monthDifference == 0)
        {
            // day = 15 but dayToday = 13
            if( $dayDifference < 0)
            {
                $output = "Today is ".$today." so that this birthday is not valid !";
                return $output;
            }
        }
        //Case 3.3. month < monthToday
        else
        {
            // do thing
        }
    }
    /*Step 3.3 - Case 4 - year < yearToday*/
    else
    {
        //always correct
    }
    return $output;
}

function isAppointmentDateValid($appointment_date)
{
    /**Step 1 - check date */
    if( !$appointment_date )
    {
        $output = "Appointment time has incorrect format. Try again !";
        return $output;
    }

    $output = "";
    $year = (int)substr($appointment_date, 0,4);
    $month = (int)substr($appointment_date,5,8);
    $day = (int)substr($appointment_date,8,10);


    $yearToday = (int)date("Y");
    $monthToday = (int)date("m");
    $dayToday = (int)date("d");

    $yearDifference  = $year - $yearToday;
    $monthDifference = $month - $monthToday;
    $dayDifference   = $day - $dayToday;

    $today = date("D, d-m-Y");

    /*Step 2 - Case 1 - the date does not exist*/
    $appointment_date_validation = checkdate($month, $day, $year);
    if( !$appointment_date_validation )
    {
        $output = "Your appointment day - ".$appointment_date." - does not exist !";
        return $output;
    }
    /*Step 2 - Case 2 - year(2023) > yearToday(2022)*/
    if( $yearDifference > 0 )
    {
        //always correct
    }
    /**Step 2 - Case 3 - year(2022) == yearToday(2022) */ //today is 18-10-2022
    else if( $yearDifference == 0)
    {
        /** Case 3.1 - month(10) == monthToday(10) */
        if( $monthDifference == 0)
        {
            /**Case 3.1.2 - day(17) < dayToday(18) */
            if( $dayDifference < 0)
            {
                $output = "Today is ".$today." so that is not valid !";
                return $output;
            }
            /**Case 3.1.2 - day(19) >= dayToday(18)  */
            else
            {
                //always correct
            }
        }
        /** Case 3.2 - month(09) < monthToday(10) */
        else if( $monthDifference < 0)
        {
            $output = "Today is ".$today." so that is not valid !";
            return $output;
        }
        /** Case 3.3 - month(11) > monthToday(10) */
        else
        {
            //always correct
        }
    }
    /**Step 2 - Case 4 - year(2019) < yearToday (2022) */
    else
    {
        $output = "Today is ".$today." so that is not valid !";
        return $output;
    }

    return $output;
}
function isAppointmentHourValid($appointment_hour, $appointment_date)
{
    /**Step 1 declare & get necessary data*/
    $output = "";
    $hour = (int)substr($appointment_hour,0,2);
    $minute = (int)substr($appointment_hour,3) ? substr($appointment_hour,3) : "00" ;

    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $currentHour = (int)Date("H");
    $currentMinute = (int)Date("i");

    // print_r("\n now: ".date("Y-m-d H:i:s"));
    // print_r("\n current hour: ".$currentHour);
    // print_r("\n current minute: ".$currentMinute);
    // print_r("\n hour: ".$hour);
    // print_r("\n minute: ".$minute);
    // print_r("\n");


    if(!$hour || !$minute)
    {
        $output = "Appointment time has incorrect format. Try again !";
        return $output;
    }



    /**Step 2 - If hour does not greater than 7 am and less than 20 => not valid */
    if( $hour < 7 || $hour > 20 )
    {
        $output = "Our working hours from 7 am to 20 am. Please, try again !";
        return $output;
    }
    //** appointment time is 20:10 pm, it is valid ? NO */
    else if( $hour == 20 && $minute > 0 )
    {
        $output = "Our working hours from 7 am to 20 am. Please, try again !";
        return $output;
    }



    /**Step 3 - minute validation */
    if( $minute < 0 || $minute > 60 )
    {
        $output = "Minute value is not valid - 0 <= minute <= 60";
        return $output;
    }



    /**Step 4 - compare with current time */
    $year = (int)substr($appointment_date, 0,4);
    $month = (int)substr($appointment_date,5,8);
    $day = (int)substr($appointment_date,8,10);

    $yearToday = (int)date("Y");
    $monthToday = (int)date("m");
    $dayToday = (int)date("d");

    $yearDifference  = $year - $yearToday;
    $monthDifference = $month - $monthToday;
    $dayDifference   = $day - $dayToday;
    /**Step 4 - Case 1 - appointment hour < currentHour */
    if( $hour < $currentHour )
    {
        /**Step 4 - Case 1.1 - Appointment date is today or tomorrow? If tomorrow, hour is still valid */
        if( $yearDifference >= 0 && $monthDifference >= 0 )
        {
            /**(current)18-10-2022 17:15pm VS (appointment)18-10-2022 16:00pm & $hour must in working hours
             * => CORRECT  */
            if( $dayDifference == 0 && $currentHour < $hour && $currentHour > 7 && $currentHour < 20)
            {
                # always correct
            }
            /**(current)18-10-2022 17:15pm VS (appointment)19-10-2022 16:00pm => CORRECT  */
            else if( $dayDifference > 0 )
            {
                # always correct
            }
            else 
            {
                $output = "Now is ".date("H:i")." so that appointment time is not valid. Try again !";
                return $output;
            }
        }
        /**Step 4 - Case 1.2 - Appointment date is previous days => INCORRECT */
        else
        {
            $output = "Now is ".date("H:i")." so that appointment time is not valid. Try again !";
            return $output;
        }
        
    }
    /**Step 4 - Case 2 - appointment time == currentHour */
    else if( $hour == $currentHour )
    {
        if( $minute <= $currentMinute )
        {
            /**Step 4 - Case 2.1 - time now (09:50) & time appointment (09:30) BUT appointment date is tomorrow */
            if( $yearDifference >= 0 && $monthDifference >= 0 && $dayDifference > 0)
            {
                # always correct
            }
            else
            {
                $output = "Now is ".date("H:i")." so that appointment time is not valid. Try again !";
                return $output;
            }
           
        }
        else 
        {
            # always correct
        }
    }
    /**$hour > $currentHour */
    else
    {
        # always correct
    }
    return $output;
}
function isAppointmentTimeValid($date)
{
    /**Step 1 */
    if( !$date )
    {
        $output = "Appointment time has incorrect format. Try again !";
        return $output;
    }
    
    
    /**Step 2 */
    $output = "";
    $appointment_date = substr($date,0, 10 );
    $appointment_hour = substr($date, 11);


    $output = isAppointmentDateValid($appointment_date);
    /**Step 3 - if appointment date is not valid => return message */
    if( !empty($output) )
    {
        return $output;
    }


    /**Step 4 - verify appointment time*/
    $output = isAppointmentHourValid($appointment_hour, $appointment_date);
    return $output;
}























