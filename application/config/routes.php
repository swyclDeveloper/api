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
| example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
| https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
| $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
| $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples: my-controller/index -> my_controller/index
|   my-controller/my-method -> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'InitController/error404';
$route['translate_uri_dashes'] = true;

/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
$route['v1/users/(:num)'] = 'v1/api/UserController/users/id/$1';
$route['v1/users/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'v1/api/UserController/users/id/$1/format/$3$4';
$route['v1/users/regist'] = 'v1/api/UserController/regist';
//$route['v1/users/regist/temp']	= 'v1/api/UserController/registTemp';
$route['v1/users/login']		= 'v1/api/UserController/login';
$route['v1/users/logout']		= 'v1/api/UserController/logout';
$route['v1/users/reflash']		= 'v1/api/UserController/reflash';
$route['v1/users/info']			= 'v1/api/UserController/userInfo';
$route['v1/users/passwd/check']	= 'v1/api/UserController/checkPasswd';
$route['v1/users/passwd/update']	= 'v1/api/UserController/updatePasswd';

$route['v1/users/find/passwd']			= 'v1/api/UsersettingController/findPassword';
$route['v1/users/find/passwd/check']	= 'v1/api/UsersettingController/findPasswordCheck';
$route['v1/users/send/passwd']			= 'v1/api/UsersettingController/updateSendPasswd';
$route['v1/users/send/passwd/check']	= 'v1/api/UsersettingController/checkSendPasswd';
$route['v1/users/bimetric']				= 'v1/api/UsersettingController/updateBiometricAuth';
$route['v1/users/bimetric/check']		= 'v1/api/UsersettingController/checkBiometricAuth';
$route['v1/users/email']				= 'v1/api/UsersettingController/updateUserEmail';

$route['v1/wallet']				= 'v1/api/WalletController/wallet';
$route['v1/wallet/restore']		= 'v1/api/WalletController/walletRestore';
$route['v1/wallet/search']		= 'v1/api/WalletController/search';

$route['v1/wallet/erc20/token/add']		= 'v1/api/WalletController/setToken';
$route['v1/wallet/erc20/token/remove']		= 'v1/api/WalletController/removeToken';

$route['v1/wallet/erc20/token/info']		= 'v1/api/WalletController/getTokenInfo';

$route['v1/wallet/create/(:any)']		= 'v1/api/WalletController/create/$1';
$route['v1/wallet/(:any)']		= 'v1/api/WalletController/wallet/$1';

$route['v1/transction/updateHash']			= 'v1/api/TransactionController/updateHash';
$route['v1/transction/updateReceipt']		= 'v1/api/TransactionController/updateReceipt';
$route['v1/transction/transfer']			= 'v1/api/TransactionController/transfer';
$route['v1/transction/transfer/(:any)']		= 'v1/api/TransactionController/transfer/$1';
 
$route['version'] = 'InitController/version';

$route['v1/cuser/authphone/try']		= 'v1/api/AuthController/authPhoneTry';
$route['v1/cuser/authphone/check']		= 'v1/api/AuthController/authPhoneCheck';

$route['v1/info/coinprice/(:any)']		= 'v1/api/InfoController/coinprice/$1';
$route['v1/info/usdtokrw']				= 'v1/api/InfoController/usdtokrw';
$route['v1/info/exchangeRate/(:any)']	= 'v1/api/InfoController/exchangeRate/$1';

$route['v1/info/contract/senderOwner']	= 'v1/api/InfoController/senderOwner';
$route['v1/info/contract/sender']		= 'v1/api/InfoController/sender';
$route['v1/info/contract/currentFee']	= 'v1/api/InfoController/currentFee';


$route['v1/api/key']					= 'v1/api/Key/index';
$route['v1/api/key/(:any)']				= 'v1/api/Key/keyCheck/$1';

$route['test/f1']						= 'TestController/f1';