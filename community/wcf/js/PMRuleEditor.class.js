/**
 * PMRuleEditor 
 */
var PMRuleEditor = Class.create({
	
	initialize: function (typesContainer) {
		this.typesContainer = typesContainer;
		this.id = 1;
		this.count = 0;
		this.options = Object.extend({
			availableConditionTypes:	'',
			selectedConditions:		'',
			actionContainer:		'',
			availableActions:		'',
			selectedAction:			'',
			selectedDestination:		'',
			imgDeleteDisabledSrc:		RELATIVE_WCF_DIR + 'icon/deleteDisabledS.png',
			imgDeleteSrc:			RELATIVE_WCF_DIR + 'icon/deleteS.png',
			imgAddSrc: 			RELATIVE_WCF_DIR + 'icon/addS.png'
		}, arguments[1] || { });
		this.initConditions(this.options.selectedConditions);
		this.initAction(this.options.selectedAction, this.options.selectedDestination);
	},

	initConditions: function(selectedConditions) {
		if (selectedConditions.length > 0) {
			for (var i = 0; i < selectedConditions.length; i++) {
				this.addCondition(selectedConditions[i]);
			}
		}
		else {
			this.addCondition();
		}
	},

	addCondition: function(defaultCondition) {
		var editorObj = this;
		var id = this.id;
		
		// create div
		var div = document.createElement('div');
		div.className = 'floatContainer';
		div.id = 'condition'+this.id;
		this.typesContainer.appendChild(div);
		
		// create subDiv
		var subDiv = document.createElement('div');
		subDiv.className = 'floatedElement';
		div.appendChild(subDiv);
		
		// create type dropdown
		var typeSelect = document.createElement('select');
		typeSelect.id = 'typeSelect'+this.id;
		typeSelect.name = 'ruleConditions['+this.id+'][type]';
		typeSelect.onchange = typeSelect.onkeyup = function() {
			editorObj.changeType.apply(editorObj, [id]);
		}
		subDiv.appendChild(typeSelect);
		for (type in this.options.availableConditionTypes) {
			var typeOption = document.createElement('option');
			typeOption.value = type;
			typeSelect.appendChild(typeOption);
			if (defaultCondition && defaultCondition.type && defaultCondition.type == type) typeOption.selected = true;
			typeOption.appendChild(document.createTextNode(this.options.availableConditionTypes[type]['name']));
		}
		
		// create type dependent fields
		this.changeType(this.id, ((defaultCondition && defaultCondition.condition) ? defaultCondition.condition : ''), ((defaultCondition && defaultCondition.value) ? defaultCondition.value : ''));
		
		// create buttons
		var buttonDiv = document.createElement('div');
		buttonDiv.className = 'floatedElementButtons';
		div.appendChild(buttonDiv);
		
		var addButton = document.createElement('input');
		addButton.type = 'image';
		addButton.src = this.options.imgAddSrc;
		addButton.onclick = function() {
			editorObj.addCondition.apply(editorObj);
			return false;
		}
		buttonDiv.appendChild(addButton);
		
		var deleteButton = document.createElement('input');
		deleteButton.type = 'image';
		if (this.count == 0) {
			deleteButton.disabled = true;
			deleteButton.src = this.options.imgDeleteDisabledSrc;
		}
		else {
			deleteButton.src = this.options.imgDeleteSrc;
		}
		deleteButton.onclick = function() {
			editorObj.deleteCondition.apply(editorObj, [id]);
			return false;
		}
		buttonDiv.appendChild(deleteButton);
		
		if (this.count == 1) {
			var nodes = document.getElementById('conditions').firstChild.childNodes;
			for (var i = 0; i < nodes.length; i++) {
				if (nodes[i].className == 'floatedElementButtons') {
					nodes[i].lastChild.disabled = false;
					nodes[i].lastChild.src = this.options.imgDeleteSrc;
					break;
				}
			}
		}
		
		this.id++;
		this.count++;
	},

	deleteCondition: function(id) {
		var div = document.getElementById('condition'+id);
		if (div && div.parentNode && this.count > 1) {
			div.parentNode.removeChild(div);
			this.count--;
		}
		if (this.count == 1) {
			var nodes = document.getElementById('conditions').firstChild.childNodes;
			for (var i = 0; i < nodes.length; i++) {
				if (nodes[i].className == 'floatedElementButtons') {
					nodes[i].lastChild.disabled = true;
					nodes[i].lastChild.src = this.options.imgDeleteDisabledSrc;
					break;
				}
			}
		}
	},

	changeType: function(id, defaultCondition, defaultValue) {
		var div = document.getElementById('condition'+id);
		var typeSelect = document.getElementById('typeSelect'+id);
		if (div && typeSelect) {
			// delete old fields
			var oldConditionSelect = document.getElementById('conditionSelect'+id);
			if (oldConditionSelect) oldConditionSelect.parentNode.removeChild(oldConditionSelect);
			var oldValueInput = document.getElementById('valueInput'+id);
			if (oldValueInput) oldValueInput.parentNode.removeChild(oldValueInput);
			
			// create new fields
			var type = typeSelect.options[typeSelect.selectedIndex].value;
			if (this.options.availableConditionTypes[type]) {
				// create condition dropdown
				var conditionSelect = document.createElement('select');
				conditionSelect.id = 'conditionSelect'+id;
				conditionSelect.name = 'ruleConditions['+id+'][condition]';
				for (condition in this.options.availableConditionTypes[type]['availableConditions']) {
					var conditionOption = document.createElement('option');
					conditionOption.value = condition;
					conditionSelect.appendChild(conditionOption);
					if (defaultCondition && defaultCondition == condition) conditionOption.selected = true;
					conditionOption.appendChild(document.createTextNode(this.options.availableConditionTypes[type]['availableConditions'][condition]));
				}
				if (conditionSelect.options.length > 0) {
					var subDiv = document.createElement('div');
					subDiv.className = 'floatedElement';
					subDiv.appendChild(conditionSelect);
					div.appendChild(subDiv);
				}
				
				// create value field
				if (this.options.availableConditionTypes[type]['valueType'] == 'text') {
					var valueInput = document.createElement('input');
					valueInput.id = 'valueInput'+id;
					valueInput.name = 'ruleConditions['+id+'][value]';
					valueInput.type = 'text';
					valueInput.className = 'inputText';
					if (defaultValue) valueInput.value = defaultValue;
					var subDiv = document.createElement('div');
					subDiv.className = 'floatedElement';
					subDiv.appendChild(valueInput);
					div.appendChild(subDiv);
				}
				else if (this.options.availableConditionTypes[type]['valueType'] == 'options') {
					var valueInput = document.createElement('select');
					valueInput.id = 'valueInput'+id;
					valueInput.name = 'ruleConditions['+id+'][value]';
					for (value in this.options.availableConditionTypes[type]['availableValues']) {
						var valueOption = document.createElement('option');
						valueOption.value = value;
						valueInput.appendChild(valueOption);
						if (defaultValue && defaultValue == value) valueOption.selected = true;
						valueOption.appendChild(document.createTextNode(this.options.availableConditionTypes[type]['availableValues'][value]));
					}
					if (valueInput.options.length > 0) {
						var subDiv = document.createElement('div');
						subDiv.className = 'floatedElement';
						subDiv.appendChild(valueInput);
						div.appendChild(subDiv);
					}
				}
			}
		}
	},

	initAction: function(selectedAction, selectedDestination) {
		var editorObj = this;
		
		// create div
		var div = document.createElement('div');
		div.className = 'floatedElement';
		this.options.actionContainer.appendChild(div);
		
		// create action dropdown
		var actionSelect = document.createElement('select');
		actionSelect.name = 'ruleAction';
		actionSelect.onchange = actionSelect.onkeyup = function() {
			editorObj.changeAction.apply(editorObj);
		}
		div.appendChild(actionSelect);
		for (availableAction in this.options.availableActions) {
			var actionOption = document.createElement('option');
			actionOption.value = availableAction;
			actionSelect.appendChild(actionOption);
			actionOption.appendChild(document.createTextNode(this.options.availableActions[availableAction]['name']));
		}
		
		// set default value
		if (selectedAction) {
			for (var i = 0; i < actionSelect.options.length; i++) {
				if (actionSelect.options[i].value == selectedAction) {
					actionSelect.options[i].selected = true;
					break;
				}
			}
		}
		
		// create action dependent fields
		this.changeAction(selectedDestination);
	},

	changeAction: function(selectedDestination) {
		var actionSelect = this.options.actionContainer.childNodes[1].firstChild;
		if (actionSelect) {
			// delete old field
			if (this.options.actionContainer.childNodes.length > 2) {
				this.options.actionContainer.removeChild(this.options.actionContainer.childNodes[2]);
			}
			// create new field
			var action = actionSelect.options[actionSelect.selectedIndex].value;
			if (this.options.availableActions[action]) {
				// create destination field
				if (this.options.availableActions[action]['destinationType'] == 'text') {
					var destinationInput = document.createElement('input');
					destinationInput.name = 'ruleDestination';
					destinationInput.type = 'text';
					destinationInput.className = 'inputText';
					if (selectedDestination) destinationInput.value = selectedDestination;
					
					// create div
					var div = document.createElement('div');
					div.className = 'floatedElement';
					this.options.actionContainer.appendChild(div);
					div.appendChild(destinationInput);
				}
				else if (this.options.availableActions[action]['destinationType'] == 'options') {
					var destinationInput = document.createElement('select');
					destinationInput.name = 'ruleDestination';
					for (destination in this.options.availableActions[action]['availableDestinations']) {
						var destinationOption = document.createElement('option');
						destinationOption.value = destination;
						destinationInput.appendChild(destinationOption);
						if (selectedDestination && selectedDestination == destination) destinationOption.selected = true;
						destinationOption.appendChild(document.createTextNode(this.options.availableActions[action]['availableDestinations'][destination]));
					}
					if (destinationInput.options.length > 0) {
						// create div
						var div = document.createElement('div');
						div.className = 'floatedElement';
						this.options.actionContainer.appendChild(div);
						div.appendChild(destinationInput);
					}
				}
			}
		}
	}
});