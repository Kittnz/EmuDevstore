/**
 * ItemListEditor provides a convienient way to get a list sortable
 * the list can be hierarchical and must contain a select box for each item
 */
var ItemListEditor = Class.create({
	/**
	 * Init ItemListEditor
	 * @param	String|Element	itemList	id or element that contains the list
	 */
	initialize: function(itemList) {
		
		this.itemList = $(itemList).identify();
		$(itemList).addClassName('dragable');
		
		this.options = Object.extend({
			itemTitleEdit:		false,
			itemTitleEditURL:	'',
			itemTitleName:		'title',
			tag:			'li',
			headlineTag:		'h3',
			tree:			false,
			treeTag:		'ul',
			dropOnEmpty:		true,
			delay:			0,
			scroll:			window,			
			onChange:		this.changeTree.bind(this),
			onUpdate:		this.updateTree.bind(this)
		}, arguments[1] || { });
		
		// solves IE 6 bug: having a select box within a tree sortable, links are not clickable
		if (IS_IE6 && this.options.tree && this.options.delay == 0) {
			this.options.delay = 100;	
		}
		
		Sortable.create(itemList, this.options);
		
		this.isEditing = false;
		if (this.options.itemTitleEdit) {
			$(itemList).select(this.options.tag + ' > ' + this.options.headlineTag).invoke('observe', 'dblclick', this.startItemTitleEdit.bind(this));
		}
		
		if ($('reset')) {
			$('reset').observe('click', this.resetList.bind(this));
		}
	},
	
	/**
	 * Reset the list
	 * @param	Event	evt	event object
	 */
	resetList: function(evt) {
		location.reload();	
	},
	
	/**
	 * Is called when the tree gets updated (finished dragging)
	 * @param	Element	itemList	element that holds the list
	 */
	updateTree: function(itemList) {
		var subItems = itemList.childElements();
		for (var index = 0, length = subItems.length; index < length; ++index) {
			var subItem = subItems[index];
			this.renderItem(subItem, index + 1, 0, length);
		}		
		this.abortItemTitleEdit();
	},
	
	/**
	 * Is called when the tree gets changed (while dragging)
	 * @param	Element	item	element that gets dragged
	 */
	changeTree: function(item) {
		
	},
	
	/**
	 * Renders a list item (recalculates the current position and parent item), works recursively
	 * @param	Element	item		item to render
	 * @param	int	position	position of item
	 * @param	int	parent		id of parent element
	 * @param	int	length		how many items are in the same level
	 */
	renderItem: function(item, position, parent, length) {
		var itemID = item.identify().gsub('item_', '');
		var selectBox = item.select('select')[0];
		
		if (selectBox) {
			// set name
			selectBox.name = this.options.tree ? this.itemList + 'Positions[' + itemID + '][' + parent + ']' : this.itemList + 'Positions[' + itemID + ']';
			
			// check options
			var selectBoxOptions = selectBox.childElements();
			// if old select box contains more options, remove the last one
			if (selectBoxOptions.length - 1 == length) {
				selectBoxOptions[selectBoxOptions.length - 1].remove();
			}
			// if old select box contains less options, add a new one
			else if (selectBoxOptions.length + 1 == length) {
				selectBox.insert('<option value="' + length + '">' + length + '</option>');
			}
			else if (selectBoxOptions.length != length) {
				var optionsHTML = '';
				// create options
				for (i = 1; i <= length; i++) {
					optionsHTML += '<option value="' + i + '">' + i + '</option>';
				}
				var newSelectBox = new Element('select');
				newSelectBox.name = selectBox.name;
				newSelectBox.insert(optionsHTML);
				selectBox.replace(newSelectBox);
				selectBox = newSelectBox;
			}
			
			// change position
			selectBox.value = position;
		}
		
		// get all items of next level
		var subItems = item.select('#parentItem_' + itemID + ' > ' + this.options.tag);
		var subItemsLength = subItems.size();
		
		// render each sub item
		subItems.each(function(subItem, index) {
			this.renderItem(subItem, (index + 1), itemID, subItemsLength);
		}, this);
	},
	
	/**
	 * Start item title editing
	 * @param	Event	evt event object
	 */
	startItemTitleEdit: function(evt) {
		if (evt.findElement().tagName.toLowerCase() == 'img') {
			return false;	
		}
		
		if (this.isEditing) {
			this.abortItemTitleEdit();
		}
		this.isEditing = true;
		// get headline element
		this.headline = evt.findElement();
		// get current board ID
		this.itemID = this.headline.parentNode.identify().gsub('item_', '');
		// get title link
		if (this.headline.select('a.title').size() > 0) {
			this.link = this.headline.select('a.title')[0];
		}
		else {
			return false;
		}
		// create input and add listener
		this.input = new Element('input', {
			'style': ('width: ' + this.link.getWidth() + 'px;'),
			'class': 'inputText',
			'value': this.link.innerHTML.unescapeHTML()	
		}).observe('blur', this.abortItemTitleEdit.bind(this)).observe('keydown', this.doItemTitleEdit.bind(this));
		// hide the link
		this.link.hide();
		// insert the input
		this.headline.insert(this.input);
		// focus the input
		this.input.focus();
	},
	
	/**
	 * Abort the item title edit
	 * @param	Event	evt event object
	 */
	abortItemTitleEdit: function(evt) {
		// when still in editing mode
		if (this.isEditing) {
			// show the link
			this.link.show();
			// remove input if there is one
			if (this.headline.select('input').size() > 0) {
				this.headline.select('input')[0].remove();
			}
			// remove helper span if there is one
			if (this.headline.select('span').size() > 0) {
				this.headline.select('span')[0].remove();
			}
		}
		this.isEditing = false;
	},
	
	/**
	 * Do the item title edit
	 * @param	Event	evt event object
	 */
	doItemTitleEdit: function(evt) {
		// enter is pressed
		if (this.input.value != '' && evt.keyCode == Event.KEY_RETURN) {
			this.input.value = this.input.value.strip();
			this.link.update(this.input.value);
			if (this.options.itemTitleEditURL != '') {
				// save new value
				var ajaxRequest = new AjaxRequest();
				ajaxRequest.openPost(this.options.itemTitleEditURL + this.itemID + SID_ARG_2ND, this.options.itemTitleName + '=' + encodeURIComponent(this.input.value));
			}
			// abort editing
			this.input.blur();
			evt.stop();
			return false;
		}
		// ESC is pressed
		if (evt.keyCode == Event.KEY_ESC) {
			// abort editing
			this.input.blur();
			evt.stop();
			return false;
		}
		// create helper span to calculate the input width
		this.span = new Element('span', { 'style': 'display: none;' }).update(this.input.value);
		if (this.headline.select('span').size() > 0) {
			this.headline.select('span')[0].remove();
		}
		this.headline.insert(this.span);
		// set the new input width
		if (this.link.getWidth() < this.span.getWidth() + 10) {
			this.input.setStyle({'width': (this.span.getWidth() + 10) + 'px'});
		}
	}

});