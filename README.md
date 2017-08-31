# Public REST API built with ProcessWire
This repo contains the code for routes and their associated data. Example data hosted on https://happy-css.com
This uses a template called **api** using URL segments. The data can be located anywhere in your processwire installation.
![segment](http://i.imgur.com/7qSO5sv.png)

## Usage

This repo is not yet on composer. It is just a demo of what is possible with Processwire as a REST API.

```php
    include('vendor/autoload.php');
    use \API\Response;
    use \API\Router;
    use \API\Route;
```

### Define routes

```php
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
	})

]);
```

### Execute router

```php
try {
    $data = $router->execute($input->urlSegmentStr);
} catch (\Exception $e) {
    if ($config->debug) {
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];
    } else {
        $data = '';
    }
} finally {
    Response::setContentTypeJSON();
    echo json_encode($data);
}
```

## Demo

The data is fake data created using [faker](https://github.com/Marak/faker.js).

### Users

http://happy-css.com/api/users


```json
[{
    "name": "james",
    "email": "james_Wehner2@gmail.com"
}]
```

#### List all users that start with a name

http://happy-css.com/api/users?name=jo

![jo](http://i.imgur.com/2lT7UHJ.png)

#### List all users that start with an email

http://happy-css.com/api/users?email=jo

#### Limit list

http://happy-css.com/api/users?name=jo&limit=1
