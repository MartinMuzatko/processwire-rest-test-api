<?php namespace ProcessWire;
include('vendor/autoload.php');

header('Access-Control-Allow-Origin: *');
/* Allowed request method */
header("Access-Control-Allow-Methods: *");
/* Allowed custom header */
header("Access-Control-Allow-Headers: *");

use \API\Response;
use \API\Router;
use \API\Route;

define('REALM', 'Happy-CSS Rest API');

$assets = $config->urls->templates;

$router = new Router([

	new Route('GET', '', function($path){
		return [
			"routes" => [
				"GET" => [
					"users",
					"products",
					"products/([\w-]+)",
					"vendors",
					"vendors/([\w-]+)"
				]
			]
		];
	}),

	new Route('GET', 'users', function($path){
		$resource = new \API\Resource('');
		$nameFilter = $this->input->get->name;
		$emailFilter = $this->input->get->email;
		$limit = $this->input->get->limit ? $this->input->get->limit : 10;
		$pages = $this->users;
		if ($nameFilter) {
			$pages = $pages->find("name*=$nameFilter, limit=$limit");
		}
		if ($emailFilter) {
			$pages = $pages->find("email*=$emailFilter, limit=$limit");
		}
		if (!$nameFilter && !$emailFilter) {
			$pages = $pages->find("limit=$limit");
		}
		$json = [];
		foreach ($pages as $page) {
			$data = ["name" => htmlentities($page->name), "email" => htmlentities($page->email)];
			array_push($json, $data);
		}
		return $json;
	}),

    new Route('GET', 'products', function($path){
		$resource = new \API\Resource('');
        $pages = $this->pages->get('template=products')->children;
		$json = [];
		foreach ($pages as $page) {
			$data = $resource->getDefaultFields($page);
			$connections = $resource->getConnections($page);
			array_push($json, array_merge($data, ["connections"=>$connections]));
		}
		return $json;
    }),

	new Route('GET', 'products/([\w-]+)', function($path, $product){
		$resource = new \API\Resource('');
		$page = $this->pages->get("name|id=$product, template=product");
		$data = $resource->getDefaultFields($page);
		$connections = $resource->getConnections($page);
		return array_merge($data, ["connections"=>$connections]);
	}),

	new Route('GET', 'vendors', function($path){
		$resource = new \API\Resource('');
		$pages = $this->pages->get('template=vendors')->children;
		$json = [];
		foreach ($pages as $page) {
			$data = $resource->getDefaultFields($page);
			$connections = $resource->getConnections($page);
			array_push($json, array_merge($data, ["connections"=>$connections]));
		}
		return $json;
	}),

	new Route('GET', 'vendors/([\w-]+)', function($path, $vendor){
		$resource = new \API\Resource('');
		$page = $this->pages->get("name|id=$vendor, template=vendor");
		$data = $resource->getDefaultFields($page);
		$connections = $resource->getConnections($page);
		return array_merge($data, ["connections"=>$connections]);
	}),

]);
$data = true;
try {
    $data = $router->execute($input->urlSegmentStr);
} catch (\Exception $e) {
    $data = false;
    if ($config->debug) {
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];
    }
} finally {
    Response::setContentTypeJSON();
    echo json_encode($data);
}
die;
