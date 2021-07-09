<?php
	$styleSheets = array(
		'/assets/css/default.css'
	);
	require_once($configuration->settings['base_path'] . '/models/servers.php');
	require_once($configuration->settings['base_path'] . '/assets/php/header.php');
	$includes = array(
		'servers' => array(
			'server_nameserver_processes',
			'server_proxy_processes'
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
<main process="server">
	<section class="section">
		<div class="container">
			<div class="hidden item-list-processing-container"></div>
			<div class="item-list-container">
				<a class="hidden icon previous" href="/servers"></a>
				<div class="clear"></div>
				<h1 class="server-name"></h1>
				<div class="item-list" from="server_nodes" page="all">
					<p class="message">Loading</p>
				</div>
				<input name="server_id" type="hidden" value="<?php echo $data['server_id']; ?>">
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
