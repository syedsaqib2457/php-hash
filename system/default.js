// todo: use response.data.length instead of response.count after refactor (count value not included in fetch)
const api = {
	setRequestParameters: function(requestParameters, mergeRequestParameters) {
		if (
			typeof requestParameters === 'object' &&
			requestParameters
		) {
			for (let requestParameterKey in requestParameters) {
				let requestParameter = requestParameters[requestParameterKey];

				if (typeof apiRequestParameters[requestParameterKey] !== 'undefined') {
					if (mergeRequestParameters === true) {
						let apiRequestParametersToMerge = apiRequestParameters[requestParameterKey];

						if (typeof requestParameter === 'object') {
							for (let requestParameterNestedKey in requestParameter) {
								apiRequestParametersToMerge[requestParameterNestedKey] = requestParameter[requestParameterNestedKey];
							}
						} else {
							apiRequestParametersToMerge = requestParameter;
						}

						requestParameter = apiRequestParametersToMerge;
					}
				}

				Object.defineProperty(apiRequestParameters, requestParameterKey, {
					configurable: true,
					enumerable: true,
					value: requestParameter,
					writable: false
				});
			}
		}
	},
	sendRequest: function(callback) {
		let request = new XMLHttpRequest();
		request.open('POST', apiRequestParameters.url, true);
		request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		request.send('json=' + encodeURIComponent(JSON.stringify(apiRequestParameters)));
		request.onload = function(response) {
			response = JSON.parse(response.target.response);

			if (
				typeof response.redirect === 'string' &&
				response.redirect
			) {
				window.location.href = response.redirect;
				return false;
			}

			if (typeof response.items !== 'undefined') {
				api.setRequestParameters({
					items: response.items
				});
			}

			if (typeof response.message === 'undefined') {
				response.message = {
					html: '',
					text: ''
				};
			}

			if (
				typeof response.message.status !== 'undefined' &&
				response.message.text
			) {
				response.message.html = '<p class="message' + (response.message.status ? ' ' + response.message.status : '') + '">' + response.message.text + '</p>';
			}

			callback(response);
			processWindowEvents('resize');
		};
	}
};
var apiRequestParameters = {
	data: {},
	items: {}
};
const camelCaseString = function(string) {
	let stringParts = string.split('_');

	for (let stringPartKey in stringParts) {
		let stringPart = stringParts[stringPartKey];

		if (stringPartKey > 0) {
			stringParts[stringPartKey] = stringPart.charAt(0).toUpperCase() + stringPart.substr(1);
		}
	}

	return stringParts.join('');
};
const capitalizeString = function(string) {
	let stringParts = string.split(' ');

	for (let stringPartKey in stringParts) {
		let stringPart = stringParts[stringPartKey];
		stringParts[stringPartKey] = stringPart.charAt(0).toUpperCase() + stringPart.substr(1);
	}

	return stringParts.join(' ');
};
const closeProcesses = function() {
	delete apiRequestParameters.processing;
	elements.addClass('.process-container', 'hidden');
	elements.html('.process .message-container', '');
	elements.removeClass('footer, header, main', 'hidden');
	processWindowEvents('resize');
	window.scroll(0, 0);
};
const elements = {
	addClass: function(selector, className) {
		const addClass = function(selectedElement, className) {
			selectedElement.classList.add(className);
		};

		if (typeof selector === 'object') {
			addClass(selector, className);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				addClass(selectedElement, className);
			});
		}
	},
	addEventListener: function(selector, listener) {
		let element = selector;
		let listenerName = listener.type + 'Listener';

		if (typeof selector === 'string') {
			element = document.querySelector(selector);
		}

		if (typeof listener.name === 'string') {
			listenerName = listener.name;
		}

		if (typeof element[listenerName] !== 'undefined') {
			element.removeEventListener(listener.type, element[listenerName]);
		}

		element[listenerName] = listener.method;
		element.addEventListener(listener.type, element[listenerName]);
	},
	addScrollable: function(selector, callback) {
		const addScrollable = function(selectedElement) {
			const event = function() {
				const elementContainerDetails = selectedElement.parentNode.getBoundingClientRect();
				selectedElement.details = elementContainerDetails;
				callback(selectedElement);
				selectedElement.setAttribute('scrolling', +(window.pageYOffset > (elementContainerDetails.top + window.pageYOffset)));
			};

			windowEvents.resize.push(event);
			windowEvents.scroll.push(event);
		};

		if (typeof selector === 'object') {
			addScrollable(selector);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				addScrollable(selectedElement);
			});
		}
	},
	get: function(selector) {
		return (typeof selector === 'object' ? selector : document.querySelector(selector));
	},
	getAttribute: function(selector, attribute) {
		const element = (typeof selector === 'object' ? selector : document.querySelector(selector));
		let value = '';

		if (
			element &&
			element.hasAttribute(attribute)
		) {
			value = element.getAttribute(attribute);
		}

		return value;
	},
	hasAttribute: function(selector, attribute) {
		const element = (typeof selector === 'object' ? selector : document.querySelector(selector));

		if (
			element &&
			element.hasAttribute(attribute)
		) {
			return true;
		}

		return false;
	},
	hasClass: function(selector, className) {
		const hasClass = function(selectedElement, className) {
			return selectedElement.classList.contains(className);
		};
		let elementHasClass = false;

		if (typeof selector === 'object') {
			elementHasClass = hasClass(selector, className);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				if (hasClass(selectedElement, className)) {
					elementHasClass = true;
				}
			});
		}

		return elementHasClass;
	},
	html: function(selector, value) {
		let element = (typeof selector === 'object' ? selector : document.querySelector(selector));

		if (!element) {
			return false;
		}

		if (typeof value !== 'undefined') {
			if (typeof selector === 'object') {
				element.innerHTML = value;
			}

			if (typeof selector === 'string') {
				selectAllElements(selector, function(selectedElementKey, selectedElement) {
					selectedElement.innerHTML = value;
				});
			}
		}

		return value || element.innerHTML;
	},
	removeAttribute: function(selector, attribute) {
		const removeAttribute = function(selectedElement, attribute, value) {
			if (selectedElement.hasAttribute(attribute)) {
				selectedElement.removeAttribute(attribute, value);
			}
		};

		if (typeof selector === 'object') {
			removeAttribute(selector, attribute);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				removeAttribute(selectedElement, attribute);
			});
		}
	},
	removeClass: function(selector, className) {
		const removeClass = function(selectedElement, className) {
			selectedElement.classList.remove(className);
		};

		if (typeof selector === 'object') {
			removeClass(selector, className);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				removeClass(selectedElement, className);
			});
		}
	},
	setAttribute: function(selector, attribute, value) {
		const setAttribute = function(selectedElement, attribute, value) {
			if (selectedElement) {
				selectedElement.setAttribute(attribute, value);
			}
		};

		if (typeof selector === 'object') {
			setAttribute(selector, attribute, value);
		}

		if (typeof selector === 'string') {
			selectAllElements(selector, function(selectedElementKey, selectedElement) {
				setAttribute(selectedElement, attribute, value);
			});
		}
	}
};
const onDocumentReady = function(callback) {
	if (document.readyState !== 'complete') {
		setTimeout(function() {
			onDocumentReady(callback);
		}, 10);
	} else {
		callback();
	}
};
const onNodeReady = function(nodeNames, callback) {
	let nodeObject = window;
	let nodeReady = true;

	for (let nodeNameKey in nodeNames) {
		let nodeName = nodeNames[nodeNameKey];
		nodeObject = nodeObject[nodeName];

		if (typeof nodeObject === 'undefined') {
			nodeReady = false;
			break;
		}
	}

	if (nodeReady === false) {
		setTimeout(function() {
			onNodeReady(nodeNames, callback);
		}, 100);
	} else {
		callback();
	}
};
const openProcess = function(processSelector) {
	if (
		processSelector &&
		elements.get(processSelector)
	) {
		elements.addClass('footer, header, main', 'hidden');
		elements.removeClass(processSelector, 'hidden');
	}

	window.scroll(0, 0);
};
const processItemList = function(itemListName, callback) {
	let itemListParameters = apiRequestParameters[itemListName];

	if (apiRequestParameters[itemListName].initial === true) {
		elements.addClass('.item-footer', 'hidden');
		var elementContent = '<div class="item-container item-configuration-container">';
		elementContent += '<div class="item">';
		elementContent += '<div class="item-configuration">';
		elementContent += '<div class="item-controls-container controls-container' + (itemListParameters.resultsPerPage >= 10 ? ' scrollable' : '') + '">';
		elementContent += '<div class="item-header">';
		elementContent += '<div class="item-controls-heading-container"></div>';
		elementContent += '<span class="clear"></span>';
		elementContent += '<div class="align-right pagination-container">';
		elementContent += '<span class="pagination" page_current="' + itemListParameters.page + '" results="' + itemListParameters.resultsPerPage + '">';
		elementContent += '<span class="align-left hidden item-controls results">';
		elementContent += '<span class="result-first"></span> - <span class="result-last"></span> of <span class="results-total"></span>';
		elementContent += '</span>';
		elementContent += '<span class="align-left button icon previous"></span>';
		elementContent += '<span class="align-left button icon next"></span>';
		elementContent += '</span>';
		elementContent += '</div>';

		if (
			typeof itemListParameters.options === 'object' &&
			itemListParameters.options
		) {
			elementContent += '<div class="align-left hidden item-control-button-container item-controls selectable-item-controls">';

			for (let optionKey in itemListParameters.options) {
				let option = itemListParameters.options[optionKey];
				elementContent += '<' + option.tag;

				if (
					typeof option.attributes === 'object' &&
					option.attributes
				) {
					for (let attributeKey in option.attributes) {
						let attribute = option.attributes[attributeKey];
						elementContent += ' ' + attribute.name;

						if (typeof attribute.value !== 'undefined') {
							elementContent += '="' + attribute.value + '"';
						}
					}
				}

				elementContent += '></' + option.tag + '>';
			}

			elementContent += '</div>';
		}

		elementContent += '<div class="clear"></div>';
		elementContent += '<p class="hidden item-controls no-margin-bottom selectable-item-controls">';
		elementContent += '<span class="checked-container">';
		elementContent += '<span class="total-checked">0</span> of <span class="results-total"></span> selected.</span>';
		elementContent += '<a class="item-action hidden" href="javascript:void(0);" index="all" status="1"><span class="action">Select</span> all results</a>';
		elementContent += '<span class="clear"></span>';
		elementContent += '</p>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<div class="additional-item-controls-container align-left full-width"></div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<div class="message-container status"></div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '<div class="message-container ' + snakeCaseString(itemListName, '-') + '"></div>';
		elementContent += '<div class="clear"></div>';
		elementContent += '</div>';
		elementContent += '</div>';
		elementContent += '<div class="item-body">';
		elementContent += '<div class="items" previous_checked="0"></div>';
		elementContent += '</div>';
		elements.html(itemListParameters.selector, elementContent);
		Object.defineProperty(itemListParameters, 'itemListName', {
			configurable: true,
			enumerable: true,
			value: itemListName,
			writable: false
		});
		Object.defineProperty(itemListParameters, 'listContentSelector', {
			configurable: true,
			enumerable: true,
			value: itemListParameters.selector + ' > .item-container > div > div > .item-body',
			writable: false
		});
		Object.defineProperty(itemListParameters, 'listControlContainerSelector', {
			configurable: true,
			enumerable: true,
			value: itemListParameters.selector + ' > .item-container > div > div > .item-controls-container',
			writable: false
		});
		elements.html(itemListParameters.listControlContainerSelector + ' > .item-header > p .total-checked', +(apiRequestParameters[itemListName].selectedItemCount || 0));
	}

	let itemListMatrix = (
		typeof apiRequestParameters.items[itemListName] !== 'undefined' &&
		typeof apiRequestParameters.items[itemListName].data === 'object'
	) ? apiRequestParameters.items[itemListName].data : [];
	let itemListMatrixCount = itemListMatrix.length;
	const itemListControlButtonContainerSelector = itemListParameters.listControlContainerSelector + ' > .item-header > .item-control-button-container';
	const itemListPaginationSelector = itemListParameters.listControlContainerSelector + ' > .item-header > .pagination-container .pagination';
	const itemListControlSelector = itemListParameters.listControlContainerSelector + ' > .item-header > .item-controls, ' + itemListPaginationSelector + ' .item-controls, ' + itemListParameters.listContentSelector + ' .items';
	const itemListSelectedDetailsSelector = itemListParameters.listControlContainerSelector + ' > .item-header > p';
	const statusMessageSelector = itemListParameters.listControlContainerSelector + ' > .item-header > .message-container.status';
	elements.html(statusMessageSelector, '<p class="message">Loading</p>');
	const itemToggle = function(itemListItem) {
		let previousChecked = elements.getAttribute(itemListParameters.listContentSelector + ' .items', 'previous_checked');
		elements.setAttribute(itemListParameters.listContentSelector + ' .items', 'current_checked', elements.getAttribute(itemListItem, 'index'));
		processItemListMatrix(window.event.shiftKey ? range(previousChecked, itemListItem.getAttribute('index')) : [itemListItem.getAttribute('index')], window.event.shiftKey ? +elements.getAttribute(itemListParameters.listContentSelector + ' .checkbox[index="' + previousChecked + '"]', 'checked') !== 0 : +elements.getAttribute(itemListItem, 'checked') === 0);
		elements.setAttribute(itemListParameters.listContentSelector + ' .items', 'previous_checked', elements.getAttribute(itemListItem, 'index'));
		itemToggleEvent();
	};
	const itemAll = elements.get(itemListSelectedDetailsSelector + ' .item-action[index="all"]');
	const itemAllVisible = elements.get(itemListControlButtonContainerSelector + ' .checkbox[index="all-visible"]');
	const itemToggleAllVisible = function(item) {
		elements.setAttribute(itemListParameters.listContentSelector + ' .items', 'current_checked', 0);
		elements.setAttribute(itemListParameters.listContentSelector + ' .items', 'previous_checked', 0);
		processItemListMatrix(range(0, selectAllElements(itemListParameters.listContentSelector + ' .items .checkbox').length - 1), +item.getAttribute('checked') === 0);
		itemToggleEvent();
	};
	const itemToggleEvent = function() {
		if (
			itemListSelectedCount === 1 &&
			elements.hasAttribute(itemListParameters.listContentSelector + ' .items .checkbox[checked="1"]', 'item_id')
		) {
			var mergeRequestParameters = {};
			mergeRequestParameters[itemListName] = {
				itemId: elements.getAttribute(itemListParameters.listContentSelector + ' .items .checkbox[checked="1"]', 'item_id')
			};
			api.setRequestParameters(mergeRequestParameters, true);
		}

		if (
			typeof itemListParameters.callbacks !== 'undefined' &&
			typeof itemListParameters.callbacks.onItemListItemToggle === 'function'
		) {
			itemListParameters.callbacks.onItemListItemToggle();
		}
	};
	const processItemListMatrix = function(itemListItemIndexes, itemState) {
		if (elements.get(itemListParameters.selector + ' .editing')) {
			elements.html(itemListParameters.selector + ' .editing .table-text', apiRequestParameters.editing.content);
			elements.removeClass(itemListParameters.selector + ' .edit.icon', 'hidden');
			elements.removeClass(itemListParameters.selector + ' .editing', 'editing');
			delete apiRequestParameters.editing;
		}

		let itemListItemCount = itemListSelectedCount = 0;
		const itemListMatrixLineSizeMaximum = +('1' + repeat(Math.min(elements.html(itemListSelectedDetailsSelector + ' .results-total').length, 4), '0'));
		const itemListPageResultCount = (+elements.html(itemListPaginationSelector + ' .result-last') - +elements.html(itemListPaginationSelector + ' .result-first') + 1);
		const itemListResultsTotal = +elements.html(itemListSelectedDetailsSelector + ' .results-total');
		const itemListMatrixLineSize = function(key) {
			return Math.min(itemListMatrixLineSizeMaximum, itemListResultsTotal - (key * itemListMatrixLineSizeMaximum)).toString();
		};
		const processItemListMatrixSelection = function(item) {
			let keyIndexes = range(0, Math.floor(itemListResultsTotal / itemListMatrixLineSizeMaximum));
			elements.html(itemListSelectedDetailsSelector + ' .total-checked', (selectionStatus = +item.getAttribute('status')) ? itemListResultsTotal : 0);

			for (let keyIndexKey in keyIndexes) {
				itemListMatrix[keyIndexes[keyIndexKey]] = selectionStatus + itemListMatrixLineSize(keyIndexes[keyIndexKey]);
			}

			itemListMatrix = (selectionStatus ? itemListMatrix : []);
			processItemListMatrix(range(0, selectAllElements(itemListParameters.listContentSelector + ' .items .checkbox').length - 1));
		};

		if (
			(
				typeof itemListItemIndexes[1] === 'number' &&
				itemListItemIndexes[1] < 0
			) ||
			(
				!itemAll &&
				!itemAllVisible
			)
		) {
			return;
		}

		if (!itemListMatrix.length) {
			elements.html(itemListSelectedDetailsSelector + ' .total-checked', itemListSelectedCount);
		}

		for (let itemListItemIndexKey in itemListItemIndexes) {
			let itemListItemIndex = itemListItemIndexes[itemListItemIndexKey];
			let encodeCount = 1;
			let encodedListMatrixLineItems = [];
			let index = ((itemListParameters.page * itemListParameters.resultsPerPage) - itemListParameters.resultsPerPage) + +itemListItemIndex;
			let item = elements.get(itemListParameters.listContentSelector + ' .items .checkbox[index="' + itemListItemIndex + '"]');
			let key = Math.floor(index / itemListMatrixLineSizeMaximum);

			if (!itemListMatrix[key]) {
				itemListMatrix[key] = repeat(itemListMatrixLineSize(key), '0');
			} else {
				itemListMatrix[key] = itemListMatrix[key].split('_');

				for (var itemListMatrixKeyKey in itemListMatrix[key]) {
					itemListMatrix[key][itemListMatrixKeyKey] = repeat(itemListMatrix[key][itemListMatrixKeyKey].substr(1), itemListMatrix[key][itemListMatrixKeyKey].substr(0, 1));
				}

				itemListMatrix[key] = itemListMatrix[key].join("");
			}

			const itemListMatrixLineIndex = index - (key * itemListMatrixLineSizeMaximum);

			if (typeof itemState === 'boolean') {
				itemListMatrix[key] = itemListMatrix[key].substr(0, itemListMatrixLineIndex) + +itemState + itemListMatrix[key].substr(itemListMatrixLineIndex + Math.max(1, ('' + +itemState).length))
			}

			itemListMatrix[key] = itemListMatrix[key].split("");
			itemListMatrix[key].map(function(itemStatus, itemStatusIndex) {
				if (itemStatus != itemListMatrix[key][itemStatusIndex + 1]) {
					encodedListMatrixLineItems.push(itemStatus + encodeCount);
					encodeCount = 0;
				}

				encodeCount++;
			});
			elements.setAttribute(item, 'checked', +itemListMatrix[key][itemListMatrixLineIndex]);
			itemListMatrix[key] = encodedListMatrixLineItems.join('_');
		}

		let itemListItemCountIndexes = range(0, itemListPageResultCount - 1);

		for (let itemListItemCountIndexKey in itemListItemCountIndexes) {
			if (+(elements.getAttribute(itemListParameters.listContentSelector + ' .items .checkbox[index="' + itemListItemCountIndexes[itemListItemCountIndexKey] + '"]', 'checked'))) {
				itemListItemCount++;
			}
		}

		const allVisibleChecked = (itemListItemCount === itemListPageResultCount);
		let itemCheckedCount = +elements.html(itemListSelectedDetailsSelector + ' .total-checked');

		if (
			apiRequestParameters[itemListName].initial === true &&
			typeof apiRequestParameters[itemListName].selectedItemCount !== 'undefined'
		) {
			itemCheckedCount = apiRequestParameters[itemListName].selectedItemCount;
		} else if (typeof itemState === 'boolean') {
			itemCheckedCount += (itemListItemCount - itemListMatrixCount);
		}

		elements.html(itemListSelectedDetailsSelector + ' .total-checked', itemCheckedCount);
		elements.addClass(itemAll, 'hidden');
		render(function() {
			elements.addEventListener(itemAll, {
				method: function() {
					processItemListMatrixSelection(itemAll);
				},
				name: itemListParameters.selector,
				type: 'click'
			});
		});
		render(function() {
			elements.addEventListener(itemAllVisible, {
				method: function() {
					itemToggleAllVisible(itemAllVisible);
				},
				name: itemListParameters.selector,
				type: 'click'
			});
		});
		elements.setAttribute(itemAllVisible, 'checked', +(allVisibleChecked));
		itemListSelectedCount = +elements.html(itemListSelectedDetailsSelector + ' .total-checked');

		if (
			itemListPageResultCount != itemListResultsTotal &&
			(
				(
					allVisibleChecked &&
					itemListSelectedCount < itemListResultsTotal
				) ||
				itemListSelectedCount === itemListResultsTotal
			)
		) {
			let selectionStatus = +(itemListSelectedCount === itemListResultsTotal);
			elements.html(itemListSelectedDetailsSelector + ' .action', (selectionStatus ? 'Unselect' : 'Select'));
			elements.removeClass(itemAll, 'hidden');
			elements.setAttribute(itemAll, 'status', +(selectionStatus === 0));
		}

		elements.removeClass(itemListControlButtonContainerSelector + ' .icon[item_function]', 'hidden');
		itemListMatrixCount = itemListItemCount;
		processWindowEvents('resize');
		var mergeRequestParameters = {
			items: {}
		};
		mergeRequestParameters.items[itemListName] = {
			data: itemListMatrix,
			from: itemListParameters.from,
			token: []
		};

		if (
			typeof apiRequestParameters.items[itemListName] !== 'undefined' &&
			typeof apiRequestParameters.items[itemListName].token === 'object'
		) {
			mergeRequestParameters.items[itemListName].token = apiRequestParameters.items[itemListName].token;
		}

		mergeRequestParameters[itemListName] = {
			selectedItemCount: itemListSelectedCount
		};
		api.setRequestParameters(mergeRequestParameters, true);
		selectAllElements(itemListControlButtonContainerSelector + ' .icon[item_function]', function(selectedElementKey, selectedElement) {
			const booleanAttributeStringValues = ['false', 'true'];
			let itemFunction = elements.getAttribute(selectedElement, 'item_function');

			if (
				booleanAttributeStringValues.indexOf(itemFunction) === 0 ||
				(
					!itemFunction &&
					!itemListSelectedCount
				) ||
				(
					booleanAttributeStringValues.indexOf(itemFunction) > 0 &&
					(
						itemListResultsTotal === itemListSelectedCount ||
						itemListSelectedCount === 0
					)
				) ||
				(
					!isNaN(parseInt(itemFunction)) &&
					(
						itemFunction > 0 &&
						(
							itemListSelectedCount > itemFunction ||
							itemListSelectedCount === 0
						)
					)
				)
			) {
				elements.addClass(selectedElement, 'hidden');
			}
		});
	};
	elements.addClass(itemListControlSelector, 'hidden');
	elements.setAttribute(itemListPaginationSelector + ' .next', 'page', 0);
	elements.setAttribute(itemListPaginationSelector + ' .previous', 'page', 0);
	let itemListRequestParameters = {
		action: itemListParameters.action,
		from: itemListParameters.from,
		itemListName: snakeCaseString(itemListName, '_'),
		limit: itemListParameters.resultsPerPage,
		offset: ((itemListParameters.page * itemListParameters.resultsPerPage) - itemListParameters.resultsPerPage),
		url: itemListParameters.url
	};

	if (typeof itemListParameters.sort !== 'undefined') {
		itemListRequestParameters.sort = itemListParameters.sort;
	}

	if (typeof itemListParameters.where !== 'undefined') {
		itemListRequestParameters.where = itemListParameters.where;
	}

	api.setRequestParameters(itemListRequestParameters);
	var mergeRequestParameters = {
		items: {}
	};
	mergeRequestParameters.items[itemListName] = {
		data: itemListMatrix,
		from: itemListParameters.from,
		token: []
	};

	if (
		typeof apiRequestParameters.items[itemListName] !== 'undefined' &&
		typeof apiRequestParameters.items[itemListName].token === 'object'
	) {
		mergeRequestParameters.items[itemListName].token = apiRequestParameters.items[itemListName].token;
	}

	api.setRequestParameters(mergeRequestParameters, true);
	api.sendRequest(function(response) {
		itemListMatrix = typeof response.items[itemListName].data !== 'undefined' ? response.items[itemListName].data : [];
		itemListMatrixCount = itemListMatrix.length;

		if (
			typeof itemListParameters.callbacks !== 'undefined' &&
			typeof itemListParameters.callbacks.onItemListReady === 'function'
		) {
			itemListParameters.callbacks.onItemListReady(response, itemListParameters);
		}

		let data = response.data;

		if (
			typeof itemListParameters.data !== 'undefined' &&
			response.data[itemListParameters.data]
		) {
			data = response.data[itemListParameters.data];
		}

		let lastResult = itemListParameters.page * itemListParameters.resultsPerPage;
		elements.html(itemListPaginationSelector + ' .result-first', itemListParameters.page === 1 ? itemListParameters.page : ((itemListParameters.page * itemListParameters.resultsPerPage) - itemListParameters.resultsPerPage) + 1);
		elements.html(itemListPaginationSelector + ' .result-last', lastResult >= response.count ? response.count : lastResult);
		elements.html(itemListSelectedDetailsSelector + ' .results-total, ' + itemListPaginationSelector + ' .results-total', response.count);
		elements.setAttribute(itemListPaginationSelector, 'page_current', itemListParameters.page);
		elements.setAttribute(itemListPaginationSelector + ' .next', 'page', +elements.html(itemListPaginationSelector + ' .result-last') < response.count ? itemListParameters.page + 1 : 0);
		elements.setAttribute(itemListPaginationSelector + ' .previous', 'page', itemListParameters.page <= 0 ? 0 : itemListParameters.page - 1);

		if (apiRequestParameters[itemListName].initial === true) {
			selectAllElements(itemListPaginationSelector + ' .button', function(selectedElementKey, selectedElement) {
				render(function() {
					elements.addEventListener(selectedElement, {
						method: function() {
							if ((page = +elements.getAttribute(selectedElement, 'page')) > 0) {
								var mergeRequestParameters = {};
								mergeRequestParameters[itemListName] = {};
								mergeRequestParameters[itemListName].page = page;
								api.setRequestParameters(mergeRequestParameters, true);
								processItemList(itemListName);
							}
						},
						name: itemListParameters.selector,
						type: 'click'
					});
				});
			});
		}

		processProcesses();

		if (
			typeof data.length !== 'undefined' &&
			data.length
		) {
			elements.removeClass(itemListControlSelector, 'hidden');
		} else {
			elements.addClass(itemListControlSelector, 'hidden');
		}

		if (elements.get(itemListParameters.listContentSelector + ' .items .checkbox[index]')) {
			processItemListMatrix(range(0, data.length - 1));
		} else {
			elements.addClass(itemListParameters.listControlContainerSelector + ' > .item-header > .selectable-item-controls', 'hidden');
		}

		if (typeof callback === 'function') {
			callback(response, itemListParameters);
			processWindowEvents('resize');
		}

		selectAllElements(itemListParameters.listContentSelector + ' .items .checkbox', function(selectedElementKey, selectedElement) {
			render(function() {
				elements.addEventListener(selectedElement, {
					method: function() {
						itemToggle(selectedElement);
					},
					name: itemListParameters.selector,
					type: 'click'
				});
			});
		});

		if (apiRequestParameters[itemListName].initial === true) {
			var mergeRequestParameters = {};
			mergeRequestParameters[itemListName] = {
				initial: false
			};
			api.setRequestParameters(mergeRequestParameters, true);
			elements.addScrollable(itemListParameters.listControlContainerSelector + '.scrollable', function(element) {
				if (element.details.width) {
					const selectorContainerDetails = elements.get(itemListParameters.selector).parentNode.getBoundingClientRect();
					elements.get(itemListParameters.listContentSelector).setAttribute('style', 'padding-top: ' + (elements.get(itemListParameters.listControlContainerSelector + ' > .item-header').clientHeight + 1) + 'px');
					elements.setAttribute(element, 'style', 'width: ' + element.details.width + 'px; right: ' + (element.details.width - selectorContainerDetails.width) + 'px');
					elements.setAttribute(element, 'scrolled_to_the_bottom', +(window.pageYOffset > Math.max(0, (element.details.bottom + window.pageYOffset - +(elements.get(itemListParameters.listControlContainerSelector + ' > .item-header').clientHeight)))));
				}
			});
		}

		elements.html(statusMessageSelector, '');

		if (
			typeof apiRequestParameters.search !== 'undefined' &&
			typeof apiRequestParameters.search[itemListName] !== 'undefined'
		) {
			elements.html(statusMessageSelector, '<a class="clear-search" href="javascript:void(0);"">Clear search</a>');
			render(function() {
				elements.addEventListener('.clear-search', {
					method: function() {
						delete apiRequestParameters.items[itemListName].data;
						delete apiRequestParameters.search[itemListName];
						processItemList(itemListName);
					},
					type: 'click'
				});
			});
		}

		elements.removeClass('.item-footer', 'hidden');
		processWindowEvents('resize');
	});
};
var processLogin = function(processElement, processSubmit) {
	if (processSubmit) {
		api.setRequestParameters({
			action: 'login',
			url: '/endpoint/main'
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			elements.html('.login.message-container', response.message.html);
		});
	} else {
		if (navigator.cookieEnabled) {
			const uniqueId = apiRequestParameters.settings.uniqueId;
			document.cookie = 'sessionId=_' + uniqueId + '; domain=' + apiRequestParameters.settings.baseDomain + '; max-age=' + 100000000 + '; path=/; samesite;';
			api.setRequestParameters({
				settings: {
					sessionId: uniqueId
				}
			}, true);
		}
	}
};
const processProcesses = function() {
	selectAllElements('.process-container', function(selectedElementKey, selectedElement) {
		let processName = elements.getAttribute(selectedElement, 'process');
		selectAllElements('[process="' + processName + '"] input[type="password"], [process="' + processName + '"] input[type="text"]', function(selectedElementKey, selectedElement) {
			render(function() {
				elements.addEventListener(selectedElement, {
					method: function() {
						if (
							event.key == 'Enter' &&
							elements.get('[process="' + processName + '"] .button.submit') &&
							!elements.hasClass(selectedElement, 'no-process')
						) {
							const submitButton = elements.get('[process="' + processName + '"] .button.submit[process="' + processName + '"]');

							if (processSubmitButtonText) {
								elements.html(submitButton, processSubmitButtonText);
							}

							elements.removeAttribute(submitButton, 'disabled');
							window.scroll(0, 0);
							const processSubmitButtonText = elements.html(submitButton);
							api.setRequestParameters({
								processing: true
							});
							elements.setAttribute(selectedElement, 'process', processName);
							elements.setAttribute(submitButton, 'disabled', 'disabled');
							elements.html(submitButton, 'Processing');
							processProcess(selectedElement, true);
						}
					},
					type: 'keydown'
				});
			});
		});
	});
	selectAllElements('.button.close', function(selectedElementKey, selectedElement) {
		render(function() {
			elements.addEventListener(selectedElement, {
				method: function() {
					closeProcesses();
				},
				type: 'click'
			});
		});
	});
	selectAllElements('a[hide]', function(selectedElementKey, selectedElement) {
		render(function() {
			elements.addEventListener(selectedElement, {
				method: function() {
					elements.addClass('.' + selectedElement.getAttribute('hide'), 'hidden');
					elements.html('.' + selectedElement.getAttribute('show') + ' .message-container', '');
					elements.removeClass('[show="' + selectedElement.getAttribute('hide') + '"]', 'hidden');
					processWindowEvents('resize');
				},
				type: 'click'
			});
		});
	});
	selectAllElements('a[show]', function(selectedElementKey, selectedElement) {
		render(function() {
			elements.addEventListener(selectedElement, {
				method: function() {
					elements.addClass(selectedElement, 'hidden');
					elements.html('.' + selectedElement.getAttribute('show') + ' .message-container', '');
					elements.removeClass('.' + selectedElement.getAttribute('show'), 'hidden');
					processWindowEvents('resize');
				},
				type: 'click'
			});
		});
	});
	selectAllElements('.checkbox, label.custom-checkbox-label', function(selectedElementKey, selectedElement) {
		render(function() {
			elements.addEventListener(selectedElement, {
				method: function() {
					let hiddenFieldSelector = 'div[field="' + selectedElement.getAttribute('name') + '"]';
					let itemSelector = '.checkbox[name="' + selectedElement.getAttribute('name') + '"]';

					if (elements.get(hiddenFieldSelector)) {
						if (elements.hasClass(hiddenFieldSelector, 'hidden')) {
							elements.removeClass(hiddenFieldSelector, 'hidden');
						} else {
							elements.addClass(hiddenFieldSelector, 'hidden');
						}
					}

					elements.setAttribute(itemSelector, 'checked', +!+elements.getAttribute(itemSelector, 'checked'));
					processWindowEvents('resize');
				},
				type: 'click'
			});
		});
	});
	selectAllElements('.button.process-button, .process .button.submit', function(selectedElementKey, selectedElement) {
		render(function() {
			elements.addEventListener(selectedElement, {
				method: function() {
					let processSubmit = elements.hasClass(selectedElement, 'submit');

					if (processSubmitButtonText) {
						elements.html(selectedElement, processSubmitButtonText);
					}

					elements.removeAttribute(selectedElement, 'disabled');
					window.scroll(0, 0);
					const processSubmitButtonText = elements.html(selectedElement);

					if (
						elements.hasAttribute(selectedElement, 'item_function') ||
						processSubmit
					) {
						api.setRequestParameters({
							processing: true
						});
						elements.setAttribute(selectedElement, 'disabled', 'disabled');

						if (!elements.hasClass(selectedElement, 'icon')) {
							elements.html(selectedElement, 'Processing');
						}
					}

					processProcess(selectedElement, processSubmit);
				},
				type: 'click'
			});
		});
	});
};
var processRemove = function(processElement) {
	let itemListName = camelCaseString(elements.getAttribute(processElement, 'item_list_name'));
	let itemListParameters = apiRequestParameters[itemListName];

	if (confirm('Are you sure you want to delete the selected ' + snakeCaseString(camelCaseString(itemListParameters.from), ' ') + '?')) {
		api.setRequestParameters({
			action: 'remove',
			from: itemListParameters.from,
			itemListName: snakeCaseString(itemListName, '_'),
			url: itemListParameters.url
		});
		api.sendRequest(function(response) {
			let itemListItems = {};
			var mergeRequestParameters = {
				items: {}
			};
			mergeRequestParameters[itemListName] = itemListParameters;
			mergeRequestParameters[itemListName].page = 1;
			mergeRequestParameters.items[itemListName] = {
				action: 'fetch',
				data: [],
				from: itemListParameters.from
			};
			api.setRequestParameters(mergeRequestParameters, true);
			processItemList(itemListName, function() {
				delete apiRequestParameters.processing;
				elements.html(itemListParameters.selector + ' .message-container.' + snakeCaseString(itemListName, '-'), response.message.html);
			});
		});
	} else {
		delete apiRequestParameters.processing;
	}
};
var processSearch = function() {
	let itemListName = camelCaseString(elements.getAttribute('.process-container[process="search"]', 'search'));
	let itemListParameters = apiRequestParameters[itemListName];

	if (
		apiRequestParameters.processing &&
		typeof itemListParameters !== 'undefined'
	) {
		api.setRequestParameters({
			action: 'search',
			encodeItemList: true,
			url: itemListParameters.url
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			elements.html('.search-configuration .message-container.search', response.message.html);

			if (response.message.status === 'success') {
				delete apiRequestParameters.items[itemListName].data;

				if (response.search) {
					var mergeRequestParameters = {
						data: {},
						search: {}
					};
					mergeRequestParameters.search[itemListName] = response.search;
					api.setRequestParameters(mergeRequestParameters, true);
				}

				closeProcesses();
				processItemList(itemListName);
			}
		});
	}
};
var processSettings = function(processElement, processSubmit) {
	api.setRequestParameters({
		action: 'settings',
		url: '/endpoint/main'
	});
	api.sendRequest(function(response) {
		delete apiRequestParameters.processing;
		elements.get('.account-password').value = response.user.password;
		elements.get('.account-whitelisted-ips').value = response.user.whitelistedIps;

		if (processSubmit) {
			elements.get('.account-password').value = apiRequestParameters.data.accountPassword || response.user.password;
			elements.get('.account-whitelisted-ips').value = response.data.whitelistedIps;
			elements.html('.password.message-container', response.message.html);
			elements.setAttribute('.account-password', 'value', response.user.password);
		}
	});
};
const processWindowEvents = function(event) {
	if (typeof event === 'undefined') {
		return false;
	}

	if (
		typeof windowEvents[event] === 'object' &&
		windowEvents[event]
	) {
		for (let windowEventKey in windowEvents[event]) {
			let windowEvent = windowEvents[event][windowEventKey];
			windowEvent();
		}
	}
};
const range = function(low, high, step) {
	let response = [];
	high = +high;
	low = +low;
	step = step || 1;

	if (low < high) {
		while (low <= high) {
			response.push(low);
			low += step;
		}
	} else {
		while (low >= high) {
			response.push(low);
			low -= step;
		}
	}

	return response;
};
const render = function(callback) {
	setTimeout(function() {
		callback();
	}, 0);
};
const repeat = function(count, pattern) {
	let response = '';
	let index = 0;

	while (count > index) {
		index++;
		response += pattern;
	}

	return response;
};
const selectAllElements = function(selector, callback) {
	let response = [];
	let nodeList = document.querySelectorAll(selector);

	if (nodeList.length) {
		response = Object.entries(nodeList);
	}

	if (typeof callback === 'function') {
		for (let selectedElementKey in response) {
			callback(selectedElementKey, response[selectedElementKey][1]);
		}
	}

	return response;
};
const snakeCaseString = function(string, separator) {
	let stringParts = string.split('');

	for (let stringPartKey in stringParts) {
		let stringPart = stringParts[stringPartKey];

		if (stringPartKey > 0) {
			let lowerCaseStringPart = stringPart.toLowerCase();

			if (
				!parseInt(stringPart) &&
				lowerCaseStringPart !== stringPart
			) {
				stringParts[stringPartKey] = separator + lowerCaseStringPart;
			}
		}
	}

	return stringParts.join('');
};
var windowEvents = {
	resize: [],
	scroll: []
};

