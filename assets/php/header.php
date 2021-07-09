<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="initial-scale=1, width=device-width" name="viewport">
<title><?php echo $configuration->parameters['title']; ?></title>
<link href="/assets/png/icon-favicon.png" rel="icon" type="image/png">
<?php
	if (!empty($styleSheets)) {
		foreach ($styleSheets as $styleSheet) {
			echo '<link rel="stylesheet" href="' . $styleSheet . '?' . time() . '" type="text/css">' . "\n";
		}
	}

	$navigationItems = array();

	if (
		!empty($data['action']) &&
		!empty($data['from']) &&
		(
			empty($configuration->publicPermissions[$data['from']]) ||
			!in_array($data['action'], $configuration->publicPermissions[$data['from']])
		)
	) {
		$navigationItems = array(
			array(
				'href' => '/servers?#password',
				'text' => 'Account'
			),
			array(
				'href' => '/developer',
				'text' => 'Developer'
			),
			array(
				'href' => '/proxies',
				'text' => 'Proxies'
			),
			array(
				'href' => '/servers',
				'text' => 'Servers'
			),
			array(
				'href' => '/servers?#login',
				'text' => 'Sign Out'
			)
		);
	}
?>
</head>
<body>
<header>
	<div class="container">
		<div class="align-left navigation">
			<?php
				if (!empty($navigationItems)) {
					echo '<nav><ul>';

					foreach ($navigationItems as $navigationItem) {
						$class = !empty($navigationItem['class']) ? $navigationItem['class'] : 'button';
						$href = !empty($navigationItem['href']) ? $navigationItem['href'] : 'javascript:void(0);';
						$process = !empty($navigationItem['process']) ? $navigationItem['process'] : '';
						$frame = !empty($navigationItem['frame']) ? $navigationItem['frame'] : '';
						echo '<li><a class="' . $class . '" href="' . $href . '">' . $navigationItem['text'] . '</a></li>';
					}

					echo '</ul></nav>';
				}
			?>
		</div>
	</div>
</header>
<div class="clear"></div>
<?php
	$includes = array(
		'main' => array(
			'login',
			'password'
		)
	);

	foreach ($includes as $includePath => $includeNames) {
		foreach ($includeNames as $includeName) {
			$includeFile = $configuration->settings['base_path'] . '/views/' . $includePath . '/includes/' . $includeName . '.php';

			if (file_exists($includeFile)) {
				require_once($includeFile);
			}
		}
	}
?>
