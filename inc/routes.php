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



/*************************** DRUGS ************************** */
// App::addRoute("GET|POST", "/drugs/?", "Drugs");
// App::addRoute("GET", "/drugs/[i:id]/?", "Drug");
$router->map('GET', '/drugs', 'DrugsController#process');
$router->map('GET', '/drug/[i:id]', 'DrugController#process');


