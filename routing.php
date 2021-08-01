<?php
	if (
		// todo: ipv6
		(empty($_SERVER['REMOTE_ADDR']) === false) &&
		(file_exists(__DIR__ . '/request_logs/' . implode('/', explode('.', $_SERVER['REMOTE_ADDR'])) . '/.') === true)
	) {
		echo 'Too many consecutive unauthorized access attempts, please try again later.';
		exit;
	}

	require_once(__DIR__ . '/system.php');

	if (
		($_SERVER['REDIRECT_URL'] !== '/') &&
		(substr($_SERVER['REDIRECT_URL'], -1) === '/')
	) {
		header('Location: ' . substr($_SERVER['REDIRECT_URL'], 0, -1), true, 301);
		exit;
	}

	$pathParts = array_filter(explode('/', $_SERVER['REDIRECT_URL']));
	$routes = array(
		array(
			'file' => $system->settings['base_path'] . '/interfaces/browser/images/[file]',
			'headers' => array(
				'Content-type: image/png'
			),
			'url' => '/images/[file]'
		),
		array(
			'file' => $system->settings['base_path'] . '/interfaces/browser/interface.php',
			'url' => '/'
		),
		array(
			'file' => $system->settings['base_path'] . '/interfaces/developer/interface.php',
			'headers' => array(
				'Access-Control-Allow-Origin: *',
				'Content-type: application/json'
			),
			'url' => '/endpoint/[from]'
		),
		array(
			'file' => $system->settings['base_path'] . '/scripts/[file]',
			'headers' => array(
				'Content-type: text/plain'
			),
			'url' => '/scripts/[file]'
		)
	);

	foreach ($routes as $key => $route) {
		$routes['files'][$key] = $route['file'];
		$routes['headers'][$key] = array();
		$routes['parts'][$key] = array_filter(explode('/', $route['url']));
		$routes['urls'][$key] = $route['url'];

		if (empty($route['headers']) === false) {
			$routes['headers'][$key] = $route['headers'];
		}

		unset($routes[$key]);
		unset($route);
	}

	$route = array_search($pathParts, $routes['parts']);

	if (is_numeric($route) === false) {
		foreach ($routes['parts'] as $routeKey => $routePathParts) {
			if (
				(count($routePathParts) !== count($pathParts)) ||
				($routePathParts[0] !== $pathParts[0])
			) {
				continue;
			}

			foreach ($routePathParts as $routePathPartKey => $routePathPart) {
				if (
					(substr($routePathPart, 0, 1) !== '[') &&
					(substr($routePathPart, -1) !== ']')
				) {
					if ($routePathPart !== $pathParts[$routePathPartKey]) {
						break;
					}
				} else {
					if (strpos($routes['files'][$routeKey], $routePathPart) !== false) {
						$pathName = $pathParts[$routePathPartKey];

						if (
							(empty($routes['headers'][$routeKey]) === true) ||
							(strpos($routes['files'][$routeKey], '[file]') === false)
						) {
							$pathName = str_replace('-', '_', $pathName);
						}

						$routes['files'][$routeKey] = str_replace($routePathPart, $pathName, $routes['files'][$routeKey]);
					}
				}

				if ($routePathPartKey === (count($pathParts) - 1)) {
					$route = $routeKey;
				}
			}
		}
	}

	if (
		($route === false) ||
		(file_exists($routes['files'][$route]) === false)
	) {
		exit;
	}

	$configuration->parameters = array(
		'route' => array(
			'file' => $routes['files'][$route],
			'headers' => $routes['headers'][$route],
			'parts' => $routes['parts'][$route],
			'url' => $routes['urls'][$route]
		)
	);

	if (empty($configuration->parameters['route']['headers']) === false) {
		$headers = $configuration->parameters['route']['headers'];

		foreach ($headers as $header) {
			header($header);
		}

		if (in_array('Content-type: image/png', $headers) === true) {
			readfile($configuration->parameters['route']['file']);
			exit;
		}

		if (in_array('Content-type: text/plain', $headers) === true) {
			echo file_get_contents($configuration->parameters['route']['file']);
			exit;
		}
	}

	if (empty($configuration->parameters['route']['parts']) === false) {
		foreach ($configuration->parameters['route']['parts'] as $routePathPartKey => $routePathPart) {
			if (
				(substr($routePathPart, 0, 1) === '[') &&
				(substr($routePathPart, -1) === ']')
			) {
				$configuration->parameters[trim($routePathPart, '[]')] = str_replace('-', '_', $pathParts[$routePathPartKey]);
			}
		}
	}

	require_once($routes['files'][$route]);
?>
