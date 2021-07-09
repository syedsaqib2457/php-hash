<?php
	$styleSheets = array(
		'/assets/css/default.css'
	);
	require_once($configuration->settings['base_path'] . '/models/servers.php');
	require_once($configuration->settings['base_path'] . '/assets/php/header.php');
	$includes = array(
		'servers' => array(
			'activate',
			'deactivate'
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
<main process="servers">
	<section class="section">
		<div class="container">
			<div class="hidden item-list-processing-container"></div>
			<div class="item-list-container">
				<h1>Servers</h1>
				<div class="item-list process" from="servers">
					<p class="message">Loading</p>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
	$scripts = array(
		'/assets/js/default.js',
		'/assets/js/servers.js',
		'/assets/js/main.js'
	);
	require_once($configuration->settings['base_path'] . '/assets/php/footer.php');
?>
