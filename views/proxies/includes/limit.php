<div class="hidden process-container" process="limit">
	<div class="process section">
		<div class="item-container">
			<div class="item">
				<div class="limit-configuration">
					<div class="item-body">
						<div class="limit message-container"></div>
						<p class="error message">New per-proxy and per-URL request limitation and blacklist/whitelist features are currently in development.</p>
						<div class="item-list" from="proxy_urls">
							<p class="message">Loading</p>
						</div>
						<div class="item-list" from="proxy_url_request_limitations">
							<p class="message">Loading</p>
						</div>
						<div class="checkbox-container no-margin-top">
							<span checked="0" class="checkbox" name="only_allow_urls"></span>
							<label class="custom-checkbox-label" name="only_allow_urls">Only Allow Requests to the Selected URLs</label>
						</div>
						<div class="checkbox-container no-margin-top">
							<span checked="0" class="checkbox" name="block_all_urls"></span>
							<label class="custom-checkbox-label" name="block_all_urls">Block All Requests to the Selected URLs</label>
						</div>
						<div class="clear"></div>
					</div>
					<div class="item-footer">
						<a class="alternate-button button close" href="javascript:void(0);">Close</a>
						<a class="button main-button submit" href="javascript:void(0);" process="limit">Save Changes</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="process-overlay"></div>
</div>
