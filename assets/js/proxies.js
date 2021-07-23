var processAuthenticate = function(processElement, processSubmit) {
	if (processSubmit) {
		processItemList('listProxyItems', function() {
			api.setRequestParameters({
				action: 'authenticate',
				url: '/endpoint/proxies'
			});
			delete apiRequestParameters.encodeItemList;
			api.sendRequest(function(response) {
				delete apiRequestParameters.processing;
				elements.html('.authenticate-configuration .message-container.authenticate', response.message.html);

				if (response.message.status === 'success') {
					elements.get('.authenticate-configuration .password').value = '';
					elements.get('.authenticate-configuration .username').value = '';
					elements.get('.authenticate-configuration .whitelisted-ips').value = '';
					elements.setAttribute('.authenticate-configuration .generate-unique', 'checked', 0);
					elements.setAttribute('.authenticate-configuration .ignore-empty', 'checked', 0);
					closeProcesses();
					openProcess('.process-container[process="download"]');
					processDownload();
					processItemList('listProxyItems');
					elements.html('.download-configuration .message-container.download', response.message.html);
				}
			});
		});
	}
};
var processLimit = function() {
	delete apiRequestParameters.encodeItemList;

	if (!apiRequestParameters.processing) {
		if (
			typeof apiRequestParameters.listProxyUrlItems === 'undefined' ||
			typeof apiRequestParameters.listProxyUrlRequestLimitationItems === 'undefined'
		) {
			api.setRequestParameters({
				listProxyUrlItems: {
					action: 'fetch',
					callbacks: {
						onItemListReady: function(response, itemListParameters) {
							processProxyUrlItems(response, itemListParameters);
							var elementContent = '<label>Select URLs <span class="icon tooltip tooltip-bottom" item_title="Select a list of URLs below to apply to the selected request limitation settings and proxy IPs. If no URLs are selected, the request limitation settings will apply to all URLs."></span></label>';
							elements.html(itemListParameters.selector + ' .item-controls-heading-container', elementContent);
						}
					},
					from: 'proxy_urls',
					initial: true,
					options: [
						{
							attributes: [
								{
									name: 'checked',
									value: '0'
								},
								{
									name: 'class',
									value: 'align-left checkbox no-margin-left'
								},
								{
									name: 'index',
									value: 'all-visible'
								}
							],
							tag: 'span'
						},
						{
							attributes: [
								{
									name: 'class',
									value: 'button icon remove process-button tooltip tooltip-bottom'
								},
								{
									name: 'item_function'
								},
								{
									name: 'item_list_name',
									value: 'list_proxy_url_items'
								},
								{
									name: 'item_title',
									value: 'Remove selected proxy URLs'
								},
								{
									name: 'process',
									value: 'remove'
								}
							],
							tag: 'span'
						}
					],
					page: 1,
					resultsPerPage: 5,
					selector: '.item-list[from="proxy_urls"]',
					url: '/endpoint/proxy-urls',
					where: {}
				},
				listProxyUrlRequestLimitationItems: {
					action: 'fetch',
					callbacks: {
						onItemListReady: function(response, itemListParameters) {
							processProxyUrlRequestLimitationItems(response, itemListParameters);
							var elementContent = '<label>Select Request Limitation Settings <span class="icon tooltip tooltip-bottom" item_title="Select a list of request limitation settings below to apply to the selected URLs and proxy IPs."></span></label>';
							elements.html(itemListParameters.selector + ' .item-controls-heading-container', elementContent);
						}
					},
					from: 'proxy_url_request_limitations',
					initial: true,
					options: [
						{
							attributes: [
								{
									name: 'checked',
									value: '0'
								},
								{
									name: 'class',
									value: 'align-left checkbox no-margin-left'
								},
								{
									name: 'index',
									value: 'all-visible'
								}
							],
							tag: 'span'
						},
						{
							attributes: [
								{
									name: 'class',
									value: 'button icon process-button remove tooltip tooltip-bottom'
								},
								{
									name: 'item_function'
								},
								{
									name: 'item_list_name',
									value: 'list_proxy_url_request_limitation_items'
								},
								{
									name: 'item_title',
									value: 'Remove selected request limitation settings'
								},
								{
									name: 'process',
									value: 'remove'
								}
							],
							tag: 'span'
						}
					],
					page: 1,
					resultsPerPage: 5,
					selector: '.item-list[from="proxy_url_request_limitations"]',
					url: '/endpoint/proxy-url-request-limitations',
					where: {}
				}
			});
		}

		onNodeReady([
			'apiRequestParameters',
			'listProxyUrlRequestLimitationItems'
		], function() {
			processItemList('listProxyUrlItems', function() {
				processItemList('listProxyUrlRequestLimitationItems', function() {
					delete apiRequestParameters.processing;
				});
			});
		});
	} else {
		api.setRequestParameters({
			action: 'limit',
			url: '/endpoint/proxies'
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			elements.html('.limit-configuration .message-container.limit', response.message.html);
			api.setRequestParameters({
				data: []
			});
		});
	}
};
var processProxies = function() {
	api.setRequestParameters({
		listProxyItems: {
			action: 'fetch',
			callbacks: {
				onItemListReady: function(response, itemListParameters) {
					processProxyItems(response, itemListParameters);
				}
			},
			enableBackgroundActionProcessing: true,
			from: 'proxies',
			initial: true,
			options: [
				{
					attributes: [
						{
							name: 'checked',
							value: '0'
						},
						{
							name: 'class',
							value: 'align-left checkbox no-margin-left'
						},
						{
							name: 'index',
							value: 'all-visible'
						}
					],
					tag: 'span'
				},
				{
					attributes: [
						{
							name: 'class',
							value: 'button icon process-button tooltip tooltip-bottom'
						},
						{
							name: 'item_title',
							value: 'Search for proxies'
						},
						{
							name: 'process',
							value: 'search'
						}
					],
					tag: 'span'
				},
				{
					attributes: [
						{
							name: 'class',
							value: 'button icon process-button tooltip tooltip-bottom'
						},
						{
							name: 'item_title',
							value: 'Manage proxy URL request limits'
						},
						{
							name: 'process',
							value: 'limit'
						}
					],
					tag: 'span'
				},
				{
					attributes: [
						{
							name: 'class',
							value: 'button hidden icon process-button tooltip tooltip-bottom'
						},
						{
							name: 'item_function'
						},
						{
							name: 'item_title',
							value: 'Manage authentication of selected proxies'
						},
						{
							name: 'process',
							value: 'authenticate'
						}
					],
					tag: 'span'
				},
				{
					attributes: [
						{
							name: 'class',
							value: 'button hidden icon process-button tooltip tooltip-bottom'
						},
						{
							name: 'item_function'
						},
						{
							name: 'item_title',
							value: 'Download list of selected proxies'
						},
						{
							name: 'process',
							value: 'download'
						}
					],
					tag: 'span'
				},
			],
			page: 1,
			resultsPerPage: 100,
			selector: '.item-list[from="proxies"][page="all"]',
			sort: {
				field: 'modified',
				order: 'DESC'
			},
			url: '/endpoint/proxies',
			where: {}
		}
	});
	processItemList('listProxyItems');
};
var processProxyItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listProxyItems');
	} else {
		const processProxyEdit = function(selectedElementSelector, proxyId) {
			const processProxyEdit = function(selectedElementSelector, proxyId) {
				api.setRequestParameters({
					action: 'edit',
					data: {
						enableUrlRequestLogs: elements.getAttribute(selectedElementSelector + ' .proxy-enable-url-request-logs-field', 'checked'),
						password: elements.get(selectedElementSelector + ' .proxy-password-field').value,
						status: elements.getAttribute(selectedElementSelector + ' .proxy-status-active-field', 'checked'),
						username: elements.get(selectedElementSelector + ' .proxy-username-field').value,
						whitelistedIps: elements.get(selectedElementSelector + ' .proxy-whitelisted-ips-field').value
					},
					url: '/endpoint/proxies',
					where: {
						id: proxyId
					}
				});
				delete apiRequestParameters.encodeItemList;
				elements.setAttribute(selectedElementSelector + ' .editing .save-edit-button', 'disabled');
				elements.html(itemListParameters.selector + ' .editing .save-edit-button', 'Processing');
				api.sendRequest(function(response) {
					processItemList('listProxyItems', function() {
						elements.html(itemListParameters.selector + ' .message-container.list-proxy-items', response.message.html);
					});
				});
			};
			api.setRequestParameters({
				action: 'fetch',
				editing: {
					content: elements.html(selectedElementSelector + ' .table-text')
				},
				from: 'proxies',
				offset: 0,
				url: '/endpoint/proxies',
				where: {
					id: proxyId
				}
			});
			api.setRequestParameters({
				action: 'view',
				editing: {
					content: elements.html(selectedElementSelector + ' .table-text')
				},
				encodeItemList: true,
				url: '/endpoint/proxies',
				where: {
					id: proxyId
				}
			});
			elements.addClass(itemListParameters.selector + ' .edit.icon', 'hidden');
			elements.html(selectedElementSelector + ' .table-text', '<label class="label">Loading</label>');
			api.sendRequest(function(response) {
				if (
					response.message.status &&
					response.message.status !== 'success'
				) {
					elements.html(selectedElementSelector + ' .table-text', '<p class="error message no-margin">' + response.message.text + '</p>');
					elements.removeClass(itemListParameters.selector + ' .edit.icon', 'hidden');
				}

				if (response.data) {
					// todo: restructure section to allow changing server node IP between proxy, VPN and DNS mode
					var elementContent = '<label>Proxy</label>';
					elementContent += '<p>' + response.data.externalIp + ' <span disabled>[' + (response.data.internalIp || response.data.externalIp) + ']</span></p>';
					elementContent += '<div class="field-group no-margin">';
					elementContent += '<div class="align-left checkbox-container no-margin-top">';
					elementContent += '<span checked="0" class="checkbox proxy-status-active-field no-margin-left" name="proxy_status_active"></span>';
					elementContent += '<label class="custom-checkbox-label" name="proxy_status_active">Active</label>';
					elementContent += '</div>';
					elementContent += '<div class="clear"></div>';
					elementContent += '<div class="field-group no-margin">';
					elementContent += '<div class="align-left checkbox-container no-margin-top">';
					elementContent += '<span checked="0" class="checkbox proxy-enable-url-request-logs-field no-margin-left" name="proxy_enable_url_request_logs"></span>';
					elementContent += '<label class="custom-checkbox-label" name="proxy_enable_url_request_logs">Enable URL Request Logs</label>';
					elementContent += '</div>';
					elementContent += '<div class="clear"></div>';
					elementContent += '<label>Username</label>';
					elementContent += '<div class="field-group no-margin-top">';
					elementContent += '<input class="no-margin proxy-username-field" name="proxy_username" placeholder="Between 4 and 15 characters">';
					elementContent += '</div>';
					elementContent += '<label>Password</label>';
					elementContent += '<div class="field-group no-margin-top">';
					elementContent += '<input class="no-margin proxy-password-field" name="proxy_password" placeholder="Between 4 and 15 characters">';
					elementContent += '</div>';
					elementContent += '<label>Whitelisted IPs and Subnets</label>';
					elementContent += '<div class="field-group no-margin-top">';
					elementContent += '<textarea class="no-margin proxy-whitelisted-ips-field" name="proxy_whitelisted_ips" placeholder="127.0.0.1\n127.0.0.2\n127.0.0.0/8\netc..."></textarea>';
					elementContent += '</div>';
					elementContent += '<div class="clear"></div>';
					elementContent += '<button class="alternate-button button no-margin-bottom proxy-cancel-edit-button margin-right">Cancel</button>';
					elementContent += '<button class="button main-button no-margin-bottom proxy-save-edit-button save-edit-button">Save Changes</button>';
					elementContent += '</div>';
					elements.addClass(selectedElementSelector + ' td:last-child', 'editing');
					elements.html(selectedElementSelector + ' .table-text', elementContent);
					render(function() {
						elements.get(selectedElementSelector + ' .table-text .proxy-whitelisted-ips-field').value = response.data.whitelistedIps;
						elements.setAttribute(selectedElementSelector + ' .table-text .proxy-enable-url-request-logs-field', 'checked', response.data.enableUrlRequestLogs);
						elements.setAttribute(selectedElementSelector + ' .table-text .proxy-password-field', 'value', response.data.password || '');
						elements.setAttribute(selectedElementSelector + ' .table-text .proxy-status-active-field', 'checked', +(response.data.status === 'active'));
						elements.setAttribute(selectedElementSelector + ' .table-text .proxy-username-field', 'value', response.data.username || '');
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .proxy-cancel-edit-button', {
								method: function() {
									elements.html(selectedElementSelector + ' .table-text', apiRequestParameters.editing.content);
									elements.removeClass(itemListParameters.selector + ' .edit.icon', 'hidden');
									elements.removeClass(selectedElementSelector + ' .editing', 'editing');
									delete apiRequestParameters.editing;
								},
								type: 'click'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .proxy-password-field', {
								method: function() {
									if (event.key == 'Enter') {
										processProxyEdit(selectedElementSelector, proxyId);
									}
								},
								type: 'keydown'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .proxy-username-field', {
								method: function() {
									if (event.key == 'Enter') {
										processProxyEdit(selectedElementSelector, proxyId);
									}
								},
								type: 'keydown'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .proxy-save-edit-button', {
								method: function() {
									processProxyEdit(selectedElementSelector, proxyId);
								},
								type: 'click'
							});
						});
						processProcesses();
					});
				}
			});
		};
		elements.html(itemListParameters.selector + ' .items', '<table class="table"><thead><tr><th></th><th>External IP <span disabled>[Internal IP]</span></th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let proxyKey in response.data) {
			let proxy = response.data[proxyKey];
			elementContent += '<tr proxy_id="' + proxy.id + '">';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + proxyKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td>';
			elementContent += '<span class="table-text">';
			elementContent += proxy.externalIp + ' <span disabled>[' + (proxy.internalIp || proxy.externalIp) + ']</span>';
			elementContent += '</span>';
			elementContent += '<span class="table-actions">';
			elementContent += '<span class="button edit icon" proxy_id="' + proxy.id + '"></span>';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			selectAllElements(itemListParameters.selector + ' .items tbody tr', function(selectedElementKey, selectedElement) {
				const proxyId = elements.getAttribute(selectedElement, 'proxy_id');
				const selectedElementSelector = itemListParameters.selector + ' .items tbody tr[proxy_id="' + proxyId + '"]';
				let proxyEditButton = elements.get(selectedElementSelector + ' .edit');
				elements.addEventListener(proxyEditButton, {
					method: function() {
						processProxyEdit(selectedElementSelector, proxyId);
					},
					type: 'click'
				});
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-proxy-items', response.message.html);
		processProcesses();
	}
};
var processProxyUrlItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listProxyUrlItems');
	} else {
		const processProxyUrlAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					url: elements.get(itemListParameters.selector + ' .create-proxy-url .proxy-url-field').value
				},
				url: '/endpoint/proxy-urls'
			});
			elements.setAttribute(itemListParameters.selector + ' .create-proxy-url .add-proxy-url-button', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listProxyUrlItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-proxy-url-items', response.message.html);
				});
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button main-button" href="javascript:void(0);" show="create-proxy-url">Create Proxy URL</a>';
		elementContent += '<div class="create-proxy-url form hidden">';
		elementContent += '<label>URL</label>';
		elementContent += '<div class="field-group no-margin-top">';
		elementContent += '<input class="no-margin proxy-url-field" name="proxy_url" placeholder="Enter domain or subdomain URL (e.g. https://example.com)">';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right" hide="create-proxy-url" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-proxy-url-button button main-button" href="javascript:void(0)">Add Proxy URL</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		elements.html(itemListParameters.selector + ' .items', '<table class="no-margin-bottom table"><thead><tr><th></th><th>Proxy URLs</th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let proxyUrlKey in response.data) {
			let proxyUrl = response.data[proxyUrlKey];
			elementContent += '<tr proxy_url_id="' + proxyUrl.id + '">';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + proxyUrlKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td>';
			elementContent += '<span class="table-text">';
			elementContent += proxyUrl.url;
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-proxy-url .add-proxy-url-button', {
				method: function() {
					processProxyUrlAdd();
				},
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-proxy-url .proxy-url-field', {
				method: function() {
					if (event.key == 'Enter') {
						processProxyUrlAdd();
					}
				},
				type: 'keydown'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-proxy-url-items', response.message.html);
		processProcesses();
	}
};
var processProxyUrlRequestLimitationItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listProxyUrlRequestLimitationItems');
	} else {
		const processProxyUrlRequestLimitationAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					proxyUrlBlockIntervalType: elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .proxy-url-block-interval-type-field').value,
					proxyUrlBlockIntervalValue:elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .proxy-url-block-interval-value-field').value,
					proxyUrlRequestIntervalType: elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .proxy-url-request-interval-type-field').value,
					proxyUrlRequestIntervalValue: elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .proxy-url-request-interval-value-field').value,
					proxyUrlRequestNumber: elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .proxy-url-request-number-field').value
				},
				url: '/endpoint/proxy-url-request-limitations'
			});
			elements.setAttribute(itemListParameters.selector + ' .create-proxy-url-request-limitation .add-proxy-url-request-limitation-button', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listProxyUrlRequestLimitationItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-proxy-url-request-limitation-items', response.message.html);
				});
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button main-button" href="javascript:void(0);" show="create-proxy-url-request-limitation">Create Proxy URL Request Limitation</a>';
		elementContent += '<div class="create-proxy-url-request-limitation form hidden">';
		elementContent += '<div class="field-group no-margin-top">';
		elementContent += '<span class="no-margin">Block URL for</span>';
		elementContent += '<input class="no-margin-bottom no-margin-right number proxy-url-block-interval-value-field" name="proxy_url_block_interval_value" placeholder="(e.g. 10)" type="text" value="10">';
		elementContent += '<select class="proxy-url-block-interval-type-field" name="proxy_url_block_interval_type">';
		elementContent += '<option value="minute">minutes</option>';
		elementContent += '<option selected value="hour">hours</option>';
		elementContent += '<option value="day">days</option>';
		elementContent += '<option value="month">months</option>';
		elementContent += '</select>';
		elementContent += '</div>';
		elementContent += '<div class="field-group no-margin-top">';
		elementContent += '<span class="no-margin">After</span>';
		elementContent += '<input class="no-margin-right proxy-url-request-number-field number" name="proxy_url_request_number" placeholder="(e.g. 20)" type="text" value="20">';
		elementContent += '<span>requests within</span>';
		elementContent += '<input class="no-margin-left proxy-url-request-interval-value-field number" name="proxy_url_request_interval_value" placeholder="(e.g. 10)" type="text" value="10">';
		elementContent += '<select class="proxy-url-request-interval-type-field" name="proxy_url_request_interval_type">';
		elementContent += '<option selected value="minute">minutes</option>';
		elementContent += '<option value="hour">hours</option>';
		elementContent += '<option value="day">days</option>';
		elementContent += '<option value="month">months</option>';
		elementContent += '</select>';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right" hide="create-proxy-url-request-limitation" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-proxy-url-request-limitation-button button main-button" href="javascript:void(0)">Add Proxy URL Request Limitation</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		elements.html(itemListParameters.selector + ' .items', '<table class="no-margin-bottom table"><thead><tr><th></th><th>Proxy URL Request Limitations</th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let proxyUrlRequestLimitationKey in response.data) {
			let proxyUrlRequestLimitation = response.data[proxyUrlRequestLimitationKey];
			elementContent += '<tr proxy_url_request_limitation_id="' + proxyUrlRequestLimitation.id + '">';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + proxyUrlRequestLimitationKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td>';
			elementContent += '<span class="table-text">';
			elementContent += 'Block URL for ' + proxyUrlRequestLimitation.proxyUrlBlockIntervalValue + ' ' + proxyUrlRequestLimitation.proxyUrlBlockIntervalType + (proxyUrlRequestLimitation.proxyUrlBlockIntervalValue !== 1 ? 's': '') + ' after ' + proxyUrlRequestLimitation.proxyUrlRequestNumber + ' request' + (proxyUrlRequestLimitation.proxyUrlRequestNumber !== 1 ? 's': '') + ' within ' + proxyUrlRequestLimitation.proxyUrlRequestIntervalValue + ' ' + proxyUrlRequestLimitation.proxyUrlRequestIntervalType + (proxyUrlRequestLimitation.proxyUrlRequestIntervalValue !== 1 ? 's': '');
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			elements.addEventListener(elements.get(itemListParameters.selector + ' .create-proxy-url-request-limitation .add-proxy-url-request-limitation-button'), {
				method: function() {
					processProxyUrlRequestLimitationAdd();
				},
				type: 'click'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-proxy-url-request-limitation-items', response.message.html);
		processProcesses();
	}
};