if (
	(
		typeof Element.prototype.addEventListener === 'undefined' ||
		typeof Element.prototype.removeEventListener === 'undefined'
	) &&
	(this.attachEvent && this.detachEvent)
) {
	Element.prototype.addEventListener = function (event, callback) {
		event = 'on' + event;
		return this.attachEvent(event, callback);
	};
	Element.prototype.removeEventListener = function (event, callback) {
		event = 'on' + event;
		return this.detachEvent(event, callback);
	};
}

if (!Object.entries) {
	Object.entries = function(object) {
		if (typeof object !== 'object') {
			return false;
		}

		let response = [];

		for (let objectKey in object) {
			if (object.hasOwnProperty(objectKey)) {
				response.push([objectKey, object[objectKey]]);
			}
		}

		return response;
	};
}

onDocumentReady(function() {
	if (document.querySelector('.hidden.settings')) {
		const settings = JSON.parse(document.querySelector('.hidden.settings').innerHTML);

		if (navigator.cookieEnabled) {
			if (document.cookie.indexOf('sessionId=') === -1) {
				document.cookie = 'sessionId=_' + settings.uniqudeId + '; domain=' + settings.baseDomain + '; max-age=' + 100000000 + '; path=/; samesite;';
			}

			let cookies = document.cookie.split(' ');

			for (let cookieKey in cookies) {
				let cookie = cookies[cookieKey];

				if (cookie.charAt(10) === '_') {
					settings.sessionId = cookie.substr(11);
				}
			}
		}

		api.setRequestParameters({
			camelCaseResponseKeys: true,
			settings: settings
		});
	}
});
