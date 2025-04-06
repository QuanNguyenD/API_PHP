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
$router->map('POST|GET','/login/?','LoginController#process');
/**************************SIGNUP********************************/
$router->map('POST','/signup/?','SignupController#process');

/*************************DOCTOR*********************** */
$router->map('GET','/doctors/[i:id]','DoctorController#process');
$router->map('GET','/doctors','DoctorsController#process');

/*************************PATIENT*************************** */
$router->map('GET|POST|DELETE|PUT','/patients/[i:id]','PatientController#process');
$router->map('GET','/patients','PatientsController#process');

/**************************SERVICE***************** */

$router->map('GET|POST|DELETE|PUT','/services/[i:id]','ServiceController#process');
$router->map('GET|POST|DELETE|PUT','/services','ServicesController#process');
/****************************DOCTORANDSERVICE******************** */
$router->map('GET|POST|DELETE|PUT','/doctorsandservice/[i:id]','DoctorAndServiceController#process');


/***************************PATIENTBOOKING*********************************** */
$router->map('GET|DELETE|PUT|PATCH','/patient/booking/[i:id]','PatientBookingController#process');
$router->map('GET|DELETE|PUT|POST|PATCH','/patient/booking','PatientBookingsController#process');
/*************************** DRUGS ************************** */
// App::addRoute("GET|POST", "/drugs/?", "Drugs");
// App::addRoute("GET", "/drugs/[i:id]/?", "Drug");
$router->map('GET', '/drugs', 'DrugsController#process');
$router->map('GET', '/drug/[i:id]', 'DrugController#process');

/************************** SPECIALITY ******************************/
$router->map('GET|PUT|DELETE|POST', '/specialities/[i:id]', 'SpecialityController#process');
$router->map('GET|POST','/specialities','SpecialitiesController#process');

/***************************BOOKING*********************************** */
$router->map('GET|DELETE|PUT|PATCH','/bookings/[i:id]','BookingController#process');
$router->map('GET|DELETE|PUT|POST|PATCH','/bookings','BookingsController#process');


/***************************ROOM*********************************** */
$router->map('GET|DELETE|PUT','/rooms/[i:id]','RoomController#process');
$router->map('GET|DELETE|PUT|POST','/rooms','RoomsController#process');

/************************** PATIENT PROFILE ******************************/
// this controller is used by patient to update personal information.
$router->map("GET|POST", "/patient/profile", "PatientProfileController#process");

/************************** APPOINTMENTS ******************************/
$router->map("GET|POST", "/appointments/?", "AppointmentsController#process");
$router->map("GET|PUT|PATCH|DELETE", "/appointments/[i:id]/?", "AppointmentController#process");
$router->map("GET|POST", "/appointment-queue/?", "AppointmentQueueController#process");
$router->map("GET|POST", "/appointment-queue-now/?", "AppointmentQueueNowController#process");
App::addRoute("GET|POST", "/appointment-records/?", "AppointmentRecordsController#process");
App::addRoute("GET|PUT|PATCH|DELETE", "/appointment-records/[i:id]/?", "AppointmentRecordController#process");

/************************** CHART ******************************/
$router->map("GET", "/charts/?", "ChartsController#process");
