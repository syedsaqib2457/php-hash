<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="initial-scale=1, width=device-width" name="viewport">
<title>Overlord</title>
<style type="text/css">
	/*.align-left { float: left !important; }
	.align-right { float: right !important; }
	.clear { clear: both !important; float: none !important; }
	.full-width { width: 100% !important; }
	.hidden { display: none !important; }
	.margin-bottom { margin-bottom: 15px !important; }
	.margin-left { margin-left: 15px !important; }
	.margin-right { margin-right: 15px !important; }
	.margin-top { margin-top: 15px !important; }
	.no-border { border: none !important; }
	.no-margin { margin: 0 !important; }
	.no-margin-left { margin-left: 0 !important; }
	.no-margin-bottom { margin-bottom: 0 !important; }
	.no-margin-right { margin-right: 0 !important; }
	.no-margin-top { margin-top: 0 !important; }
	.no-padding { padding: 0 !important; }
	.no-padding-left { padding-left: 0 !important; }
	.no-padding-bottom { padding-bottom: 0 !important; }
	.no-padding-right { padding-right: 0 !important; }
	.no-padding-top { padding-top: 0 !important; }
	.width-auto { width: auto !important; }
	[disabled] {
		cursor: default !important;
		opacity: 0.6;
	}
	::placeholder {
		color: #bbb;
	}
	a,
	a:active,
	a:focus,
	a:visited {
		color: #000;
		font-weight: 600;
		text-decoration: none;
	}
	a,
	body,
	button,
	div,
	html,
	input,
	label,
	pre,
	select,
	span,
	textarea {
		font-family: helvetica, arial !important;
	}
	body,
	html,
	pre {
		background: #fff;
		color: #777;
		font-size: 13px;
		line-height: 27px;
		margin: 0;
	}
	body {
		cursor: default;
		margin: 91px 15px 115px;
	}
	footer,
	header {
		float: left;
		width: 100%;
	}
	h1 {
		font-size: 22px;
		font-weight: 300;
		line-height: 34px;
		margin: 0 0 14px;
	}
	input,
	textarea {
		background: #fff;
	}
	label {
		display: block;
		line-height: 26px;
		font-weight: 600;
	}
	main {
		display: block;
		min-height: 300px;
		padding: 0;
	}
		main img {
			margin: 10px 0;
			width: 100%;
		}
	p {
		line-height: 21px;
		margin: 0 0 15px 0;
	}
	pre {
		background: #000;
		color: #fff;
		font-size: 12px;
		font-weight: 400;
		line-height: 17px;
		margin: 10px 0 15px 0;
		overflow-x: scroll;
		padding: 15px;
		white-space: pre;
	}
		pre span {
			margin: 0 !important;
		}
			pre span.comment {
				color: #555;
			}
			pre span.request-heading,
			pre span.response-heading {
				border-bottom: 1px solid #222;
				display: block;
				line-height: 17px;
				margin-bottom: 15px !important;
				padding-bottom: 15px;
				width: 100%;
			}
			pre span.response-heading {
				margin-top: 15px !important;
			}
			pre span.value {
				color: #888;
			}
	select {
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
		background: url('../png/icon-select-arrow.png') #fff right center no-repeat;
		background-size: auto 16px !important;
		border: 1px solid #aaa;
		border-radius: 0;
		color: #777;
		cursor: pointer;
		display: block;
		font-size: 13px;
		height: 40px;
		line-height: 38px;
		margin: 0;
		outline: none;
		padding: 0 34px 0 10px;
		width: 100%;
	}
		select::-ms-expand {
			clear: both;
			display: flex;
			margin-top: 100px;
			position: absolute;
			width: 0;
		}
		select:hover {
			border-color: #000;
			color: #000;
		}
	strong {
		font-weight: 600;
	}
	.alternate-button,
	.main-button {
		border: none;
		cursor: pointer;
		display: inline-block;
		font-size: 11px;
		font-weight: 600;
		line-height: 18px;
		letter-spacing: 0.2px;
		margin: 0 14px 4px 0;
		outline: none;
		padding: 12px 24px;
		text-transform: uppercase;
	}
	.alternate-button {
		background: #fff;
		border: 1px solid #aaa;
		color: #000;
		padding: 11px 24px;
	}
		.alternate-button:hover {
			border-color: #000;
		}
	.checkbox {
		background: #fff !important;
		border: 1px solid #aaa !important;
		box-shadow: none !important;
		cursor: pointer !important;
		display: block !important;
		height: 14px !important;
		padding: 0 !important;
		position: relative;
		width: 14px !important;
		z-index: 2 !important;
	}
		.checkbox:hover {
			border-color: #000 !important;
		}
		.checkbox[checked="1"] {
			border-color: #000 !important;
			border-width: 4px !important;
			height: 8px !important;
			width: 8px !important;
		}
	.checkbox-container {
		float: left;
		margin: 10px 0;
	}
		.checkbox-container .checkbox {
			float: left;
		}
		.checkbox-container .custom-checkbox-label {
			box-sizing: border-box;
			color: #000;
			cursor: pointer;
			float: none;
			font-size: 13px;
			line-height: 18px;
			padding-left: 28px;
			padding-top: 0;
		}
	.clear-search {
		display: block;
		float: left;
		line-height: 16px;
		margin-bottom: 0px;
		margin-top: 6px;
	}
	.container {
		margin: 0 auto;
		max-width: 500px;
		width: 100%;
	}
	.content-container {
		border-top: 1px solid #ddd;
		padding-top: 19px;
	}
		.content-container a {
			margin-right: 15px;
		}
		.content-container h2 {
			border-bottom: 1px solid #ddd;
			clear: none;
			float: left;
			font-size: 16px;
			font-weight: 300;
			line-height: 24px;
			margin-bottom: 18px;
			margin-top: 10px;
			padding: 0 0 16px;
			width: 100%;
		}
		.content-container img {
			background: #fff;
			border: 4px solid #000;
			box-sizing: border-box;
			padding: 20px;
		}
		.content-container span {
			margin-right: 15px;
		}
	.field-group .add {
		float: left;
		height: 38px;
		line-height: 38px;
		margin-left: -1px !important;
		padding: 0 18px;
		position: relative;
		right: auto;
		top: auto;
		z-index: 4;
	}
	.field-group .number {
		width: 100px;
	}
	.field-group {
		float: left;
		margin-bottom: 10px;
		margin-top: 10px;
		position: relative;
		width: 100%;
	}
		.field-group input,
		.field-group select,
		.field-group span,
		.field-group textarea {
			float: left;
			margin: 0 0 0 -1px;
			max-width: 100%;
			position: relative;
			width: auto;
		}
		.field-group input:focus,
		.field-group input:hover,
		.field-group select:focus,
		.field-group select:hover,
		.field-group textarea:focus,
		.field-group textarea:hover {
			z-index: 1;
		}
		.field-group span {
			cursor: default;
			font-weight: 600;
			line-height: 38px !important;
			padding: 0 13px;
			z-index: 2;
		}
			.field-group .table span {
				line-height: 20px !important;
			}
		.field-group select {
			margin-bottom: 10px;
		}
		.field-group .add {
			float: left;
			height: 38px;
			line-height: 38px;
			margin-left: -1px !important;
			padding: 0 18px;
			position: relative;
			right: auto;
			top: auto;
			z-index: 4;
		}
		.field-group .number {
			width: 100px;
		}
		.field-group.interactive-field-group .button {
			background: #000;
			border: none;
			color: #fff;
			cursor: pointer;
			display: block;
			height: 28px;
			font-size: 11px;
			font-weight: 600;
			line-height: 1px;
			margin: 0 !important;
			outline: none;
			padding: 0 15px;
			position: absolute;
			right: 6px;
			text-transform: uppercase;
			top: 6px;
			z-index: 3;
		}
		.field-group .display {
			background: none;
			font-size: 14px;
			font-weight: 400;
		}
		.field-group .discount-code-field {
			padding-right: 142px;
		}
	.field-group-container .field-group {
		width: auto;
	}
	.field-group-container span.icon {
		margin-left: 10px;
		margin-top: 10px;
	}
	.heading {
		margin-bottom: 15px;
	}
	.icon {
		color: #fff !important;
		cursor: pointer;
		display: block;
		height: 16px !important;
		float: left;
		font-size: 10px;
		margin: 0 0 8px 8px !important;
		width: 16px !important;
	}
	.icon.edit {
		background: url('../png/icon-edit.png') transparent center no-repeat;
		margin-top: 0;
	}
	.icon.next {
		background: url('../png/icon-next.png') transparent center no-repeat !important;
	}
	.icon.previous {
		background: url('../png/icon-previous.png') transparent center no-repeat !important;
	}
		.icon.next,
		.icon.previous {
			height: 16px !important;
			margin: 0 0 5px 10px;
			padding: 0 !important;
			width: 11px !important;
		}
		.icon.next[page="0"],
		.icon.previous[page="0"],
		.icon.next[page="0"]:hover,
		.icon.previous[page="0"]:hover {
			opacity: 0.2;
			cursor: default !important;
		}
		a.icon.previous {
			margin: 5px 13px 11px 0 !important;
		}
	.icon.remove {
		background: url('../png/icon-remove.png') center no-repeat;
	}
	.icon[process="activate"] {
		background: url('../png/icon-activate.png') center no-repeat;
	}
	.icon[process="authenticate"] {
		background: url('../png/icon-authenticate.png') center no-repeat;
	}
	.icon[process="deactivate"] {
		background: url('../png/icon-deactivate.png') center no-repeat;
	}
	.icon[process="download"] {
		background: url('../png/icon-download.png') center no-repeat;
	}
	.icon[process="limit"] {
		background: url('../png/icon-limit.png') center no-repeat;
	}
	.icon[process="search"] {
		background: url('../png/icon-search.png') center no-repeat;
	}
	.icon[process="server_nameserver_processes"] {
		background: url('../png/icon-server-nameserver-processes.png') center no-repeat;
	}
	.icon[process="server_proxy_processes"] {
		background: url('../png/icon-server-proxy-processes.png') center no-repeat;
	}
	.icon.edit,
	.icon.next,
	.icon.previous,
	.icon.remove,
	.icon[process] {
		background-size: auto 16px !important;
	}
	.item-configuration-container .checked-container {
		display: block;
	}
	.item-configuration-container .item-controls-container .item-header {
		border-bottom: 1px solid #ddd;
	}
		.item-configuration-container .item-controls-container .item-header .item-controls-heading-container {
			display: block;
			float: left;
			width: 100%;
		}
			.item-configuration-container .item-controls-container .item-header .item-controls-heading-container label {
				margin-bottom: 8px;
				margin-top: -4px;
			}
			.item-configuration-container .item-controls-container .item-header .pagination-container .pagination .item-controls.results,
			.item-configuration-container .item-controls-container .item-header .pagination-container .pagination .item-controls.results span {
				line-height: 16px !important;
			}
		.item-configuration-container .item-controls-container .item-header .alternate-button,
		.item-configuration-container .item-controls-container .item-header .main-button {
			margin: 0 0 7px 0;
			top: 22px;
		}
		.item-configuration-container .item-controls-container .item-header .additional-item-controls {
			border-top: 1px solid #ddd;
			float: left;
			margin-top: 16px;
			padding-top: 10px;
			width: 100%;
		}
			.item-configuration-container .item-controls-container .item-header .additional-item-controls .button {
				margin-top: 5px;
			}
				.item-configuration-container .item-controls-container .item-header .additional-item-controls .form .button {
					margin-bottom: 1px;
				}
				.item-configuration-container .item-controls-container .item-header .additional-item-controls .form .button,
				.item-configuration-container .editing .button {
					margin-top: 7px;
				}
			.item-configuration-container .item-controls-container .item-header .additional-item-controls .form {
				margin-bottom: 0px;
				padding-left: 32px;
			}
		.item-configuration-container .item-controls-container.scrolling .item-header {
			border-top: none !important;
		}
	.item-configuration-container .item-body {
		border-top: none;
		padding-top: 85px;
	}
	.item-controls .checkbox {
		margin-bottom: 11px;
		margin-right: 8px;
	}
	.item-configuration-container .item-configuration-container .checkbox {
		background: #fff;
	}
	.item-configuration-container .item-configuration-container .icon.next,
	.item-configuration-container .item-configuration-container .icon.previous {
		cursor: pointer !important;
		margin-left: 5px !important;
		margin-top: 0 !important;
	}
		.item-configuration-container .item-configuration-container .icon.next[page="0"],
		.item-configuration-container .item-configuration-container .icon.previous[page="0"] {
			cursor: default !important;
		}
	.item-configuration-container .item-configuration-container .item-body,
	.item-configuration-container .item-configuration-container .item-header {
		background: none;
	}
	.item-configuration-container .item-configuration-container .item-body {
		padding: 0 0 35px 0;
	}
	.item-configuration-container .item-configuration-container .item-header {
		border-bottom: 1px solid #ddd;
		border-top: 1px solid #ddd;
		margin-top: 1px;
		padding: 18px 0 12px !important;
	}
		.item-configuration-container .item-configuration-container .item-header span {
			line-height: 20px !important;
		}
			.item-configuration-container .item-configuration-container .item-header span.action {
				margin-right: 0;
			}
	.item-configuration-container .item-configuration-container span {
		background: none;
		border: none;
		float: none;
		font-weight: 400;
		line-height: 28px !important;
		margin-left: 0 !important;
		padding: 0;
	}
		.item-configuration-container .item-configuration-container span.action {
			cursor: pointer;
			font-weight: 600;
		}
	.item-container {
		box-sizing: border-box;
		margin-bottom: 5px;
		margin-top: 15px;
		position: relative;
		width: 100%;
	}
		.item-container input,
		.item-container textarea {
			border: 1px solid #aaa;
			box-sizing: border-box;
			color: #777;
			display: block;
			float: left;
			font-size: 13px;
			margin-bottom: 10px;
			outline: none;
			width: 100%;
		}
			.item-container input:hover,
			.item-container textarea:hover,
			.item-container input:active,
			.item-container input:focus,
			.item-container input:visited,
			.item-container textarea:active,
			.item-container textarea:focus,
			.item-container textarea:visited {
				border-color: #000;
			}
			.item-container input {
				height: 40px;
				line-height: 38px;
				padding: 0 12px;
			}
			.item-container textarea {
				height: 140px;
				line-height: 18px;
				padding: 10px 12px;
			}
		.item-container.item-button {
			border: 1px solid #ddd;
			margin-top: 15px !important;
			padding: 15px !important;
		}
			.item-container.item-button .item .checkbox-container {
				height: 50px;
				left: 0;
				margin: 0;
				position: absolute;
				top: 0;
				width: 50px;
				z-index: 2;
			}
				.item-container.item-button .item .checkbox-container .checkbox {
					left: 15px;
					margin: 0;
					position: absolute;
					top: 15px;
				}
			.item-container.item-button .item .item-checkbox {
				padding-left: 31px;
				padding-top: 0;
			}
			.item-container.item-button .item .label {
				text-transform: capitalize;
			}
			.item-container.item-button .field-group {
				margin: 0 0 8px 0;
			}
			.item-container.item-button:hover {
				border-color: #000;
			}
			.item-container.item-button p {
				line-height: 14px;
				margin-bottom: 10px;
			}
		.item-container.item-button-selectable {
			position: relative;
		}
			.item-container.item-button-selectable:hover {
				border-color: #000;
			}
			.item-container.item-button-selectable .checkbox {
				left: 15px;
				position: absolute;
				top: 15px;
			}
			.item-container.item-button-selectable p {
				cursor: default;
				line-height: 13px;
				margin-bottom: 8px;
				margin-left: -1px;
			}
		.item-container .item {
			cursor: default;
			display: block;
			margin-bottom: 0;
		}
			.item-container .item-body,
			.item-container .item-footer,
			.item-container .item-header {
				background: #fff;
				padding: 21px 0 16px;
			}
			.item-container .item-body {
				padding: 0;
			}
			.item-container .item-header {
				border-top: 1px solid #ddd;
			}
				.item-container .item-header .field-group span {
					line-height: 27px;
				}
				.item-container .item-header .item-controls {
					line-height: 16px;
				}
					.item-container .item-header .item-controls .item-action,
					.item-container .item-header .message {
						display: block;
						float: left;
						margin-bottom: 0 !important;
						margin-top: 15px;
					}
					.item-container .item-header .item-controls .item-action {
						line-height: 16px;
						margin-top: 7px;
					}
					.item-container .item-header .message {
						border-top: 1px solid #ddd;
						padding-top: 15px;
						width: 100%;
					}
					.item-container .item-header .item-controls .total {
						font-weight: 600;
					}
		.item-container .item-link-container {
			height: 100%;
			left: 0px;
			position: absolute;
			top: 0px;
			width: 100%;
		}
			.item-container .item-link-container a {
				display: inherit;
				height: inherit;
				position: relative;
				width: inherit;
				z-index: 1;
			}
	.item-list-container .item-configuration-container {
		margin: 0;
	}
	.item-list .item-container .item-container {
		margin: 0;
	}
		.item-list .item-container .item-container .item-body {
			padding-bottom: 0;
		}
		.item-list .item-container .item-container .item-header {
			padding: 0;
		}
	.items-container {
		padding-top: 20px;
	}
	.label,
	.message {
		color: #000;
		display: inline-block;
		font-weight: 600;
		margin: 0 8px 0px 0;
		padding: 0;
		position: relative;
		width: auto;
		z-index: 1;
	}
	.main-button {
		background: #000;
		color: #fff !important;
	}
	.message {
		display: block;
		margin-bottom: 10px;
		margin-right: 0;
		word-break: break-word;
	}
		.message a {
			border-bottom: 1px dashed;
		}
			.message a:hover {
				border: none;
			}
	.navigation {
		margin-bottom: -10px;
		padding: 0;
		width: 100%;
	}
		.navigation p {
			padding: 18px 0;
		}
		.navigation nav ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}
			.navigation nav ul li {
				float: left;
			}
				.navigation nav ul li a {
					display: block;
					line-height: 34px;
					margin-left: 15px;
				}
					.navigation nav ul li:first-child a {
						margin-left: 0;
					}
	.process-container,
	.process-container .process,
	.process-container .process-overlay {
		bottom: 0;
		left: 0;
		margin: auto;
		position: relative;
		right: 0;
		top: 0;
	}
		.process-container {
			z-index: 1;
		}
			.process-container .process {
				max-width: 500px;
				padding-top: 4px;
				z-index: 3;
			}
				.process-container .item-container {
					margin: 0;
					padding: 0 0 88px;
				}
					.process-container .item-list .item-container {
						padding: 0;
					}
				.process-container .process p,
				.process-container .process pre {
					margin-bottom: 10px !important;
				}
			.process-container .process-overlay {
				z-index: 2;
			}
				.process-container .process .item-body {
					padding: 0;
				}
					.process-container .process .item-body .process-button {
						display: block;
						line-height: 16px;
						margin: 6px 0 8px 0;
						width: 100%;
					}
						.process-container .process .item-body .process-button.icon {
							width: 18px !important;
						}
				.process-container .process .item-footer {
					padding-top: 8px;
					position: relative;
					float: left;
				}
					.process-container .process .item-footer a.button {
						float: left;
						margin-bottom: 15px;
					}
	.process .checkbox-option-container {
		box-sizing: border-box;
		clear: left;
		float: left;
		margin-bottom: 18px;
		max-width: 100%;
		padding-left: 32px;
	}
		.process .checkbox-option-container .field-group {
			margin-bottom: 8px;
		}
		.process .checkbox-option-container .checkbox-container {
			box-sizing: border-box;
			clear: left;
		}
	.progress-container {
		background: #ddd;
		display: block;
		height: 4px;
		position: relative;
		width: 100%;
	}
		.progress {
			background: #000;
			display: block;
			height: 4px;
			left: 0;
			position: absolute;
			top: 0;
		}
	.scrollable {
		left: 0;
		position: absolute;
		right: 0;
		top: -1px;
		z-index: 1;
	}
	.scrollable[scrolling="1"] {
		margin: auto;
		position: fixed;
	}
	.scrollable[scrolled_to_the_bottom="1"] {
		bottom: 0;
		left: auto;
		position: absolute;
		right: auto !important;
		top: auto;
	}
	.scrollable[scrolling="1"],
	.scrollable[scrolled_to_the_bottom="1"] {
		z-index: 4;
	}
	.section {
		margin-top: 72px;
	}
	.table {
		border: none;
		border-spacing: 0;
		margin-bottom: 10px;
		text-align: left;
		width: 100%;
	}
		.table tr {
			border-bottom: 1px solid #ddd;
			float: left;
			position: relative;
			width: 100%;
		}
			.table tr:hover span.table-actions {
				display: block;
			}
			.table tr td {
				padding: 5px;
				width: 100%;
			}
				.table tr td.checkbox-container {
					float: none;
					padding: 0 10px 0 0;
					width: 1px;
				}
				.table tr td.editing {
					border-left: 1px solid #ddd;
					padding: 10px 16px 16px;
				}
				.table tr td input.custom {
					width: 100%;
				}
				.table tr td .table-text {
					float: left !important;
					width: 100%;
				}
					.table tr td .table-text a {
						float: left;
						word-break: break-word;
						z-index: 1;
					}
				.table tr td .table-actions {
					display: none;
					margin: 5px auto;
					position: absolute;
					right: 0;
				}
					.table tr td .table-actions span.icon {
						margin-left: 2px;
					}
		.table thead tr {
			border: none !important;
		}
			.table thead tr th {
				font-size: 13px;
				font-weight: 400;
				line-height: 34px;
				padding: 0 12px;
			}
			.table thead tr th:first-child {
				padding: 0;
				width: 20px;
			}
		.table tbody tr:first-child {
			border-top: 1px solid #ddd;
		}
	.tooltip:hover.tooltip:after {
		background-color: #fff;
		color: #000;
		content: attr(item_title);
		display: block;
		font-size: 13px;
		font-weight: 600;
		line-height: 18px;
		margin-left: -1px;
		padding: 5px 9px 5px;
		position: absolute;
		width: auto;
		z-index: 9;
	}
		.tooltip:hover.tooltip-bottom:after {
			margin-left: -10px;
			margin-top: 22px;
		}
	*/
