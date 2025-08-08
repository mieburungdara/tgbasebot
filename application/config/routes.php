<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rute kustom untuk dasbor dan pengaturan
$route['dashboard'] = 'dashboard';
$route['settings'] = 'settings';
$route['migrate'] = 'migrate';

// Rute untuk aksi dasbor
$route['dashboard/delete/(:num)'] = 'dashboard/delete/$1';
$route['dashboard/clear_logs'] = 'dashboard/clear_logs';

// Rute untuk fitur-fitur baru dasbor
$route['dashboard/keywords'] = 'dashboard/keywords';
$route['dashboard/add_keyword'] = 'dashboard/add_keyword';
$route['dashboard/delete_keyword/(:num)'] = 'dashboard/delete_keyword/$1';
$route['dashboard/broadcast'] = 'dashboard/broadcast';
$route['dashboard/send_broadcast'] = 'dashboard/send_broadcast';

// Rute untuk Manajemen Pengguna
$route['user_management'] = 'UserManagement';
$route['user_management/index'] = 'UserManagement/index';
$route['user_management/edit/(:num)'] = 'UserManagement/edit/$1';
$route['user_management/update/(:num)'] = 'UserManagement/update/$1';

// Rute untuk aksi tambahan dasbor
$route['dashboard/delete_broadcast/(:num)'] = 'dashboard/delete_broadcast/$1';
$route['dashboard/export_csv'] = 'dashboard/export_csv';
$route['dashboard/reset_cron_key'] = 'dashboard/reset_cron_key';
$route['dashboard/switch_bot/(:num)'] = 'dashboard/switch_bot/$1';

// Rute untuk cron job
$route['cron/run'] = 'cron/run'; // Untuk semua bot (hanya CLI)
$route['cron/run/(:num)'] = 'cron/run/$1'; // Untuk bot spesifik (URL)

// Rute untuk webhook bot (multi-bot)
$route['bot/webhook/(:any)'] = 'bot_webhook/handle/$1';

// Rute untuk Manajemen Bot
$route['bot_management'] = 'bot_management';
$route['bot_management/add'] = 'bot_management/add';
$route['bot_management/edit/(:num)'] = 'bot_management/edit/$1';
$route['bot_management/update/(:num)'] = 'bot_management/update/$1';
