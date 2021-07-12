const processProcess = function(processElement, processSubmit) {
	// todo: button processing is stuck after pressing button too fast
	let processName = elements.getAttribute(processElement, 'process');

	if (
		elements.hasClass(processElement, 'close') ||
		(
			!elements.hasAttribute(processElement, 'item_function') &&
			processSubmit === false
		)
	) {
		closeProcesses();
	}

	if (processSubmit === true) {
		let processData = {};
		var mergeRequestParameters = {
			action: processName
		};
		selectAllElements('[process="' + processName + '"] input[name], [process="' + processName + '"] select[name], [process="' + processName + '"] textarea[name]', function(selectedElementKey, selectedElement) {
			processData[camelCaseString(selectedElement.getAttribute('name'))] = selectedElement.value;
		});
		selectAllElements('[process="' + processName + '"] .checkbox[name]', function(selectedElementKey, selectedElement) {
			processData[camelCaseString(selectedElement.getAttribute('name'))] = +selectedElement.getAttribute('checked');
		});
		mergeRequestParameters.data = processData;
		api.setRequestParameters(mergeRequestParameters, true);
	} else {
		openProcess('.process-container[process="' + processName + '"]');
	}

	processName = 'process' + capitalizeString(camelCaseString(processName));

	if (typeof window[processName] === 'function') {
		window[processName](processElement, processSubmit);
	}

	processWindowEvents('resize');
};
onDocumentReady(function() {
	processProcesses();
	window.onresize = function() {
		processWindowEvents('resize');
	};
	window.onscroll = function() {
		processWindowEvents('scroll');
	};
	let processElement = false;
	let processName = false;

	if (elements.get('main').hasAttribute('process')) {
		processElement = elements.get('main');
		processName = 'process' + capitalizeString(elements.get('main').getAttribute('process'));
	}

	if (window.location.hash) {
		let processString = window.location.hash.substr(1).toLowerCase();
		let processStringProcessElement = elements.get('.process-container[process="' + processString + '"]');

		if (processStringProcessElement) {
			closeProcesses();
			openProcess('.process-container[process="' + processString + '"]');
			processStringProcessName = 'process' + capitalizeString(processString);

			if (typeof window[processStringProcessName] === 'function') {
				processElement = processStringProcessElement;
				processName = processStringProcessName;
			}
		}
	}

	if (
		processName &&
		typeof window[processName] === 'function'
	) {
		render(function() {
			onNodeReady([
				'apiRequestParameters',
				'settings'
			], function() {
				window[processName](processElement, false);
			});
		});
	}
});
