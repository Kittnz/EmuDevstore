function PackageNameEditor() {}

PackageNameEditor.prototype.init = function(id) {
	var nameContainer = document.getElementById('packageName'+id);
	if (nameContainer) {
		var editorObj = this;
		nameContainer.ondblclick = function() { 
			editorObj.startTitleEdit.apply(editorObj, [id]);
		}
	}
}

PackageNameEditor.prototype.startTitleEdit = function(id) {
	var nameContainer = document.getElementById('packageName'+id);
	if (nameContainer) {
		// cancel, if input field does already exist
		var inputs = nameContainer.getElementsByTagName('input');
		if (inputs.length > 0) {
			return;
		}
		
		// hide first child
		var value = '';
		var width = 0;
		var name = nameContainer.getElementsByTagName('span')[0];
		if (name) {
			width = name.offsetWidth;
			
			name.style.display = 'none';
			value = name.innerText;
			
			// IE, Opera, Safari, Konqueror
			if (name.innerText) {
				value = name.innerText;
			}
			// Firefox
			else {
				value = new StringUtil(name.innerHTML).decodeHTML();
			}
		}
	
		// show input field
		var inputField = document.createElement('input');
		inputField.type = 'text';
		inputField.className = 'inputText';
		inputField.value = value;
		inputField.style.width = width+"px";
		nameContainer.appendChild(inputField);
		
		// add event listeners
		var editorObj = this;
		inputField.onkeydown = function(event) { 
			editorObj.doTitleEdit.apply(editorObj, [id, event]);
		}
		inputField.onblur = function() { 
			editorObj.abortTitleEdit.apply(editorObj, [id]);
		}
		
		// set focus
		inputField.focus();
	}
}

PackageNameEditor.prototype.abortTitleEdit = function(id) {
	var nameContainer = document.getElementById('packageName'+id);
	if (nameContainer) {
		// show first child
		var name = nameContainer.getElementsByTagName('span')[0];
		if (name) {
			name.style.display = '';
		}
		
		// remove input field
		var inputs = nameContainer.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i++) {
			nameContainer.removeChild(inputs[i]);
		}
	}
}


PackageNameEditor.prototype.doTitleEdit = function(id, e) {
	if (!e) e = window.event;
	
	// get key code
	var keyCode = 0;
	if (e.which) keyCode = e.which;
	else if (e.keyCode) keyCode = e.keyCode;

	// get input field
	if (e.target) var inputField = e.target;
	else if (e.srcElement) var inputField = e.srcElement;
	
	// enter
	if (keyCode == '13' && (inputField.value != '')) {
		// set new value
		inputField.value = new StringUtil(inputField.value).trim();
		var nameContainer = document.getElementById('packageName'+id);
		var name = nameContainer.getElementsByTagName('span')[0];
		if (name) {
			if (name.firstChild) name.removeChild(name.firstChild);
			name.appendChild(document.createTextNode(inputField.value));
		}
		
		// save new value
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=PackageRename&packageID='+id+SID_ARG_2ND, 'name='+encodeURIComponent(inputField.value));
		
		// abort editing
		inputField.blur();
		return false;
	}
	// esc
	else if (keyCode == '27') {
		inputField.blur();
		return false;
	}
}