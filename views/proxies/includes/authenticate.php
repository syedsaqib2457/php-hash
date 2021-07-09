<div class="hidden process-container" process="authenticate">
	<div class="process section">
		<div class="item-container">
			<div class="item">
				<div class="authenticate-configuration">
					<div class="item-body">
						<div class="authenticate message-container"></div>
						<label>Username</label>
						<input class="username" name="username" placeholder="Between 4 and 15 characters" type="text">
						<label>Password</label>
						<input class="password" name="password" placeholder="Between 4 and 15 characters" type="text">
						<div class="checkbox-container">
							<span checked="0" class="checkbox generate-unique" name="generate_unique"></span>
							<label class="custom-checkbox-label" name="generate_unique">Generate Random Unique Usernames and Passwords</label>
						</div>
						<div class="clear"></div>
						<label>Whitelisted IPs and Subnets</label>
						<textarea class="whitelisted-ips" name="whitelisted_ips" placeholder="<?php echo "127.0.0.1\n127.0.0.2\n127.0.0.0/8\netc..." ?>" type="text"></textarea>
						<div class="checkbox-container">
							<span checked="0" class="checkbox" name="ignore_empty"></span>
							<label class="custom-checkbox-label" name="ignore_empty">Ignore Empty Authentication Values</label>
						</div>
						<div class="clear"></div>
					</div>
					<div class="item-footer">
						<a class="alternate-button button close alternate-button" href="javascript:void(0);">Close</a>
						<a class="button main-button submit" href="javascript:void(0);" process="authenticate">Save Changes</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="process-overlay"></div>
</div>
