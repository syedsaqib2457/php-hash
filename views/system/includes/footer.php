<?php
	$settings = array(
		'baseDomain' => (!empty($configuration->settings['base_domain']) ? $configuration->settings['base_domain'] : ''),
		'siteName' => (!empty($configuration->settings['site_name']) ? $configuration->settings['site_name'] : ''),
		'uniqueId' => sha1($_SERVER['REMOTE_ADDR'] . uniqid()) . md5(time() . uniqid())
	);
	echo '<div class="hidden settings">' . json_encode($settings) . '</div>';

	if (!empty($scripts)) {
		foreach ($scripts as $script) {
			echo '<script src="' . $script . '?' . time() . '" type="text/javascript"></script>' . "\n";
		}
	}
?>
</body>
</html>
