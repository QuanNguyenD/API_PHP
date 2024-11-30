<?php 
// // Language slug
// // 
// // Will be used theme routes
// require_once APPPATH.'/Core/Config.php';
// $langs = [];
// foreach (Config::get("applangs") as $l) {
//     if (!in_array($l["code"], $langs)) {
//         $langs[] = $l["code"];
//     }

//     if (!in_array($l["shortcode"], $langs)) {
//         $langs[] = $l["shortcode"];
//     }
// }
// $langslug = $langs ? "[".implode("|", $langs).":lang]" : "";




/**************************LOGIN***************************** */
$router->map('GET|POST','/login/?','LoginController#process');
/**************************SIGNUP********************************/
$router->map('POST','/signup/?','SignupController#process');

/*************************DOCTOR*********************** */
$router->map('GET','/doctor/[i:id]','DoctorController#process');
$router->map('GET','/doctors','DoctorsController#process');

/*************************PATIENT*************************** */
$router->map('GET|POST|DELETE|PUT','/patients/[i:id]','PatientController#process');
$router->map('GET','/patients','PatientsController#process');

/**************************SERVICE***************** */

$router->map('GET|POST|DELETE|PUT','/service/[i:id]','ServiceController#process');
$router->map('GET|POST|DELETE|PUT','/service','ServicesController#process');
/****************************DOCTORANDSERVICE******************** */
$router->map('GET|POST|DELETE|PUT','/doctorsandservice/[i:id]','DoctorAndServiceController#process');


/***************************PATIENTBOOKING*********************************** */
$router->map('GET|DELETE|PUT','/patient/booking/[i:id]','PatientBookingController#process');
$router->map('GET|DELETE|PUT|POST','/patient/booking','PatientBookingsController#process');
/*************************** DRUGS ************************** */
// App::addRoute("GET|POST", "/drugs/?", "Drugs");
// App::addRoute("GET", "/drugs/[i:id]/?", "Drug");
$router->map('GET', '/drugs', 'DrugsController#process');
$router->map('GET', '/drug/[i:id]', 'DrugController#process');

/************************** SPECIALITY ******************************/
$router->map('GET|PUT|DELETE|POST', '/speciality/[i:id]', 'SpecialityController#process');
$router->map('GET|POST','/specialities','SpecialitiesController#process');

/***************************BOOKING*********************************** */
$router->map('GET|DELETE|PUT','/bookings/[i:id]','BookingController#process');
$router->map('GET|DELETE|PUT|POST','/bookings','BookingsController#process');

