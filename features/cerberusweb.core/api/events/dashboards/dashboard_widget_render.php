<?php
/***********************************************************************
| Cerb(tm) developed by Webgroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002-2017, Webgroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Devblocks Public License.
| The latest version of this license can be found here:
| http://cerb.ai/license
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://cerb.ai	    http://webgroup.media
***********************************************************************/

class Event_DashboardWidgetRender extends Extension_DevblocksEvent {
	const ID = 'event.dashboard.widget.render';
	
	/**
	 *
	 * @param Model_TriggerEvent $trigger
	 * @return Model_DevblocksEvent
	 */
	function generateSampleEventModel(Model_TriggerEvent $trigger) {
		$actions = [];
		
		// [TODO] Load random widget of type bot
		
		return new Model_DevblocksEvent(
			self::ID,
			array(
				'widget' => null,
				'actions' => &$actions,
			)
		);
	}
	
	function setEvent(Model_DevblocksEvent $event_model=null, Model_TriggerEvent $trigger=null) {
		$labels = array();
		$values = array();
		
		/**
		 * Behavior
		 */
		
		$merge_labels = array();
		$merge_values = array();
		CerberusContexts::getContext(CerberusContexts::CONTEXT_BEHAVIOR, $trigger, $merge_labels, $merge_values, null, true);

			// Merge
			CerberusContexts::merge(
				'behavior_',
				'',
				$merge_labels,
				$merge_values,
				$labels,
				$values
			);
		
		// Widget
		@$widget = $event_model->params['widget'];
		$labels['widget_id'] = 'Widget ID';
		$values['widget_id'] = @$widget->id ?: 0;
		
		// Actions
		$values['_actions'] =& $event_model->params['actions'];
		
		/**
		 * Return
		 */

		$this->setLabels($labels);
		$this->setValues($values);
	}
	
	function getValuesContexts($trigger) {
		$vals = array(
			'behavior_id' => array(
				'label' => 'Behavior',
				'context' => CerberusContexts::CONTEXT_BEHAVIOR,
			),
			'behavior_bot_id' => array(
				'label' => 'Behavior',
				'context' => CerberusContexts::CONTEXT_BOT,
			),
			'widget_id' => array(
				'label' => 'Widget ID',
				'context' => CerberusContexts::CONTEXT_WORKSPACE_WIDGET,
			),
		);
		
		$vars = parent::getValuesContexts($trigger);
		
		$vals_to_ctx = array_merge($vals, $vars);
		DevblocksPlatform::sortObjects($vals_to_ctx, '[label]');
		
		return $vals_to_ctx;
	}
	
	function getConditionExtensions(Model_TriggerEvent $trigger) {
		$labels = $this->getLabels($trigger);
		$types = $this->getTypes();
		
		$labels['widget_id'] = 'Widget ID';
		$types['widget_id'] = Model_CustomField::TYPE_NUMBER;
		
		$conditions = $this->_importLabelsTypesAsConditions($labels, $types);
		
		return $conditions;
	}
	
	function renderConditionExtension($token, $as_token, $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix','condition'.$seq);
		
		switch($as_token) {
		}

		$tpl->clearAssign('namePrefix');
		$tpl->clearAssign('params');
	}
	
	function runConditionExtension($token, $as_token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$pass = true;
		
		switch($as_token) {
			default:
				$pass = false;
				break;
		}
		
		return $pass;
	}
	
	function getActionExtensions(Model_TriggerEvent $trigger) {
		$actions =
			array(
				'render_html' => array('label' => 'Render HTML'),
			)
		;
		
		return $actions;
	}
	
	function renderActionExtension($token, $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix','action'.$seq);

		$labels = $this->getLabels($trigger);
		$tpl->assign('token_labels', $labels);
			
		switch($token) {
			case 'render_html':
				$tpl->assign('label', null);
				$tpl->assign('key', 'html');
				$tpl->assign('textarea_height', '25em');
				$tpl->display('devblocks:cerberusweb.core::events/dashboard/action_return_value.tpl');
				break;
		}
		
		$tpl->clearAssign('params');
		$tpl->clearAssign('namePrefix');
		$tpl->clearAssign('token_labels');
	}
	
	function simulateActionExtension($token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		switch($token) {
			case 'render_html':
				$tpl_builder = DevblocksPlatform::getTemplateBuilder();
				
				$actions =& $dict->_actions;
				
				@$html = $params['html'];
				$html = $tpl_builder->build($html, $dict);
				
				$out = sprintf(">>> Rendering HTML\n".
					"%s\n",
					$html
				);
				break;
		}
		
		return $out;
	}
	
	function runActionExtension($token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		switch($token) {
			case 'render_html':
				$tpl_builder = DevblocksPlatform::getTemplateBuilder();
				
				$actions =& $dict->_actions;
				
				@$html = $params['html'];
				$html = $tpl_builder->build($html, $dict);
				
				$actions[] = array(
					'_action' => 'render_html',
					'_trigger_id' => $trigger->id,
					'html' => $html,
				);
				break;
		}
	}
};