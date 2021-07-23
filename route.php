<?php
	if (
		!empty($_SERVER['REMOTE_ADDR']) &&
		($requestLogsPath = __DIR__ . '/request_logs/' . str_replace('.', '/', $_SERVER['REMOTE_ADDR'])) &&
		file_exists($requestLogsPath)
	) {
		echo 'Too many consecutive unauthorized access attempts, please try again later.';
		exit;
	}

	require_once(__DIR__ . '/configuration.php');

	if (
		$_SERVER['REDIRECT_URL'] !== '/' &&
		substr($_SERVER['REDIRECT_URL'], -1) === '/'
	) {
		$configuration->redirect(substr($_SERVER['REDIRECT_URL'], 0, -1));
	}

	$pathParts = array_filter(explode('/', $_SERVER['REDIRECT_URL']));
	$routes = array(
		array(
			'file' => $configuration->settings['base_path'] . '/assets/css/[file]',
			'headers' => array(
				'Content-type: text/css'
			),
			'url' => '/assets/css/[file]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/assets/font/[file]',
			'headers' => array(
				'Content-type: text/plain'
			),
			'url' => '/assets/font/[file]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/assets/php/[file]',
			'headers' => array(
				'Content-type: text/plain'
			),
			'url' => '/assets/php/[file]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/assets/png/[file]',
			'headers' => array(
				'Content-type: image/png'
			),
			'url' => '/assets/png/[file]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/assets/js/[file]',
			'headers' => array(
				'Content-type: text/javascript'
			),
			'url' => '/assets/js/[file]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/views/[from]/endpoint.php',
			'headers' => array(
				'Access-Control-Allow-Origin: *',
				'Content-type: application/json'
			),
			'url' => '/endpoint/[from]'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/views/main/developer.php',
			'title' => 'Developer',
			'url' => '/developer'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/views/proxies/list.php',
			'title' => 'Proxies',
			'url' => '/proxies'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/views/servers/list.php',
			'title' => 'Servers',
			'url' => '/servers'
		),
		array(
			'file' => $configuration->settings['base_path'] . '/views/servers/view.php',
			'title' => 'Server [id]',
			'url' => '/servers/[id]'
		)
	);

	foreach ($routes as $key => $route) {
		$routes['files'][$key] = $route['file'];
		$routes['headers'][$key] = !empty($route['headers']) ? $route['headers'] : array();
		$routes['parts'][$key] = array_filter(explode('/', $route['url']));
		$routes['titles'][$key] = $route['title'];
		$routes['urls'][$key] = $route['url'];
		unset($routes[$key]);
		unset($route);
	}

	$route = array_search($pathParts, $routes['parts']);

	if (!is_numeric($route)) {
		foreach ($routes['parts'] as $routeKey => $routePathParts) {
			if (
				count($routePathParts) !== count($pathParts) ||
				$routePathParts[0] !== $pathParts[0]
			) {
				continue;
			}

			foreach ($routePathParts as $routePathPartKey => $routePathPart) {
				if (
					substr($routePathPart, 0, 1) !== '[' &&
					substr($routePathPart, -1) !== ']'
				) {
					if ($routePathPart !== $pathParts[$routePathPartKey]) {
						break;
					}
				} else {
					if (strpos($routes['files'][$routeKey], $routePathPart) !== false) {
						$pathName = $pathParts[$routePathPartKey];

						if (
							empty($routes['headers'][$routeKey]) ||
							strpos($routes['files'][$routeKey], '[file]') === false
						) {
							$pathName = str_replace('-', '_', $pathName);
						}

						$routes['files'][$routeKey] = str_replace($routePathPart, $pathName, $routes['files'][$routeKey]);
					}

					if (strpos($routes['titles'][$routeKey], $routePathPart) !== false) {
						$routes['titles'][$routeKey] = str_replace($routePathPart, $pathParts[$routePathPartKey], $routes['titles'][$routeKey]);
					}
				}

				if ($routePathPartKey === (count($pathParts) - 1)) {
					$route = $routeKey;
				}
			}
		}
	}

	if (
		$route === false ||
		!file_exists($routes['files'][$route])
	) {
		exit;
	}

	$configuration->parameters = array(
		'title' => $routes['titles'][$route],
		'route' => array(
			'file' => $routes['files'][$route],
			'headers' => $routes['headers'][$route],
			'parts' => $routes['parts'][$route],
			'url' => $routes['urls'][$route]
		)
	);

	if (!empty($configuration->parameters['route']['headers'])) {
		$headers = $configuration->parameters['route']['headers'];

		foreach ($headers as $header) {
			header($header);
		}

		if (in_array('Content-type: image/png', $headers) ) {
			readfile($configuration->parameters['route']['file']);
			exit;
		}

		if (in_array('Content-type: text/plain', $headers)) {
			echo file_get_contents($configuration->parameters['route']['file']);
			exit;
		}
	}

	if (!empty($configuration->parameters['route']['parts'])) {
		foreach ($configuration->parameters['route']['parts'] as $routePathPartKey => $routePathPart) {
			if (
				substr($routePathPart, 0, 1) === '[' &&
				substr($routePathPart, -1) === ']'
			) {
				$configuration->parameters[trim($routePathPart, '[]')] = str_replace('-', '_', $pathParts[$routePathPartKey]);
			}
		}
	}

	require_once($routes['files'][$route]);
?>
