<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ActionsModel extends MainModel {

		public function cancel($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error cancelling action, please try again.'
				),
				'processing' => $parameters['processing']
			);

			if (!empty($parameters['where']['id'])) {
				$actionParameters = array(
					'fields' => array(
						'chunks',
						'encoded_items_processed',
						'encoded_items_to_process',
						'encoded_parameters',
						'id',
						'progress',
						'processed',
						'processing',
						'modified'
					),
					'from' => 'actions',
					'sort' => array(
						'field' => 'created',
						'order' => 'ASC'
					),
					'where' => array_merge(array_intersect_key($parameters['where'], array(
						'id' => true
					)), array(
						'processed' => false
					))
				);
				$action = $this->fetch($actionParameters);
				$actionData = array(
					array(
						'id' => $parameters['where']['id'],
						'processed' => true,
						'processing' => false
					)
				);

				if (
					!empty($action['count']) &&
					$this->save(array(
						'data' => $actionData,
						'to' => 'actions'
					))
				) {
					$response = array(
						'message' => array(
							'status' => 'success',
							'text' => 'Action cancelled successfully.'
						),
						'processing' => array_merge($action['data'][0], $actionData[0])
					);
				}
			}

			return $response;
		}

		public function shellProcessActions() {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'There aren\'t any new actions to process, please try again later.'
				)
			);
			$actionParameters = array(
				'fields' => array(
					'chunks',
					'encoded_items_processed',
					'encoded_items_to_process',
					'encoded_parameters',
					'id',
					'progress',
					'processed',
					'processing',
					'modified'
				),
				'from' => 'actions',
				'sort' => array(
					'field' => 'created',
					'order' => 'ASC'
				),
				'where' => array(
					'processed' => false,
					'processing' => true
				)
			);
			$actionsProcessing = $this->fetch($actionParameters);

			if (empty($actionsProcessing['count'])) {
				$actionParameters['where'] = array(
					'processed' => false,
					'processing' => false
				);
				$actionsToProcess = $this->fetch($actionParameters);
				$actionsProcessedCount = 0;

				if (!empty($actionsToProcess['count'])) {
					$actionData = array();

					foreach ($actionsToProcess['data'] as $actionToProcess) {
						$actionData[] = array(
							'id ' => $actionToProcess['id'],
							'processing' => true
						);
					}

					if ($this->save(array(
						'data' => $actionData,
						'to' => 'actions'
					))) {
						foreach ($actionsToProcess['data'] as $actionToProcess) {
							$actionData = array(
								array(
									'id' => $actionToProcess['id']
								)
							);
							$actionProgress = min(100, $actionToProcess['progress'] + ceil(100 / $actionToProcess['chunks']));
							$itemsProcessed = (array) json_decode($actionToProcess['encoded_items_processed'], true);
							$itemsToProcess = json_decode($actionToProcess['encoded_items_to_process'], true);
							$parameters = json_decode($actionToProcess['encoded_parameters'], true);

							if (!empty($itemsToProcess)) {
								$itemLineProcessedCount = count($itemsProcessed);
								$parameters['items'][$parameters['item_list_name']] = array(
									'data' => array(
										$itemLineProcessedCount => $itemsToProcess[$itemLineProcessedCount]
									),
									'from' => $parameters['from'],
									'where' => $parameters['where']
								);

								if (
									$fetchRemovedItems = (
										strpos(json_encode($parameters['where']), '"removed":') === false &&
										!empty($this->settings['database']['schema'][$parameters['from']]['removed'])
									)
								) {
									$parameters['items'][$parameters['item_list_name']]['where']['AND'][]['OR'] = array(
										array(
											'AND' => array(
												'removed' => true
											)
										),
										array(
											'AND' => array(
												'removed' => false
											)
										)
									);
								}

								$actionData[0]['encoded_items_processed'] = json_encode(array_merge($itemsProcessed, $parameters['items'][$parameters['item_list_name']]['data']));
								$parameters['items'] = $this->_decodeItems($parameters, true);

								if ($fetchRemovedItems === true) {
									array_pop($parameters['items'][$parameters['item_list_name']]['where']['AND']);
								}
							}

							$actionData[0] = array_merge($actionData[0], array(
								'processed' => ($itemsProcessed = ($actionProgress === 100)),
								'processing' => false,
								'progress' => $actionProgress
							));
							$actionResponse = $this->_call(array(
								'method_from' => $parameters['from'],
								'method_name' => $parameters['action'],
								'method_parameters' => array(
									$parameters
								)
							));
							$actionParameters['where']['processing'] = true;
							$actionProcessing = $this->fetch($actionParameters);

							if (!empty($actionProcessing['count'])) {
								if ($itemsProcessed === true) {
									$this->_deleteRemovedItems(array(
										'from' => $parameters['from'],
										'where' => !empty($parameters['where']) ? $parameters['where'] : array()
									));
								}

								if ($actionResponse['message']['status'] === 'error') {
									$actionData[0]['processed'] = true;
								}

								$this->save(array(
									'data' => $actionData,
									'to' => 'actions'
								));
							}

							$actionsProcessedCount++;
						}
					}

					if ($actionsProcessedCount) {
						$response = array(
							'message' => array(
								'status' => 'success',
								'text' => 'Actions processed successfully.'
							)
						);
					}
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$actionsModel = new ActionsModel();
		$data = $actionsModel->route($configuration->parameters);
	}
?>
