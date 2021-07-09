<div class="hidden process-container" process="password">
	<div class="process section">
		<div class="item-container">
			<div class="item">
				<div class="item-body">
					<div class="message-container password"></div>
					<label for="account-password">Password</label>
					<input class="form-item account-password" id="account-password" name="account_password" placeholder="Enter an account password" type="text">
					<label for="account-whitelisted-ips">Whitelisted IPs</label>
					<textarea class="account-whitelisted-ips" id="account-whitelisted-ips" name="account_whitelisted_ips" placeholder="<?php echo "127.0.0.1\n127.0.0.2\netc..." ?>" type="text"></textarea>
					<div class="clear"></div>
				</div>
				<div class="item-footer">
					<a class="alternate-button button close" href="/servers">Close</a>
					<a class="button main-button submit" href="javascript:void(0);" process="password">Save</a>
				</div>
			</div>
		</div>
	</div>
	<div class="process-overlay"></div>
</div>