</style>
</head>
<body>
<div class="hidden" process="configure">
	<div class="process-container">
		<p class="message password"></p>
		<label>Account Password</label>
		<input class="account-password" name="authentication_password" type="password">
		<label>Endpoint Whitelist</label>
		<textarea class="authentication-whitelist" name="authentication_whitelist" placeholder="<?php echo "127.0.0.1\n127.0.0.2\netc..." ?>" type="text"></textarea>
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="configure">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="login">
	<div class="process-container">
		<p class="login message"></p>
		<label>Password</label>
		<input class="password" name="password" type="password">
		<div class="clear"></div>
		<span class="button submit" process="login">Log In</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="node_activate">
	<div class="process-container">
		<p class="message node-activate"></p>
		<div class="clear"></div>
		<span class="button close">Close</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="node_add">
	<div class="process-container">
		<p class="message node-add"></p>
		<label>External IPv4</label>
		<input name="external_ip_version_4" type="text">
		<label>Internal IPv4</label>
		<input name="internal_ip_version_4" type="text">
		<label>External IPv6</label>
		<input name="external_ip_version_6" type="text">
		<label>Internal IPv6</label>
		<input name="internal_ip_version_6" type="text">
		<div class="checkbox-container" toggle="enable_binding_to_existing_node">
			<span checked="0"></span>
			<label>Enable binding to existing node</label>
		</div>
		<div class="container hidden" name="enable_binding_to_existing_node">
			<label>Existing Node External IP Address or Node ID</label>
			<input name="node_id" type="text">
		</div>
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="node_add">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="node_deactivate">
	<div class="process-container">
		<p class="message node-deactivate"></p>
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="node_deactivate">Confirm Deactivation</span>
	</div>
	<div class="process-overlay"></div>
