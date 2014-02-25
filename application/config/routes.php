<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "home";
$route['404_override'] = 'error';

$route['buy/(:num)'] = 'buy/index/$1';
$route['view/(:num)'] = 'view/index/$1';
$route['userproducts/(:num)'] = 'userproducts/index/$1';
$route['home/(:any)'] = 'home/index/$1';
$route['publisher/edit/(:num)'] = 'publisher/products/edit/$1';
$route['publisher/encrypt/(:num)'] = 'publisher/encrypt/index/$1';
$route['publisher/encrypt/process(:num)'] = 'publisher/encrypt/process/$1';

// show product detail
$route['Product/:any-(:num)'] = 'product/show/$1';

// show products by category
$route[':any/Products-(:num)'] = 'category/show_products/$1';

// list subcategories
$route[':any-(:num)'] = 'category/show_subcats/$1';
$route[':any/:any-(:num)'] = 'category/show_subcats/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */