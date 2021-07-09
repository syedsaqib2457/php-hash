var processActivate = function() {
	elements.html('.activate-container', '<p class="message">Loading</p>');

	if (!elements.get('.item-list[from="servers"] .checkbox[index="0"]')) {
		processServers();
	} else {
		api.setRequestParameters({
			action: 'activate',
			url: '/endpoint/servers',
			where: {
				id: apiRequestParameters.listServerItems.itemId
			}
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			var elementContent = '<div class="align-left item-container no-margin no-padding">';

			if (
				response.data.server &&
				!response.data.server.statusActivated
			) {
				elementContent += '<p>Log in to server <strong>' + response.data.server.ip + '</strong> as root and paste this into the command line:</p>';
				elementContent += '<pre>' + response.data.deploymentCommand + '</pre>';
			}

			elementContent += '</div>';
			elementContent += '<div class="clear"></div>';
			elements.html('.activate-container', elementContent);
			elements.html('.message-container.activate', response.message.html);
		});
	}
};
var processDeactivate = function() {
	elements.html('.deactivate-container', '<p class="message">Loading</p>');
	elements.addClass('[process="deactivate"] .submit', 'hidden');

	if (!elements.get('.item-list[from="servers"] .checkbox[index="0"]')) {
		processServers();
	} else {
		api.setRequestParameters({
			action: 'deactivate',
			url: '/endpoint/servers',
			where: {
				id: apiRequestParameters.listServerItems.itemId
			}
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			var elementContent = '<div class="align-left item-container no-margin no-padding">';

			if (
				response.data.server &&
				response.data.server.statusActivated
			) {
				elementContent += '<p>After deactivation, all proxy and nameserver IPs on server ' + response.data.server.ip + ' will become unusable until activated and deployed again.</p>';
				elementContent += '<input class="hidden" name="confirm_deactivation" type="hidden" value="1">';
				elementContent += '</div>';
			}

			elementContent += '<div class="clear"></div>';
			elements.html('.deactivate-container', elementContent);
			elements.html('.message-container.deactivate', response.message.html);
			api.setRequestParameters({
				data: {
					confirmDeactivation: false
				}
			}, true);

			if (
				response.message.status === 'success' &&
				response.data.server &&
				response.data.server.statusActivated
			) {
				elements.removeClass('[process="deactivate"] .submit', 'hidden');
			}
		});
	}
};
var processServer = function() {
	const serverId = elements.get('input[name="server_id"]').value;
	api.setRequestParameters({
		action: 'view',
		serverId: serverId,
		url: '/endpoint/servers',
		where: {
			id: serverId
		}
	});
	api.sendRequest(function(response) {
		elements.html('.message-container.server', response.message.html);

		if (
			typeof response.data !== 'undefined' &&
			response.data.server
		) {
			api.setRequestParameters({
				listServerNodeItems: {
					action: 'fetch',
					callbacks: {
						onItemListReady: function(response, itemListParameters) {
							processServerNodeItems(response, itemListParameters);
						}
					},
					from: 'server_nodes',
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
									value: 'Manage server proxy processes'
								},
								{
									name: 'process',
									value: 'server_proxy_processes'
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
									value: 'Manage server nameserver processes'
								},
								{
									name: 'process',
									value: 'server_nameserver_processes'
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
									value: 'list_server_node_items'
								},
								{
									name: 'item_title',
									value: 'Remove selected server nodes'
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
					resultsPerPage: 100,
					selector: '.item-list[from="server_nodes"][page="all"]',
					sort: {
						field: 'modified',
						order: 'DESC'
					},
					url: '/endpoint/server-nodes',
					where: {
						serverId: apiRequestParameters.serverId
					}
				}
			});
			onNodeReady([
				'apiRequestParameters',
				'listServerNodeItems'
			], function() {
				elements.removeClass('.item-list-container .icon.previous', 'hidden');
				elements.html('.server-name', 'Server ' + response.data.server.ip);
				processItemList('listServerNodeItems');
			});
		}
	});
};
var processServerNameserverProcesses = function() {
	if (typeof apiRequestParameters.listServerNameserverProcessItems === 'undefined') {
		api.setRequestParameters({
			listServerNameserverProcessItems: {
				action: 'fetch',
				callbacks: {
					onItemListReady: function(response, itemListParameters) {
						processServerNameserverProcessItems(response, itemListParameters);
					}
				},
				from: 'server_nameserver_processes',
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
								value: 'list_server_nameserver_process_items'
							},
							{
								name: 'item_title',
								value: 'Remove selected nameserver processes'
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
				resultsPerPage: 10,
				selector: '.item-list[from="server_nameserver_processes"]',
				sort: {
					field: 'modified',
					order: 'DESC'
				},
				url: '/endpoint/server-nameserver-processes',
				where: {
					serverId: apiRequestParameters.serverId,
					serverNameserverProcessId: null
				}
			}
		});
	}

	onNodeReady([
		'apiRequestParameters',
		'listServerNameserverProcessItems'
	], function() {
		processItemList('listServerNameserverProcessItems', function() {
			elements.addClass('[process="server_nameserver_processes"] .process.item-body', 'no-padding-top');
		});
	});
};
var processServerNameserverProcessItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listServerNameserverProcessItems');
	} else {
		const processServerNameserverProcessAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					createInternalProcess: elements.getAttribute(itemListParameters.selector + ' .create-server-nameserver-process .checkbox.server-nameserver-process-create-internal-process', 'checked'),
					externalSourceIp: elements.get(itemListParameters.selector + ' .create-server-nameserver-process .server-nameserver-process-external-source-ip-field').value,
					listeningIp: elements.get(itemListParameters.selector + ' .create-server-nameserver-process .server-nameserver-process-listening-ip-field').value
				},
				url: '/endpoint/server-nameserver-processes'
			});
			delete apiRequestParameters.from;
			elements.html(itemListParameters.selector + ' .create-server-nameserver-process .add-server-nameserver-process-button', 'Adding Server Nameserver Process');
			elements.setAttribute(itemListParameters.selector + ' .create-server-nameserver-process .add-server-nameserver-process-button', 'disabled', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listServerNameserverProcessItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-server-nameserver-process-items', response.message.html);
				});
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button create-server-nameserver-process-button main-button no-margin-bottom" href="javascript:void(0);" show="create-server-nameserver-process">Create Server Nameserver Process</a>';
		elementContent += '<div class="create-server-nameserver-process form hidden">';
		elementContent += '<div class="checkbox-container">';
		elementContent += '<span checked="0" class="checkbox server-nameserver-process-create-internal-process" name="create_internal_process"></span>';
		elementContent += '<label class="custom-checkbox-label server-nameserver-process-create-internal-process-field" name="create_internal_process">Create Internal Process</label>';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<label>Listening IP</label>';
		elementContent += '<div class="field-group no-margin">';
		elementContent += '<input class="server-nameserver-process-listening-ip-field" name="listening_ip" placeholder="Enter listening IP (e.g. 127.0.0.1, etc)" value="127.0.0.1" type="text">';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<div class="checkbox-option-container clear hidden no-padding" field="create_internal_process">';
		elementContent += '<label>External Source IP</label>';
		elementContent += '<div class="field-group no-margin">';
		elementContent += '<input class="server-nameserver-process-external-source-ip-field" placeholder="Enter external source IP from list of server node IPs" type="text">';
		elementContent += '</div>';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right" hide="create-server-nameserver-process" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-server-nameserver-process-button button main-button" href="javascript:void(0)">Add Server Nameserver Process</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		elements.html(itemListParameters.selector + ' .items', '<table class="table"><thead><tr><th></th><th>Listening IP <span disabled>&rarr;</span> External Source IP <span disabled>[Internal Source IP]</span></th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let serverNameserverProcessKey in response.data) {
			let serverNameserverProcess = response.data[serverNameserverProcessKey];
			elementContent += '<tr>';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + serverNameserverProcessKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td class="width-auto">';
			elementContent += serverNameserverProcess.listeningIp + ' <span disabled>&rarr;</span> ' + serverNameserverProcess.externalSourceIp + ' <span disabled>[' + (serverNameserverProcess.internalSourceIp || 'N/A') + ']</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-nameserver-process .add-server-nameserver-process-button', {
				method: function() {
					processServerNameserverProcessAdd();
				},
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-nameserver-process .server-nameserver-process-listening-ip-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerNameserverProcessAdd();
					}
				},
				name: itemListParameters.selector,
				type: 'keydown'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-nameserver-process .server-nameserver-process-external-source-ip-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerNameserverProcessAdd();
					}
				},
				name: itemListParameters.selector,
				type: 'keydown'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-server-nameserver-process-items', response.message.html);
		processProcesses();
	}
};
var processServerNodeItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listServerNodeItems');
	} else {
		var serverNodeElementContent = '<label>External IP</label>';
		serverNodeElementContent += '<div class="field-group no-margin-top">';
		serverNodeElementContent += '<input class="no-margin server-node-external-ip-field" name="server_external_node_external_ip" placeholder="Enter external IP address" type="text">';
		serverNodeElementContent += '</div>';
		serverNodeElementContent += '<div class="clear"></div>';
		serverNodeElementContent += '<label>Internal IP</label>';
		serverNodeElementContent += '<div class="field-group no-margin-top">';
		serverNodeElementContent += '<input class="no-margin server-node-internal-ip-field" name="server_node_internal_ip" placeholder="Enter optional internal IP address if already routed by the host" type="text">';
		serverNodeElementContent += '</div>';
		const processServerNodeAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					externalIp: elements.get(itemListParameters.selector + ' .create-server-node .server-node-external-ip-field').value,
					internalIp: elements.get(itemListParameters.selector + ' .create-server-node .server-node-internal-ip-field').value,
					serverId: apiRequestParameters.serverId
				},
				url: '/endpoint/server-nodes'
			});
			delete apiRequestParameters.from;
			elements.html(itemListParameters.selector + ' .create-server-node .add-server-node-button', 'Adding Server Node');
			elements.setAttribute(itemListParameters.selector + ' .create-server-node .add-server-node-button', 'disabled', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listServerNodeItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-server-node-items', response.message.html);
				});
			});
		};
		const processServerNodeEdit = function(selectedElementSelector, serverNodeId) {
			const processServerNodeEdit = function(selectedElementSelector, serverNodeId) {
				api.setRequestParameters({
					action: 'edit',
					data: {
						externalIp: elements.get(selectedElementSelector + ' .server-node-external-ip-field').value,
						internalIp: elements.get(selectedElementSelector + ' .server-node-internal-ip-field').value
					},
					url: '/endpoint/server-nodes',
					where: {
						id: serverNodeId
					}
				});
				delete apiRequestParameters.from;
				elements.setAttribute(selectedElementSelector + ' .editing .save-edit-button', 'disabled');
				elements.html(itemListParameters.selector + ' .editing .save-edit-button', 'Processing');
				api.sendRequest(function(response) {
					processItemList('listServerNodeItems', function() {
						if (
							typeof response.data.server !== 'undefined' &&
							typeof response.data.server.ip !== 'undefined'
						) {
							elements.html('.server-name', 'Server ' + response.data.server.ip);
						}

						elements.html(itemListParameters.selector + ' .message-container.list-server-node-items', response.message.html);
					});
				});
			};
			api.setRequestParameters({
				action: 'fetch',
				editing: {
					content: elements.html(selectedElementSelector + ' .table-text')
				},
				from: 'server_nodes',
				offset: 0,
				url: '/endpoint/server-nodes',
				where: {
					id: serverNodeId
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
					var elementContent = '<div class="field-group no-margin">';
					elementContent += serverNodeElementContent;
					elementContent += '<button class="alternate-button button no-margin-bottom server-node-cancel-edit-button margin-right">Cancel</button>';
					elementContent += '<button class="button main-button no-margin-bottom save-edit-button server-node-save-edit-button">Save Changes</button>';
					elementContent += '</div>';
					elements.addClass(selectedElementSelector + ' td:last-child', 'editing');
					elements.html(selectedElementSelector + ' .table-text', elementContent);
					render(function() {
						elements.setAttribute(selectedElementSelector + ' .table-text .server-node-external-ip-field', 'value', response.data[0].externalIp);

						if (response.data[0].internalIp) {
							elements.setAttribute(selectedElementSelector + ' .table-text .server-node-internal-ip-field', 'value', response.data[0].internalIp);
						}

						render(function() {
							elements.addEventListener(selectedElementSelector + ' .server-node-cancel-edit-button', {
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
							elements.addEventListener(selectedElementSelector + ' .server-node-external-ip-field', {
								method: function() {
									if (event.key == 'Enter') {
										processServerNodeEdit(selectedElementSelector, serverNodeId);
									}
								},
								type: 'keydown'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .server-node-internal-ip-field', {
								method: function() {
									if (event.key == 'Enter') {
										processServerNodeEdit(selectedElementSelector, serverNodeId);
									}
								},
								type: 'keydown'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .server-node-save-edit-button', {
								method: function() {
									processServerNodeEdit(selectedElementSelector, serverNodeId);
								},
								type: 'click'
							});
						});
					});
				}
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button main-button no-margin-bottom" href="javascript:void(0);" show="create-server-node">Create Server Node</a>';
		elementContent += '<div class="create-server-node form hidden">';
		elementContent += serverNodeElementContent;
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right" hide="create-server-node" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-server-node-button button main-button" href="javascript:void(0)">Add Server Node</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		elements.html(itemListParameters.selector + ' .items', '<table class="no-margin-bottom table"><thead><tr><th></th><th>External IP <span disabled>[Internal IP]</span></th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let serverNodeKey in response.data) {
			let serverNode = response.data[serverNodeKey];
			elementContent += '<tr server_node_id="' + serverNode.id + '">';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + serverNodeKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td>';
			elementContent += '<span class="table-text">';
			elementContent += serverNode.externalIp + ' <span disabled>[' + (serverNode.internalIp ? serverNode.internalIp : serverNode.externalIp) + ']</span>';
			elementContent += '</span>';
			elementContent += '<span class="table-actions">';
			elementContent += '<span class="button edit icon" server_node_id="' + serverNode.id + '"></span>';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			selectAllElements(itemListParameters.selector + ' .items tbody tr', function(selectedElementKey, selectedElement) {
				const serverNodeId = elements.getAttribute(selectedElement, 'server_node_id');
				const selectedElementSelector = itemListParameters.selector + ' .items tbody tr[server_node_id="' + serverNodeId + '"]';
				let serverNodeEditButton = elements.get(selectedElementSelector + ' .edit');
				elements.addEventListener(serverNodeEditButton, {
					method: function() {
						processServerNodeEdit(selectedElementSelector, serverNodeId);
					},
					type: 'click'
				});
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-node .add-server-node-button', {
				method: function() {
					processServerNodeAdd();
				},
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-node .server-node-external-ip-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerNodeAdd();
					}
				},
				type: 'keydown'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-node .server-node-internal-ip-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerNodeAdd();
					}
				},
				type: 'keydown'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-server-node-items', response.message.html);
		processProcesses();
	}
};
var processServerProxyProcesses = function() {
	if (typeof apiRequestParameters.listServerProxyProcessItems === 'undefined') {
		api.setRequestParameters({
			listServerProxyProcessItems: {
				action: 'fetch',
				callbacks: {
					onItemListReady: function(response, itemListParameters) {
						processServerProxyProcessItems(response, itemListParameters);
					}
				},
				from: 'server_proxy_processes',
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
								value: 'list_server_proxy_process_items'
							},
							{
								name: 'item_title',
								value: 'Remove selected proxy processes'
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
				resultsPerPage: 10,
				selector: '.item-list[from="server_proxy_processes"]',
				sort: {
					field: 'modified',
					order: 'DESC'
				},
				url: '/endpoint/server-proxy-processes',
				where: {
					serverId: apiRequestParameters.serverId
				}
			}
		});
	}

	onNodeReady([
		'apiRequestParameters',
		'listServerProxyProcessItems'
	], function() {
		processItemList('listServerProxyProcessItems', function() {
			elements.addClass('[process="server_proxy_processes"] .process.item-body', 'no-padding-top');
		});
	});
};
var processServerProxyProcessItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listServerProxyProcessItems');
	} else {
		let proxyProcessElementContent = '<label>Port</label>';
		proxyProcessElementContent += '<div class="field-group no-margin-top">';
		proxyProcessElementContent += '<input class="no-margin server-proxy-process-port-field" name="server_proxy_process_port" placeholder="Enter port number (e.g. 80, 8888, etc)" type="text">';
		proxyProcessElementContent += '</div>';
		proxyProcessElementContent += '<div class="clear"></div>';
		const processServerProxyProcessAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					port: elements.get('.create-server-proxy-process .server-proxy-process-port-field').value,
					serverId: apiRequestParameters.serverId
				},
				url: '/endpoint/server-proxy-processes'
			});
			delete apiRequestParameters.from;
			elements.html(itemListParameters.selector + ' .create-server-proxy-process .add-server-proxy-process-button', 'Adding Server Proxy Process');
			elements.setAttribute(itemListParameters.selector + ' .create-server-proxy-process .add-server-proxy-process-button', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listServerProxyProcessItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-server-proxy-process-items', response.message.html);
				});
			});
		};
		const processServerProxyProcessEdit = function(selectedElementSelector, serverProxyProcessId) {
			const processServerProxyProcessEdit = function(selectedElementSelector, serverProxyProcessId) {
				api.setRequestParameters({
					action: 'edit',
					data: {
						port: elements.get(selectedElementSelector + ' .server-proxy-process-port-field').value
					},
					url: '/endpoint/server-proxy-processes',
					where: {
						id: serverProxyProcessId
					}
				});
				delete apiRequestParameters.from;
				elements.setAttribute(selectedElementSelector + ' .editing .save-edit-button', 'disabled');
				elements.html(itemListParameters.selector + ' .editing .save-edit-button', 'Processing');
				api.sendRequest(function(response) {
					processItemList('listServerProxyProcessItems', function() {
						elements.html(itemListParameters.selector + ' .message-container.list-server-proxy-process-items', response.message.html);
					});
				});
			};
			api.setRequestParameters({
				action: 'view',
				editing: {
					content: elements.html(selectedElementSelector + ' .table-text')
				},
				url: '/endpoint/server-proxy-processes',
				where: {
					id: serverProxyProcessId
				}
			});
			elements.addClass(itemListParameters.selector + ' .edit.icon', 'hidden');
			elements.html(selectedElementSelector + ' .table-text', '<label class="label">Loading</label>');
			delete apiRequestParameters.from;
			api.sendRequest(function(response) {
				if (
					response.message.status &&
					response.message.status !== 'success'
				) {
					elements.html(selectedElementSelector + ' .table-text', '<p class="error message no-margin">' + response.message.text + '</p>');
					elements.removeClass(itemListParameters.selector + ' .edit.icon', 'hidden');
				}

				if (response.data) {
					var elementContent = '<div class="field-group no-margin">';
					elementContent += proxyProcessElementContent;
					elementContent += '<button class="alternate-button button no-margin-bottom margin-right server-proxy-process-port-cancel-edit-button">Cancel</button>';
					elementContent += '<button class="button main-button no-margin-bottom save-edit-button server-proxy-process-port-save-edit-button">Save Changes</button>';
					elementContent += '</div>';
					elements.addClass(selectedElementSelector + ' td:last-child', 'editing');
					elements.html(selectedElementSelector + ' .table-text', elementContent);
					render(function() {
						elements.setAttribute(selectedElementSelector + ' .table-text .server-proxy-process-port-field', 'value', response.data.port);
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .server-proxy-process-port-cancel-edit-button', {
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
							elements.addEventListener(selectedElementSelector + ' .server-proxy-process-port-field', {
								method: function() {
									if (event.key == 'Enter') {
										processServerProxyProcessEdit(selectedElementSelector, serverProxyProcessId);
									}
								},
								name: selectedElementSelector,
								type: 'keydown'
							});
						});
						render(function() {
							elements.addEventListener(selectedElementSelector + ' .server-proxy-process-port-save-edit-button', {
								method: function() {
									processServerProxyProcessEdit(selectedElementSelector, serverProxyProcessId);
								},
								type: 'click'
							});
						});
					});
				}
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button main-button no-margin-bottom" href="javascript:void(0);" show="create-server-proxy-process">Create Server Proxy Process</a>';
		elementContent += '<div class="create-server-proxy-process form hidden">';
		elementContent += proxyProcessElementContent;
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right no-margin-bottom" hide="create-server-proxy-process" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-server-proxy-process-button button main-button no-margin-bottom" href="javascript:void(0)">Add Server Proxy Process</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		elements.html(itemListParameters.selector + ' .items', '<table class="table"><thead><tr><th></th><th>Port <span disabled> [Protocol]</span></th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let serverProxyProcessKey in response.data) {
			let serverProxyProcess = response.data[serverProxyProcessKey];
			elementContent += '<tr server_proxy_process_id="' + serverProxyProcess.id + '">';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + serverProxyProcessKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td>';
			elementContent += '<span class="table-text">';
			elementContent += serverProxyProcess.port + ' <span disabled>[SOCKS]</span>';
			elementContent += '</span>';
			elementContent += '<span class="table-actions">';
			elementContent += '<span class="button edit icon" server_proxy_process_id="' + serverProxyProcess.id + '"></span>';
			elementContent += '</span>';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		render(function() {
			selectAllElements(itemListParameters.selector + ' .items tbody tr', function(selectedElementKey, selectedElement) {
				const serverProxyProcessId = elements.getAttribute(selectedElement, 'server_proxy_process_id');
				const selectedElementSelector = itemListParameters.selector + ' .items tbody tr[server_proxy_process_id="' + serverProxyProcessId + '"]';
				let serverProxyProcessEditButton = elements.get(selectedElementSelector + ' .edit');
				elements.addEventListener(serverProxyProcessEditButton, {
					method: function() {
						processServerProxyProcessEdit(selectedElementSelector, serverProxyProcessId);
					},
					type: 'click'
				});
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-proxy-process .add-server-proxy-process-button', {
				method: function() {
					processServerProxyProcessAdd();
				},
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server-proxy-process .server-proxy-process-port-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerProxyProcessAdd();
					}
				},
				name: itemListParameters.selector,
				type: 'keydown'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-server-proxy-process-items', response.message.html);
		processProcesses();
	}
};
var processServerProxyProcessNameserverProcessItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listServerProxyProcessNameserverProcessItems');
	} else {
		elements.html(itemListParameters.selector + ' .items', '<table class="table"><thead><tr><th></th><th>Listening IP <span disabled>[Source IP Count]</span></th></tr></thead><tbody></tbody></table>');
		var elementContent = '';

		for (let serverProxyProcessNameserverProcessKey in response.data) {
			let serverProxyProcessNameserverProcess = response.data[serverProxyProcessNameserverProcessKey];
			elementContent += '<tr>';
			elementContent += '<td class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + serverProxyProcessNameserverProcessKey + '">';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '<td class="width-auto">';
			elementContent += '<span class="table-text">';
			elementContent += serverProxyProcessNameserverProcess.listeningIp + ' <span disabled>[' + (serverProxyProcessNameserverProcess.sourceIpCount || 'Public') + ']</span>';
			elementContent += '</span>';
			elementContent += '</td>';
			elementContent += '</tr>';
		}

		elements.html(itemListParameters.selector + ' .items table tbody', elementContent);
		elements.html(itemListParameters.selector + ' .message-container.list-server-proxy-process-items', response.message.html);
		processProcesses();
	}
};
var processServers = function(response) {
	api.setRequestParameters({
		listServerItems: {
			action: 'fetch',
			callbacks: {
				onItemListReady: function(response, itemListParameters) {
					processServerItems(response, itemListParameters);
				}
			},
			from: 'servers',
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
							name: 'item_function',
							value: 1
						},
						{
							name: 'item_title',
							value: 'Activate selected server'
						},
						{
							name: 'process',
							value: 'activate'
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
							name: 'item_function',
							value: 1
						},
						{
							name: 'item_title',
							value: 'Deactivate selected server'
						},
						{
							name: 'process',
							value: 'deactivate'
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
							value: 'list_server_items'
						},
						{
							name: 'item_title',
							value: 'Remove selected servers'
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
			resultsPerPage: 10,
			selector: '.item-list[from="servers"]',
			sort: {
				field: 'modified',
				order: 'DESC'
			},
			url: '/endpoint/servers',
			where: {}
		}
	});
	onNodeReady([
		'apiRequestParameters',
		'listServerItems'
	], function() {
		processItemList('listServerItems');
	});
};
const processServerItems = function(response, itemListParameters) {
	if (typeof itemListParameters !== 'object') {
		processItemList('listServerItems');
	} else {
		const processServerAdd = function() {
			api.setRequestParameters({
				action: 'add',
				data: {
					ip: elements.get(itemListParameters.selector + ' .create-server .ip-field').value
				},
				url: '/endpoint/servers'
			});
			elements.html(itemListParameters.selector + ' .create-server .add-server-button', 'Adding Server');
			elements.setAttribute(itemListParameters.selector + ' .create-server .add-server-button', 'disabled', 'disabled');
			api.sendRequest(function(response) {
				processItemList('listServerItems', function() {
					elements.html(itemListParameters.selector + ' .message-container.list-server-items', response.message.html);
				});
			});
		};
		var elementContent = '<div class="additional-item-controls">';
		elementContent += '<a class="align-right button create-server-button main-button no-margin-bottom" href="javascript:void(0);" show="create-server">Create Server</a>';
		elementContent += '<div class="create-server form hidden">';
		elementContent += '<label>Main Server IP</label>';
		elementContent += '<div class="field-group no-margin">';
		elementContent += '<input class="ip-field" name="ip" placeholder="Enter the server\'s main IP" type="text">';
		elementContent += '</div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<a class="alternate-button button margin-right" hide="create-server" href="javascript:void(0)">Cancel</a>';
		elementContent += '<a class="add-server-button button main-button" href="javascript:void(0)">Add Server</a>';
		elementContent += '</div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector + ' .additional-item-controls-container', elementContent);
		var elementContent = '';

		for (let serverKey in response.data) {
			let server = response.data[serverKey];
			elementContent += '<div class="item-container item-button">';
			elementContent += '<div class="item">';
			elementContent += '<span class="checkbox-container">';
			elementContent += '<span checked="0" class="checkbox" index="' + serverKey + '" item_id="' + server.id + '"></span>';
			elementContent += '</span>';
			elementContent += '<div class="item-body item-checkbox no-padding-bottom">';
			elementContent += '<p><a href="/servers/' + server.id + '">Server ' + server.ip + '</a></p>';
			elementContent += '<p>' + ((server.statusActivated && server.statusDeployed) ? 'Active' : 'Inactive') + '</p>';
			elementContent += '<p class="no-margin">' + server.ipCount + ' IP' + (server.ipCount !== 1 ? 's' : '') + '</p>';
			elementContent += '</div>';
			elementContent += '</div>';
			elementContent += '<div class="item-link-container"><a class="item-link" href="/servers/' + server.id + '"></a></div>';
			elementContent += '</div>';
		}

		elements.html(itemListParameters.selector + ' .items', elementContent);
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server .add-server-button', {
				method: function() {
					processServerAdd();
				},
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemListParameters.selector + ' .create-server .ip-field', {
				method: function() {
					if (event.key == 'Enter') {
						processServerAdd();
					}
				},
				type: 'keydown'
			});
		});
		elements.html(itemListParameters.selector + ' .message-container.list-server-items', response.message.html);
		processProcesses();
	}
};