</div>
// todo: change keys to match database, don't require enable_ field values in validation for API consistency
<div class="hidden" process="node_edit">
	<div class="process-container">
		<p class="message node-edit"></p>
		<label>Status</label>
		<div name="node_status"></div>
		<label>External IPv4</label>
		<input name="external_ip_version_4" type="text">
		<label>Internal IPv4</label>
		<input name="internal_ip_version_4" type="text">
		<label>External IPv6</label>
		<input name="external_ip_version_6" type="text">
		<label>Internal IPv6</label>
		<input name="internal_ip_version_6" type="text">
		<div class="checkbox-container" toggle="enable_nameserver_processes">
			<span checked="0"></span>
			<label>Enable nameserver processes</label>
			<!-- Add note that process / port settings will affect all nodes on [node_id] if node_id exists. users can create 1 node per machine or eventually create 1 vm per node -->
		</div>
		<div class="container hidden" name="enable_nameserver_processes">
			<div name="nameserver_process_details">
				<label>Nameserver Processes</label>
				<!-- list_statistics_for_process_usage -->
				<label>Nameserver Process Ports</label>
				<div name="nameserver_process_ports">
					<!-- list_open_process_ports -->
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_opening_custom_nameserver_process_ports">
				<span checked="0"></span>
				<label>Enable opening custom nameserver process ports</label>
			</div>
			<div class="container hidden" name="enable_opening_custom_nameserver_process_ports">
				<div class="list" from="ports"></div>
				<div class="checkbox-container" toggle="enable_only_allowing_custom_nameserver_process_ports">
					<span checked="0"></span>
					<label>Enable only allowing custom nameserver process ports</label>
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_closing_custom_nameserver_process_ports">
				<span checked="0"></span>
				<label>Enable closing custom nameserver process ports</label>
			</div>
			<div class="container hidden" name="enable_closing_custom_nameserver_process_ports">
				<div class="list" from="ports"></div>
			</div>
			<label>Nameserver Users</label>
			<div class="list" from="users"></div>
			<div class="checkbox-container" toggle="enable_nameserver_on_external_ip_version_4">
				<span checked="0"></span>
				<label>Enable nameserver on external IPv4</label>
			</div>
			<div class="checkbox-container" toggle="enable_nameserver_on_external_ip_version_6">
				<span checked="0"></span>
				<label>Enable nameserver on external IPv6</label>
			</div>
		</div>
		<div class="checkbox-container" toggle="enable_http_proxy_processes">
			<span checked="0"></span>
			<label>Enable HTTP proxy processes</label>
		</div>
		<div class="container hidden" name="enable_http_proxy_processes">
			<div name="http_proxy_process_details">
				<label>HTTP Proxy Processes</label>
				<!-- list_statistics_for_process_usage -->
				<label>HTTP Proxy Process Ports</label>
				<div name="http_proxy_process_ports">
					<!-- list_open_process_ports -->
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_opening_custom_http_proxy_process_ports">
				<span checked="0"></span>
				<label>Enable opening custom HTTP proxy process ports</label>
			</div>
			<div class="container hidden" name="enable_opening_custom_http_proxy_process_ports">
				<div class="list" from="ports"></div>
				<div class="checkbox-container" toggle="enable_only_allowing_custom_http_proxy_process_ports">
					<span checked="0"></span>
					<label>Enable only allowing custom HTTP proxy process ports</label>
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_closing_custom_http_proxy_process_ports">
				<span checked="0"></span>
				<label>Enable closing custom HTTP proxy process ports</label>
			</div>
			<div class="container hidden" name="enable_closing_custom_http_proxy_process_ports">
				<div class="list" from="ports"></div>
			</div>
			<label>HTTP Proxy Users</label>
			<div class="list" from="users"></div>
		</div>
		<div class="checkbox-container" toggle="enable_socks_proxy_processes">
			<span checked="0"></span>
			<label>Enable SOCKS proxy processes</label>
		</div>
		<div class="container hidden" name="enable_socks_proxy_processes">
			<div name="socks_proxy_process_details">
				<label>SOCKS Proxy Processes</label>
				<!-- list_statistics_for_process_usage -->
				<label>SOCKS Proxy Process Ports</label>
				<div name="socks_proxy_process_ports">
					<!-- list_open_process_ports -->
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_opening_custom_socks_proxy_process_ports">
				<span checked="0"></span>
				<label>Enable opening custom SOCKS proxy process ports</label>
			</div>
			<div class="container hidden" name="enable_opening_custom_socks_proxy_process_ports">
				<div class="list" from="ports"></div>
				<div class="checkbox-container" toggle="enable_only_allowing_custom_socks_proxy_process_ports">
					<span checked="0"></span>
					<label>Enable only allowing custom SOCKS proxy process ports</label>
				</div>
			</div>
			<div class="checkbox-container" toggle="enable_closing_custom_socks_proxy_process_ports">
				<span checked="0"></span>
				<label>Enable closing custom SOCKS proxy process ports</label>
			</div>
			<div class="container hidden" name="enable_closing_custom_socks_proxy_process_ports">
				<div class="list" from="ports"></div>
			</div>
			<label>SOCKS Proxy Users</label>
			<div class="list" from="users"></div>
		</div>
		<div class="checkbox-container" toggle="enable_reverse_proxy_forwarding">
			<span checked="0"></span>
			<label>Enable reverse proxy forwarding</label>
		</div>
		<div class="container hidden" name="enable_reverse_proxy_forwarding">
			<label>IPv4 Destination IP Address or External Hostname</label>
			<input name="destination_address_version_4" type="text">
			<label>IPv4 Destination Port</label>
			<input name="destination_port_version_4" type="text">
			<label>IPv6 Destination IP Address or External Hostname</label>
			<input name="destination_address_version_6" type="text">
			<label>IPv6 Destination Port</label>
			<input name="destination_port_version_6" type="text">
		</div>
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="node_add">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_destination_add">
	<div class="process-container">
		<p class="message request-destination-add"></p>
		<label>Destination Address</label>
		<input name="destination" type="text">
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="request_destination_add">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_destination_edit">
	<div class="process-container">
		<p class="message request-destination-edit"></p>
		<label>Destination Address</label>
		<input name="destination" type="text">
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="request_destination_edit">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_destinations">
	<div class="process-container">
		<div class="list" from="request_destinations"></div>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_limit_rules_add">
	<div class="process-container">
		<p class="message request-limit-rules-add"></p>
		<label>Interval in Minutes</label>
		<input name="request_interval_minutes" type="text">
		<label>Maximum Requests Allowed During Interval</label>
		<input name="request_maximum" type="text">
		<label>Interval in Minutes to Limit Requests After Reaching Maximum Allowed</label>
		<input name="request_limit_interval_minutes" type="text">
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="request_limit_rules_add">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_limit_rules_edit">
	<div class="process-container">
		<p class="message request-limit-rules-edit"></p>
		<label>Interval in Minutes</label>
		<input name="request_interval_minutes" type="text">
		<label>Maximum Requests Allowed During Interval</label>
		<input name="request_maximum" type="text">
		<label>Interval in Minutes to Limit Requests After Reaching Maximum Allowed</label>
		<input name="request_limit_interval_minutes" type="text">
		<div class="clear"></div>
		<span class="button close:>Close</span>
		<span class="button submit" process="request_limit_rules_edit">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="request_limit_rules">
	<div class="process-container">
		<div class="list" from="request_limit_rules"></div>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="user_add">
	<div class="process-container">
		<p class="message user-add"></p>
		<label>Authentication Username</label>
		<input name="authentication_username" type="text">
		<label>Authentication Password</label>
		<input name="authentication_password" type="text">
		<label>Authentication Whitelist</label>
		<textarea name="authentication_whitelist"></textarea>
		<div class="checkbox-container" toggle="status_allowing_request_logs">
			<span checked="1"></span>
			<label>Enable request logs</label>
		</div>
		<div class="container" name="enable_request_logs">
			<div class="checkbox-container" toggle="request_limit_rules">
				<span checked="0"></span>
				<label>Enable request limit rules for specific destinations</label>
			</div>
			<div class="container" name="request_limit_rules">
				<label>Request Destinations</label>
				<div class="list" from="request_destinations"></div>
				<label>Request Limit Rules</label>
				<div class="list" from="request_limit_rules"></div>
				<div class="checkbox-container" toggle="status_allowing_requests_destinations_only">
					<span checked="0"></span>
					<label>Enable only allowing requests to specific destinations</label>
				</div>
			</div>
		</div>
		<div class="checkbox-container" toggle="temporary_authentication">
			<span checked="0"></span>
			<label>Enable temporary authentication</label>
		</div>
		<div class="container" name="temporary_authentication">
			<label>Interval in Minutes Before Authentication Expires</label>
			<input name="authentication_interval_minutes" type="text">
		</div>
		<label>Tag</label>
		<input name="tag" type="text">
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="user_add">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="user_edit">
	<div class="process-container">
		<p class="message user-edit"></p>
		<label>Authentication Username</label>
		<input name="authentication_username" type="text">
		<label>Authentication Password</label>
		<input name="authentication_password" type="text">
		<label>Authentication Whitelist</label>
		<textarea name="authentication_whitelist"></textarea>
		<div class="checkbox-container" toggle="status_allowing_request_logs">
			<span checked="1"></span>
			<label>Enable request logs</label>
		</div>
		<div class="container" name="status_allowing_request_logs">
			<div class="checkbox-container" toggle="request_limit_rules">
				<span checked="0"></span>
				<label>Enable request limit rules for specific destinations</label>
			</div>
			<div class="container" name="request_limit_rules">
				<label>Request Destinations</label>
				<div class="list" from="request_destinations"></div>
				<label>Request Limit Rules</label>
				<div class="list" from="request_limit_rules"></div>
				<div class="checkbox-container" toggle="status_allowing_request_destinations_only">
					<span checked="0"></span>
					<label>Enable only allowing requests to specific destinations</label>
				</div>
			</div>
		</div>
		<div class="checkbox-container" toggle="temporary_authentication">
			<span checked="0"></span>
			<label>Enable temporary authentication</label>
		</div>
		<div class="container" name="temporary_authentication">
			<label>Interval in Minutes Before Authentication Expires</label>
			<input name="authentication_interval_minutes" type="text">
		</div>
		<label>Tag</label>
		<input name="tag" type="text">
		<div class="clear"></div>
		<span class="button close">Close</span>
		<span class="button submit" process="user_edit">Save Changes</span>
	</div>
	<div class="process-overlay"></div>
