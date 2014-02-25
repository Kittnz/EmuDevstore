function SmileyCategorySwitcher(smileyCategories) {
	this.smileyCategories = smileyCategories;
	this.selectedSmileyCategoryID = 0;
	this.init();
}

SmileyCategorySwitcher.prototype.init = function() {
	if (this.smileyCategories.keys().length > 1) {
		var switcherObj = this;
		// add event listener
		var tabMenuList = document.getElementById('tabMenuList');
		for (var i = 0; i < tabMenuList.childNodes.length; i++) {
			if (tabMenuList.childNodes[i].nodeName.toLowerCase() == 'li') {
				if (tabMenuList.childNodes[i].id == 'smiliesTab') {
					tabMenuList.childNodes[i].onclick = function() {
						switcherObj.showSmileyCategories.apply(switcherObj);
					}
				}
				else {
					tabMenuList.childNodes[i].onclick = function() { 
						if (this.className.indexOf('disabled') == -1) {
							switcherObj.hideSmileyCategories.apply(switcherObj);
						}
					}
				}
			}
		}
	}
}

SmileyCategorySwitcher.prototype.showSmileyCategories = function() {
	if (this.smileyCategories.keys().length > 1) {
		this.hideSmileyCategories();
		var switcherObj = this;
		var subTabMenu = document.getElementById('subTabMenu');
		
		// create ul
		var ul = document.createElement('ul');
		subTabMenu.firstChild.appendChild(ul);
		
		var ids = this.smileyCategories.keys();
		for (var i = 0; i < ids.length; i++) {
			var id = ids[i];
			// create li
			var li = document.createElement('li');
			ul.appendChild(li);
			li.id = 'smileyCategoryLink-'+id;
			li.name = id;
			li.onclick = function() {
				switcherObj.showSmileyCategory.apply(switcherObj, [this.name]);
			}
			if (this.selectedSmileyCategoryID == id) li.className = 'activeSubTabMenu';
			
			// create a
			var a = document.createElement('a');
			li.appendChild(a);
			
			// create span
			var span = document.createElement('span');
			a.appendChild(span);
			
			var text = document.createTextNode(this.smileyCategories.get(id).unescapeHTML());
			span.appendChild(text);
		}
	}
}

SmileyCategorySwitcher.prototype.hideSmileyCategories = function() {
	var subTabMenu = document.getElementById('subTabMenu');
	if (subTabMenu.firstChild) {
		for (var i = subTabMenu.firstChild.childNodes.length - 1; i >= 0; i--) {
			subTabMenu.firstChild.removeChild(subTabMenu.firstChild.childNodes[i]);
		}
	}
}
	
SmileyCategorySwitcher.prototype.showSmileyCategory = function(newSelectedID) {
	var switcherObj = this;
	var ids = this.smileyCategories.keys();
	for (var i = 0; i < ids.length; i++) {
		var id = ids[i];
		// set active status
		var smileyCategoryLink = document.getElementById('smileyCategoryLink-'+id);
		if (newSelectedID == id) smileyCategoryLink.className = 'activeSubTabMenu';
		else smileyCategoryLink.className = '';
		
		// show smileys
		var smileyCategory = document.getElementById('smileyCategory-'+id);
		if (newSelectedID == id) {
			if (smileyCategory) {
				smileyCategory.className = 'smileys';
			}
			else {
				// create elements
				var smileyContainer = document.getElementById('smileyContainer');
				var ul = document.createElement('ul');
				smileyContainer.appendChild(ul);
				ul.id = 'smileyCategory-'+id;
				ul.className = 'smileys';
			
				// load smileys
				var requestedSmileyCategoryID = id;
				var ajaxRequest = new AjaxRequest();
				ajaxRequest.openPost('index.php?page=SmileyXMLList', 'smileyCategoryID='+encodeURIComponent(id), function() {
					switcherObj.receiveSmileys.apply(switcherObj, [ajaxRequest.xmlHttpRequest, requestedSmileyCategoryID]);
				});
			}
		}
		else {
			if (smileyCategory) smileyCategory.className = 'hidden';
		}
	}
	
	this.selectedSmileyCategoryID = newSelectedID;
}
	
SmileyCategorySwitcher.prototype.receiveSmileys = function(request, id) {
	if (request.readyState == 4 && request.status == 200 && request.responseXML) {
		var smileyCategory = document.getElementById('smileyCategory-'+id);
		var smileys = request.responseXML.getElementsByTagName('smileys');
		if (smileys.length > 0) {
			for (var i = 0; i < smileys[0].childNodes.length; i++) {
				if (smileys[0].childNodes[i].childNodes.length > 0) {
					var path = '';
					var title = '';
					var code = '';
					for (var j = 0; j < smileys[0].childNodes[i].childNodes.length; j++) {
						if (smileys[0].childNodes[i].childNodes[j].nodeName == 'path') {
							path = smileys[0].childNodes[i].childNodes[j].childNodes[0].nodeValue;
						}
						if (smileys[0].childNodes[i].childNodes[j].nodeName == 'title') {
							title = smileys[0].childNodes[i].childNodes[j].childNodes[0].nodeValue;
						}
						if (smileys[0].childNodes[i].childNodes[j].nodeName == 'code') {
							code = smileys[0].childNodes[i].childNodes[j].childNodes[0].nodeValue;
						}
					}
					
					// create element
					var li = document.createElement('li');
					smileyCategory.appendChild(li);
					var img = document.createElement('img');
					li.appendChild(img);
					img.src = RELATIVE_WCF_DIR + path;
					img.onmouseover = function() { this.style.cursor='pointer'; };
					img.title = title;
					img.onclick = this.getSmileyInsertFunction(RELATIVE_WCF_DIR + path, title, code);
					
					// add smiley to wysiwyg
					smilies[code] = new Array(RELATIVE_WCF_DIR + path, title);
				}
			}
		}
		
		request.abort();
	}
}
	
SmileyCategorySwitcher.prototype.getSmileyInsertFunction = function(path, title, code) {
	return function() { WysiwygInsert.apply(window, ['smiley', path, title, code]); };
}