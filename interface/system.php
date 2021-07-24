<?php
	$styleSheets = array(
		'/assets/css/default.css'
	);
	require_once($configuration->settings['base_path'] . '/models/nodes.php');
	require_once($configuration->settings['base_path'] . '/views/system/includes/header.php');
	$includes = array(
		'nodes' => array(
			'edit',
			'search'
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
<main process="proxies">
	<section class="section">
		<div class="container">
			<div class="hidden item-list-processing-container"></div>
			<div class="item-list-container">
				<h1>Proxies</h1>
				<div class="item-list" from="proxies" page="all">
					<p class="message">Loading</p>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
	$scripts = array(
		'/assets/js/default.js',
		'/assets/js/proxies.js',
		'/assets/js/main.js'
	);
	require_once($configuration->settings['base_path'] . '/assets/php/footer.php');
?>