</div>
<div class="hidden" process="users">
	<div class="process-container">
		<div class="list" from="users"></div>
	</div>
	<div class="process-overlay"></div>
</div>
<main process="nodes">
	<div class="process-container">
		<div class="list" from="nodes"></div>
	</div>
</main>
<div class="hidden settings">{"baseDomain":"<?php echo $configuration->settings['base_domain']; ?>","uniqueId":"<?php echo sha1($configuration->settings['source_ip'] . uniqid()) . md5(time() . uniqid()); ?>"}</div>
<script type="text/javascript">
	// todo: use response.data.length instead of response.count after refactor (count value not included in fetch)
	// todo: refactor code below for nodes (previously default.js)
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

				callback(response);
				processWindowEvents('resize');
			};
		}
	};
	var apiRequestParameters = {
		data: {}
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
		elements.removeClass('main', 'hidden');
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
	const processList = function(listName, callback) {
		let listParameters = apiRequestParameters[listName];

		if (apiRequestParameters[listName].initial === true) {
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
				(typeof listParameters.options === 'object') &&
				listParameters.options
			) {
				elementContent += '<div class="align-left hidden item-control-button-container item-controls selectable-item-controls">';

				for (let optionKey in listParameters.options) {
					let option = listParameters.options[optionKey];
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
			elements.html(listParameters.selector, elementContent);
			Object.defineProperty(listParameters, 'listName', {
				configurable: true,
				enumerable: true,
				value: listName,
				writable: false
			});
			Object.defineProperty(listParameters, 'listContentSelector', {
				configurable: true,
				enumerable: true,
				value: listParameters.selector + ' > .item-container > div > div > .item-body',
				writable: false
			});
			Object.defineProperty(listParameters, 'listControlContainerSelector', {
				configurable: true,
				enumerable: true,
				value: listParameters.selector + ' > .item-container > div > div > .item-controls-container',
				writable: false
			});
		}

		const listControlButtonContainerSelector = listParameters.listControlContainerSelector + ' > .item-header > .item-control-button-container';
		const listPaginationSelector = listParameters.listControlContainerSelector + ' > .item-header > .pagination-container .pagination';
		const listControlSelector = listParameters.listControlContainerSelector + ' > .item-header > .item-controls, ' + listPaginationSelector + ' .item-controls, ' + listParameters.listContentSelector + ' .items';
		const listSelectedDetailsSelector = listParameters.listControlContainerSelector + ' > .item-header > p';
		const statusMessageSelector = listParameters.listControlContainerSelector + ' > .item-header > .message-container.status';
		elements.html(statusMessageSelector, '<p class="message">Loading</p>');
		elements.addClass(listControlSelector, 'hidden');
		elements.setAttribute(listPaginationSelector + ' .next', 'page', 0);
		elements.setAttribute(listPaginationSelector + ' .previous', 'page', 0);
		let listRequestParameters = {
			from: listParameters.from,
			limit: listParameters.resultsPerPage,
			listName: snakeCaseString(listName, '_'),
			method: 'list',
			offset: ((listParameters.page * listParameters.resultsPerPage) - listParameters.resultsPerPage),
			url: listParameters.url
		};

		if (typeof listParameters.sort !== 'undefined') {
			listRequestParameters.sort = listParameters.sort;
		}

		if (typeof listParameters.where !== 'undefined') {
			listRequestParameters.where = listParameters.where;
		}

		api.setRequestParameters(listRequestParameters);
		api.sendRequest(function(response) {
			if (
				typeof listParameters.callbacks !== 'undefined' &&
				typeof listParameters.callbacks.onReady === 'function'
			) {
				listParameters.callbacks.onReady(response, listParameters);
			}

			let listCount = response.data.length;
			// add listCount and listTotalCount to each list() method
			let listData = response.data;
			let lastResult = listParameters.page * listParameters.resultsPerPage;
			elements.html(listPaginationSelector + ' .result-first', listParameters.page === 1 ? listParameters.page : ((listParameters.page * listParameters.resultsPerPage) - listParameters.resultsPerPage) + 1);
			elements.html(listPaginationSelector + ' .result-last', lastResult >= listTotalCount ? listTotalCount : lastResult);
			elements.html(listSelectedDetailsSelector + ' .results-total, ' + listPaginationSelector + ' .results-total', listTotalCount);
			elements.setAttribute(listPaginationSelector, 'page_current', listParameters.page);
			elements.setAttribute(listPaginationSelector + ' .next', 'page', +elements.html(listPaginationSelector + ' .result-last') < listTotalCount ? listParameters.page + 1 : 0);
			elements.setAttribute(listPaginationSelector + ' .previous', 'page', itemListParameters.page <= 0 ? 0 : itemListParameters.page - 1);

			if (apiRequestParameters[listName].initial === true) {
				selectAllElements(listPaginationSelector + ' .button', function(selectedElementKey, selectedElement) {
					render(function() {
						elements.addEventListener(selectedElement, {
							method: function() {
								if ((page = +elements.getAttribute(selectedElement, 'page')) > 0) {
									var mergeRequestParameters = {};
									mergeRequestParameters[listName] = {};
									mergeRequestParameters[listName].page = page;
									api.setRequestParameters(mergeRequestParameters, true);
									processList(listName);
								}
							},
							name: listParameters.selector,
							type: 'click'
						});
					});
				});
			}

			processProcesses();

			if (
				(typeof data.length !== 'undefined') &&
				data.length
			) {
				elements.removeClass(listControlSelector, 'hidden');
			} else {
				elements.addClass(listControlSelector, 'hidden');
			}

			if (typeof callback === 'function') {
				callback(response, listParameters);
				processWindowEvents('resize');
			}

			if (apiRequestParameters[listName].initial === true) {
				var mergeRequestParameters = {};
				mergeRequestParameters[listName] = {
					initial: false
				};
				api.setRequestParameters(mergeRequestParameters, true);
				elements.addScrollable(listParameters.listControlContainerSelector + '.scrollable', function(element) {
					if (element.details.width) {
						const selectorContainerDetails = elements.get(listParameters.selector).parentNode.getBoundingClientRect();
						elements.get(listParameters.listContentSelector).setAttribute('style', 'padding-top: ' + (elements.get(listParameters.listControlContainerSelector + ' > .item-header').clientHeight + 1) + 'px');
						elements.setAttribute(element, 'style', 'width: ' + element.details.width + 'px; right: ' + (element.details.width - selectorContainerDetails.width) + 'px');
						elements.setAttribute(element, 'scrolled_to_the_bottom', +(window.pageYOffset > Math.max(0, (element.details.bottom + window.pageYOffset - +(elements.get(listParameters.listControlContainerSelector + ' > .item-header').clientHeight)))));
					}
				});
			}

			elements.html(statusMessageSelector, '');

			if (
				typeof apiRequestParameters.search !== 'undefined' &&
				typeof apiRequestParameters.search[listName] !== 'undefined'
			) {
				elements.html(statusMessageSelector, '<a class="clear-search" href="javascript:void(0);"">Clear search</a>');
				render(function() {
					elements.addEventListener('.clear-search', {
						method: function() {
							delete apiRequestParameters.search[listName];
							processList(listName);
						},
						type: 'click'
					});
				});
			}

			processWindowEvents('resize');
		});
	};
	var processLogin = function(processElement, processSubmit) {
		if (processSubmit) {
			api.setRequestParameters({
				method: 'login',
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
		selectAllElements('.submit[process]', function(selectedElementKey, selectedElement) {
			render(function() {
				elements.addEventListener(selectedElement, {
					method: function() {
						api.setRequestParameters({
							processing: true
						});
						elements.setAttribute(selectedElement, 'disabled', 'disabled');
						elements.html(selectedElement, 'Processing');
						processProcess(selectedElement, true);
					},
					type: 'click'
				});
			});
		});
	};
	var processRemove = function(processElement) {
		let listName = camelCaseString(elements.getAttribute(processElement, 'list_name'));
		let listParameters = apiRequestParameters[listName];

		if (confirm('Are you sure you want to remove this ' + snakeCaseString(camelCaseString(listParameters.from), ' ') + '?')) {
			api.setRequestParameters({
				from: listParameters.from,
				listName: snakeCaseString(listName, '_'),
				method: 'remove',
				url: listParameters.url,
				where: {
					id: elements.getAttribute(processElement, snakeCaseString(listName, '_') + '_id')
				}
			});
			api.sendRequest(function(response) {
				let listItems = {};
				mergeRequestParameters[listName] = listParameters;
				mergeRequestParameters[listName].page = 1;
				api.setRequestParameters(mergeRequestParameters, true);
				processList(listName, function() {
					delete apiRequestParameters.processing;
					elements.html(listParameters.selector + ' .message-container.' + snakeCaseString(listName, '-'), response.message);
				});
			});
		} else {
			delete apiRequestParameters.processing;
		}
	};
	var processConfigure = function(processElement, processSubmit) {
		api.setRequestParameters({
			method: 'configure',
			url: '/endpoint/system'
		});
		api.sendRequest(function(response) {
			delete apiRequestParameters.processing;
			elements.get('.configure .authentication-password').value = response.user.authenticationPassword;
			elements.get('.configure .authentication-whitelist').value = response.user.authenticationWhitelist;

			if (processSubmit) {
				elements.html('.configure.message-container', response.message);

				if (apiRequestParameters.data.authenticationPassword) {
					elements.get('.configure .authentication-password').value = apiRequestParameters.data.authenticationPassword;
				}

				if (apiRequestParameters.data.authenticationWhitelist) {
					elements.get('.configure .authentication-whitelist').value = apiRequestParameters.data.authenticationWhitelist;
				}
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

		if (step) {
			step = step;
		} else {
			step = 1;
		}

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
	// todo: refactor code below for nodes (previously proxies.js)
	/*var processAuthenticate = function(processElement, processSubmit) {
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
	};*/
	var processNodes = function() {
		api.setRequestParameters({
			nodeList: {
				callbacks: {
					onReady: function(response, listParameters) {
						processNodeList(response, listParameters);
					}
				},
				from: 'nodes',
				initial: true,
				options: [
					{
						attributes: [
							{
								name: 'class',
								value: 'button hidden icon process-button tooltip tooltip-bottom'
							},
							{
								name: 'option_title',
								value: 'Add node'
							},
							{
								name: 'process',
								value: 'node_add'
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
								name: 'option_title',
								value: 'Manage request destinations'
							},
							{
								name: 'process',
								value: 'request_destinations'
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
								name: 'option_title',
								value: 'Manage request limit rules'
							},
							{
								name: 'process',
								value: 'request_limit_rules'
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
								name: 'option_title',
								value: 'Manage users'
							},
							{
								name: 'process',
								value: 'users'
							}
						],
						tag: 'span'
					},
					/*{
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
					}*/
				],
				page: 1,
				resultsPerPage: 100,
				selector: '.list[from="nodes"]',
				url: '/endpoint/nodes'
			}
		});
		onNodeReady([
			'apiRequestParameters',
			'nodeList'
		], function() {
			elements.removeClass('.list-container .icon.previous', 'hidden');
			processList('nodeList');
		});
	};
	var processNodeAdd = function(processElement, processSubmit) {
		if (processSubmit) {
			delete apiRequestParameters.from;
			api.setRequestParameters({
				data: {
					externalIpVersion4: elements.get('[process="node_add"] [name="external_ip_version_4"]').value,
					externalIpVersion6: elements.get('[process="node_add"] [name="external_ip_version_6"]').value,
					internalIpVersion4: elements.get('[process="node_add"] [name="internal_ip_version_4"]').value,
					internalIpVersion6: elements.get('[process="node_add"] [name="internal_ip_version_6"]').value,
					nodeId: elements.get('[process="node_add"] [name="node_id"]').value
				},
				method: 'add',
				url: '/endpoint/nodes'
			});
			api.sendRequest(function(response) {
				elements.html('.message.node-add', response.message);
				elements.html('.submit[process="node_add"]', 'Save Changes');
				elements.removeAttribute('.submit[process="node_add"]', 'disabled');

				if (response.statusValid === true) {
					closeProcesses();
					processList('listNodes', function() {
						elements.html('.message-container.list-nodes', response.message);
					});
				}
			});
		} else {
			elements.get('[process="node_add"] input').value = '';
		}
	};
	var processNodeList = function(response, listParameters) {
		if (typeof listParameters !== 'object') {
			processList('nodeList');
		} else {
			/*const processServerNodeAdd = function() {
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
			};*/
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




	// todo: refactor code below for nodes (previously servers.js)
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
										value: 'button hidden icon process-button tooltip tooltip-bottom'
									},
									{
										name: 'item_function'
									},
									{
										name: 'item_title',
										value: 'Download request logs for selected server nodes'
									},
									{
										name: 'process',
										value: 'download'
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
	// todo: refactor code below for nodes (previously main.js)
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
</script>
</body>
</html>
