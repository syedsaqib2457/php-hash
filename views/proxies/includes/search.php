<div class="hidden process-container" process="search" search="list_proxy_items">
	<div class="process section">
		<div class="item-container">
			<div class="item">
				<div class="search-configuration">
					<div class="item-body">
						<div class="message-container search"></div>
						<label>Search Terms</label>
						<input class="broad-search" name="broad_search" placeholder="<?php echo "Enter broad search terms (e.g. proxyusername, etc)"; ?>" type="text">
						<label>Filter List of Specific IPs or Subnets</label>
						<textarea class="granular-search" name="granular_search" placeholder="<?php echo "Enter list of specific proxy IPs or subnets\n127.0.0.1\n192.168\n127.0.0.0/8\netc..."; ?>"></textarea>
						<div class="checkbox-container">
							<span checked="0" class="checkbox" name="match_all_search"></span>
							<label class="custom-checkbox-label" name="match_all_search">Require All Search Terms to Match Proxy Results</label>
						</div>
						<div class="clear"></div>
					</div>
					<div class="item-footer">
						<a class="alternate-button button close" href="javascript:void(0);">Close</a>
						<a class="button main-button submit" href="javascript:void(0);" process="search">Search</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="process-overlay"></div>
</div>
