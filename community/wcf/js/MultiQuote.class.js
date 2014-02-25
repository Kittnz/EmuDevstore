/**
 * Functions to quote multiple posts or single text passages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
var MultiQuote = Class.create({
	/**
	 * Inits MultiQuote.
	 */
	initialize: function(data, formURL) {
		this.options = Object.extend({
			langButtonQuote			: '',
			langButtonQuoteMultiple		: '',
			langQuoteDirectly		: '',
			langMarkToQuote			: '',
			langQuoteTextDirectly		: '',
			langMarkTextToQuote		: '',
			langRemoveQuotes		: '',
			langQuoteParagraphDirectly	: '',
			langMarkParagraphToQuote	: '',
			langQuoteParagraphsDirectly	: '',
			langMarkParagraphsToQuote	: '',
			iconMessageQuoteOptions		: ''
		}, arguments[2] || { });
		
		// store the data
		this.data = data;
		this.formURL = formURL;
		
		// init given objects
		var multiQuote = this;
		this.data.each(function(objectData) {
			multiQuote.initializeObject.apply(multiQuote, [objectData]);
		});
	},
	
	/**
	 * Inits a quote object.
	 */
	initializeObject: function(objectData) {
		var multiQuote = this;
		
		// search existing button
		var button = $(objectData.value.objectType + 'Quote' + objectData.value.objectID);
		if (!button) {
			// create new button
			var buttonBar = document.getElementById(objectData.value.objectType + 'Buttons' + objectData.value.objectID);
			if (buttonBar) {
				// li
				var li = document.createElement('li');
				li.className = 'options';
				if (buttonBar.childNodes.length < 2) {
					buttonBar.appendChild(li);
				}
				else {
					buttonBar.insertBefore(li, buttonBar.childNodes[2]);
				}
				
				// a
				button = new Element('a');
				button.id = objectData.value.objectType + 'Quote' + objectData.value.objectID;
				li.appendChild(button);
				// img
				var img = document.createElement('img');
				img.src = this.options.iconMessageQuoteOptions;
				button.appendChild(img);
				// space
				button.appendChild(document.createTextNode(' '));
				// span
				var span = document.createElement('span');
				button.appendChild(span);
				span.appendChild(document.createTextNode(this.options.langButtonQuote));
			}
		}
		if (button) {
			// set dropdown class name
			button.up().addClassName('options');
			
			// create drop down menu
			button.href = "javascript:void(0);";
			
			// add double click listener
			if (this.formURL) {
				button.ondblclick = function() {
					multiQuote.quoteDirectly.apply(multiQuote, [objectData.value.objectType, objectData.value.objectID]);
				};
			}
				
			// change button icon
			if (button.firstChild && button.firstChild.src) {
				var icon = button.firstChild;
				icon.src = this.options.iconMessageQuoteOptions;
			}
				
			// add onclick listener
			button.onmousedown = function() {
				multiQuote.showMenu.apply(multiQuote, [objectData.value.objectType, objectData.value.objectID]);
			};
				
			// crate popup menu div
			var menuDiv = document.createElement('div');
			menuDiv.id = objectData.value.objectType + 'Quote' + objectData.value.objectID + 'Menu';
			menuDiv.className = 'hidden';
			button.parentNode.appendChild(menuDiv);
			popupMenuList.register(objectData.value.objectType + 'Quote' + objectData.value.objectID);
		}
		
		if (objectData.value.quotes > 0) {
			this.updateQuoteButton(objectData.value.objectType, objectData.value.objectID);
		}
	},
	
	/**
	 * Updates the status of a quote button.
	 */
	updateQuoteButton : function(objectType, objectID) {
		// get button
		var button = $(objectType + 'Quote' + objectID);
		if (button) {
			// update (li) class name
			var objectValue = this.data.get(objectType + '-' + objectID);
			if (objectValue.quotes > 0) {
				button.up().addClassName('selected');
			}
			else {
				button.up().removeClassName('selected');
			}
			
			// update button text
			if (button.childNodes[2] && button.childNodes[2].firstChild) {
				button.childNodes[2].removeChild(button.childNodes[2].firstChild);
				button.childNodes[2].appendChild(document.createTextNode(eval(this.options.langButtonQuoteMultiple)));
			}
		}
	},
	
	/**
	 * Gets the selected text passage.
	 */
	getSelectedText : function(objectType, objectID) {
		this.selectedText = '';
		var selectedText = '';
		
		// get selected text
		if (window.getSelection) {
			selectedText = window.getSelection().toString();
			if (selectedText == undefined) { // safari fix
				selectedText = new String(window.getSelection());
			}
		}
		else if (document.getSelection) {
			selectedText = document.getSelection().toString();
		}
		else if (document.selection) {
			selectedText = document.selection.createRange().text;
		}
		
		// check whether the selected text is contained by the text div
		if (selectedText != '') {
			var availableText = this.getAvailableText(objectType, objectID);
			availableText = availableText.replace(/\s+/g, '');
			var text = new String(selectedText).replace(/\s+/g, '');
			if (availableText.indexOf(text) != -1) {
				this.selectedText = new String(selectedText);
			}
		}
	},
	
	/**
	 * Returns the complete text of a message.
	 */
	getAvailableText : function(objectType, objectID) {
		var element = document.getElementById(objectType + 'Text' + objectID);
		if (!element) return '';
		return this.getNodeText(element);
	},
	
	/**
	 * Returns the text of a node and his children.
	 */
	getNodeText : function(node) {
		var nodeText = '';
		
		for (var i = 0; i < node.childNodes.length; i++) {
			if (node.childNodes[i].nodeType == 3) {
				// text node
				nodeText += node.childNodes[i].nodeValue;
			}
			else {
				// fix for mozilla smiley bug
				if (IS_MOZILLA && node.childNodes[i].nodeName.toLowerCase() == 'img' && node.childNodes[i].alt) {
					nodeText += node.childNodes[i].alt;
				}
				// fix for ie line break issue
				if (IS_IE && node.childNodes[i].nodeName.toLowerCase() == 'br') {
					nodeText += '\n';
				}
				nodeText += this.getNodeText(node.childNodes[i]);
			}
		}
		
		return nodeText;
	},
	
	/**
	 * Returns a list of quote options.
	 */
	getQuoteOptions : function(objectType, objectID) {
		var multiQuote = this;
		var options = new Array();
		var i = 0;
		this.getSelectedText(objectType, objectID);
		
		// quote directly
		if (this.formURL) {
			options[i] = new Object();
			options[i]['function'] = function() {
				multiQuote.quoteDirectly.apply(multiQuote, [objectType, objectID]);
			};
			options[i]['text'] = (this.selectedText != '' ? this.options.langQuoteTextDirectly : this.options.langQuoteDirectly);
			i++;
		}
		
		// mark to quote
		options[i] = new Object();
		options[i]['function'] = function() {
			multiQuote.markToQuote.apply(multiQuote, [objectType, objectID]);
		};
		options[i]['text'] = (this.selectedText != '' ? this.options.langMarkTextToQuote : this.options.langMarkToQuote);
		options[i]['className'] = 'bottomSeparator';
		i++;
		
		if (this.selectedText != '') {
			// quote paragraph
			if (this.formURL) {
				options[i] = new Object();
				options[i]['function'] = function() {
					multiQuote.quoteParagraphDirectly.apply(multiQuote, [objectType, objectID]);
				};
				options[i]['text'] = this.options.langQuoteParagraphDirectly;
				i++;
			}
			
			options[i] = new Object();
			options[i]['function'] = function() {
				multiQuote.markParagraphToQuote.apply(multiQuote, [objectType, objectID]);
			};
			options[i]['text'] = this.options.langMarkParagraphToQuote;
			i++;
		}
		else {
			// quote paragraphs
			if (this.formURL) {
				options[i] = new Object();
				options[i]['function'] = function() {
					multiQuote.quoteParagraphsDirectly.apply(multiQuote, [objectType, objectID]);
				};
				options[i]['text'] = this.options.langQuoteParagraphsDirectly;
				i++;
			}
			
			options[i] = new Object();
			options[i]['function'] = function() {
				multiQuote.markParagraphsToQuote.apply(multiQuote, [objectType, objectID]);
			};
			options[i]['text'] = this.options.langMarkParagraphsToQuote;
			i++;
		}
		
		// remove quotes
		var objectValue = this.data.get(objectType + '-' + objectID);
		if (objectValue.quotes) {
			options[i] = new Object();
			options[i]['function'] = function() {
				multiQuote.removeQuotes.apply(multiQuote, [objectType, objectID]);
			};
			options[i]['text'] = this.options.langRemoveQuotes;
			options[i]['className'] = 'topSeparator';
			i++;
		}
		
		return options;
	},
	
	/**
	 * Show the quote menu.
	 */
	showMenu : function(objectType, objectID) {
		var options = this.getQuoteOptions(objectType, objectID);
		this.createMenu(options, objectType, objectID);
	},
	
	/**
	 * Creates a new quote menu.
	 */
	createMenu : function(options, objectType, objectID) {
		// get menu div
		var menuDiv = document.getElementById(objectType + 'Quote' + objectID + 'Menu');
		
		// remove old elements
		while (menuDiv.hasChildNodes()) {
			menuDiv.removeChild(menuDiv.firstChild);
		}
		
		// menu ul
		var menuUL = document.createElement('ul');
		menuDiv.appendChild(menuUL);
		
		// menu elements
		for (var i = 0; i < options.length; i++) {
			var menuLI = document.createElement('li');
			menuUL.appendChild(menuLI);
			if (options[i]['className']) menuLI.className = options[i]['className'];
			
			var menuA = document.createElement('a');
			menuA.onclick = options[i]['function'];
			menuLI.appendChild(menuA);
			menuA.appendChild(document.createTextNode(options[i]['text']));
		}
	},
	
	/**
	 * Quotes one post directly.
	 */
	quoteDirectly : function(objectType, objectID) {
		this.saveQuote(objectType, objectID, this.selectedText, true);
	},
	
	/**
	 * Returns the text of a selected paragraph.
	 */
	getParagraphText : function(objectType, objectID) {
		var text = this.getAvailableText(objectType, objectID);
		// find text position
		var selectionStart = text.indexOf(this.selectedText);
		if (selectionStart != -1) {
			// find start of the paragraph
			var linebreak = false;
			for (var paragraphStart = selectionStart; paragraphStart >= 0; paragraphStart--) {
				if (text.charAt(paragraphStart) == '\n') {
					if (linebreak) {
						paragraphStart = paragraphStart + 2;
						break;
					}
					else {
						linebreak = true;
					}
				}
				else {
					linebreak = false;
				}
			}
			
			// find end of the paragraph
			linebreak = false;
			for (var paragraphEnd = selectionStart + this.selectedText.length; paragraphEnd < text.length; paragraphEnd++) {
				if (text.charAt(paragraphEnd) == '\n') {
					if (linebreak) {
						paragraphEnd = paragraphEnd - 1;
						break;
					}
					else {
						linebreak = true;
					}
				}
				else {
					linebreak = false;
				}
			}
			
			return text.substring(paragraphStart, paragraphEnd).strip();
		}
		
		return '';
	},
	
	/**
	 * Returns the text of a selected paragraph.
	 */
	getParagraphs : function(objectType, objectID) {
		var text = this.getAvailableText(objectType, objectID);
		var paragraphs = new Array();
		
		var linebreak = false;
		var lastPosition = 0;
		for (var i = 0; i < text.length; i++) {
			if (text.charAt(i) == '\n') {
				if (linebreak) {
					var paragraphText = text.substring(lastPosition, i - 1).strip();
					if (paragraphText != '') paragraphs.push(paragraphText);
					lastPosition = i + 1;
				}
				else {
					linebreak = true;
				}
			}
			else {
				linebreak = false;
			}
		}
		
		var paragraphText = text.substring(lastPosition).strip();
		if (paragraphText != '') paragraphs.push(paragraphText);
		
		return paragraphs;
	},
	
	/**
	 * Quotes the selected paragraph of one post directly.
	 */
	quoteParagraphDirectly : function(objectType, objectID) {
		var paragraphText = this.getParagraphText(objectType, objectID);
		if (paragraphText != '') {
			this.saveQuote(objectType, objectID, paragraphText, true);
		}
	},
	
	/**
	 * Marks a paragraph to quote.
	 */
	markParagraphToQuote : function(objectType, objectID) {
		var paragraphText = this.getParagraphText(objectType, objectID);
		if (paragraphText != '') {
			this.saveQuote(objectType, objectID, paragraphText, false);
		
			// update button
			var objectValue = this.data.get(objectType + '-' + objectID);
			objectValue.quotes++;
			this.updateQuoteButton(objectType, objectID);
		}
	},
	
	/**
	 * Quotes the paragraphs of one post directly.
	 */
	quoteParagraphsDirectly : function(objectType, objectID) {
		var paragraphs = this.getParagraphs(objectType, objectID);
		if (paragraphs.length > 0) {
			this.saveQuote(objectType, objectID, paragraphs, true);
		}
	},
	
	/**
	 * Marks paragraphs to quote.
	 */
	markParagraphsToQuote : function(objectType, objectID) {
		var paragraphs = this.getParagraphs(objectType, objectID);
		if (paragraphs.length > 0) {
			this.saveQuote(objectType, objectID, paragraphs, false);
			
			// update button
			var objectValue = this.data.get(objectType + '-' + objectID);
			objectValue.quotes = objectValue.quotes + paragraphs.length;
			this.updateQuoteButton(objectType, objectID);
		}
	},
	
	/**
	 * Marks a post to quote.
	 */
	markToQuote : function(objectType, objectID) {
		this.saveQuote(objectType, objectID, this.selectedText, false);
				
		// update button
		var objectValue = this.data.get(objectType + '-' + objectID);
		objectValue.quotes++;
		this.updateQuoteButton(objectType, objectID);
	},
	
	/**
	 * Removes all quotes of a message.
	 */
	removeQuotes : function(objectType, objectID) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=MessageQuotesRemove&t='+SECURITY_TOKEN+SID_ARG_2ND, 'objectType='+objectType+'&objectID='+objectID);
		
		// update button
		var objectValue = this.data.get(objectType + '-' + objectID);
		objectValue.quotes = 0;
		this.updateQuoteButton(objectType, objectID);
		
		if (this.manager) {
			this.manager.removeQuotes(objectType, objectID);
			this.manager.showQuotes();
		}
	},
	
	/**
	 * Saves a quote via ajax.
	 */
	saveQuote : function(objectType, objectID, text, singleQuote) {
		document.fire('wcf:MultiQuote:saveQuote');
		var multiQuote = this;
		var quoteData = this.data.get(objectType+'-'+objectID);
		this.ajaxRequest = new AjaxRequest();
		var postData = 'objectType='+objectType+'&objectID='+objectID;
		if (text instanceof Array) {
			for (i = 0; i < text.length; i++) {
				var quoteID = this.getQuoteID();
				postData += '&text['+quoteID+']='+encodeURIComponent(text[i]);
				
				if (this.manager) {
					this.manager.storeQuote(quoteID, objectType, objectID, text[i], quoteData.author, quoteData.url);
				}
			}
		}
		else {
			var quoteID = this.getQuoteID();
			postData += '&text['+quoteID+']='+encodeURIComponent(text);
			
			if (this.manager) {
				this.manager.storeQuote(quoteID, objectType, objectID, (text != '' ? text : this.getAvailableText(objectType, objectID)), quoteData.author, quoteData.url);
			}
		}
		
		this.ajaxRequest.openPost('index.php?action='+objectType.substring(0, 1).toUpperCase()+objectType.substring(1)+'MessageQuote&t='+SECURITY_TOKEN+SID_ARG_2ND, postData, (singleQuote ? function() { multiQuote.receiveResponse.apply(multiQuote); } : false));
		
		if (this.manager) {
			this.manager.showQuotes();
		}
	},
	
	/**
	 * Receives the ajax response.
	 */
	receiveResponse : function() {
		if (this.ajaxRequest && this.ajaxRequest.xmlHttpRequest.readyState == 4) {
			document.location.href = fixURL(this.formURL);
		}
	},
	
	/**
	 * Sets the multi quote manager object.
	 */
	setManager : function(manager) {
		this.manager = manager;
	},
	
	/**
	 * Generates a quote id.
	 */
	getQuoteID : function() {
		var chars = 'abcdef1234567890';
		var quoteID = '';
		for (var i = 0; i < 40; i++) {
			quoteID += chars.charAt(Math.floor(Math.random() * 16.0));
		}
		
		return quoteID;
	}
});