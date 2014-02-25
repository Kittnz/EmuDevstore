/**
 * Functions to manage stored quotes.
 *
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
var MultiQuoteManager = Class.create({
	/**
	 * Inits MultiQuote.
	 */
	initialize: function(quotes, formURL) {
		this.options = Object.extend({
			langInsertQuote: '',
			langRemoveQuote: '',
			langNoQuotes: '',
			langSelectedQuotes: '',
			imgInsertSrc: '',
			imgRemoveSrc: ''
		}, arguments[1] || { });
		
		// store the data
		this.quotes = quotes;
		
		// show quotes
		this.showQuotes();
	},
	
	/**
	 * Shows the quotes.
	 */
	showQuotes: function() {
		var div = document.getElementById('multiQuoteList');
		if (div) {
			// empty div
			while (div.childNodes.length > 0) {
				div.removeChild(div.firstChild);
			}
			
			var quoteIDs = this.quotes.keys();
			if (quoteIDs.length > 0) {
				var ul = document.createElement('ul');
				div.appendChild(ul);
				ul.className = 'dataList';
				
				for (var i = 0; i < quoteIDs.length; i++) {
					var quoteID = quoteIDs[i];
					var quote = this.quotes.get(quoteID);
					var li = document.createElement('li');
					ul.appendChild(li);
					li.className = (i % 2 ? 'container-1' : 'container-2');
					li.id = 'quote-' + quoteID;
					
					// buttons
					var divIcon = document.createElement('div');
					li.appendChild(divIcon);
					divIcon.className = 'buttons';
					
					// checkbox
					var divInput = document.createElement('div');
					li.appendChild(divInput);
					divInput.className = 'containerIcon';
					
					var checkbox = new Element('input', { 'type': 'checkbox' });
					divInput.appendChild(checkbox);
					checkbox.name = 'usedQuotes[]';
					checkbox.value = quoteID;
					checkbox.id = 'usedQuotes-'+quoteID;
					checkbox.observe('change', this.showSelectedQuotesMessage.bind(this));
					if (quote.used == 1) checkbox.checked = true;
					
					var button1 = document.createElement('img');
					divIcon.appendChild(button1);
					button1.src = this.options.imgInsertSrc;
					button1.className = 'pointer';
					button1.onclick = this.getInsertQuoteFunction(quoteID);
					button1.title = this.options.langInsertQuote;
					
					var button2 = document.createElement('img');
					divIcon.appendChild(button2);
					button2.src = this.options.imgRemoveSrc;
					button2.className = 'pointer';
					button2.onclick = this.getRemoveQuoteFunction(quoteID);
					button2.title = this.options.langRemoveQuote;
					
					// text
					var divContent = document.createElement('div');
					li.appendChild(divContent);
					divContent.className = 'containerContent';
					
					var label = new Element('label', { 'for': 'usedQuotes-' + quoteID });
					divContent.appendChild(label);
					label.appendChild(document.createTextNode((quote.text.length > 300 ? quote.text.substring(0, 297) + '...' : quote.text)));
				}
			}
			else {
				div.appendChild(document.createTextNode(this.options.langNoQuotes));
			}
			this.showSelectedQuotesMessage();
		}
	},
	
	/**
	 * Gets the function for inserting quotes.
	 */
	getInsertQuoteFunction: function(id) {
		var multiQuoteManager = this;
		return function() {
			multiQuoteManager.insertQuote.apply(multiQuoteManager, [id]);
		}
	},
	
	/**
	 * Gets the function for removing quotes.
	 */
	getRemoveQuoteFunction: function(id) {
		var multiQuoteManager = this;
		return function() {
			multiQuoteManager.removeQuote.apply(multiQuoteManager, [id]);
		}
	},
	
	/**
	 * Stores a quote.
	 */
	storeQuote: function(quoteID, objectType, objectID, text, author, url) {
		quotes.set(quoteID, {
			quoteID: quoteID,
			objectID: objectID,
			objectType: objectType,
			author: author,
			url: url,
			text: text,
			used: 1
		});
		
		this.insertQuote(quoteID);
	},
	
	/**
	 * Inserts a quote in the message.
	 */
	insertQuote: function(id) {
		// insert message
		var quote = this.quotes.get(id);
		WysiwygInsert('bbcode', '[quote' + ((quote.author || quote.url) ? "='" + (quote.author ? quote.author.replace(/'/g, "\\\'") : '') + "'" + (quote.url ? ",'" + quote.url + "'" : '') : '') + ']' + quote.text + '[/quote]\n');
		
		var checkbox = document.getElementById('usedQuotes-'+id);
		if (checkbox) checkbox.checked = true;
		this.showSelectedQuotesMessage();
	},
	
	/**
	 * Writes a short notification what happens with selected quotes
	 */
	showSelectedQuotesMessage: function(evt) {
		if (evt && evt.findElement('input')) {
			var checkbox = evt.findElement('input');
			var quote = this.quotes.get(checkbox.identify().sub('usedQuotes-', ''));
			quote.used = checkbox.checked ? true : false;
		}
		var count = 0;
		var div = $('multiQuoteList');
		div.select('input').each(function(input) {
			if (input.checked) count++;
		});
		if (count) {
			if ($('multiQuoteSelectedQuotes')) {
				$('multiQuoteSelectedQuotes').down('div').update(eval(this.options.langSelectedQuotes));
			}
			else {
				//var div = new Element('div', {id: 'multiQuoteSelectedQuotes'}).addClassName('buttonBar').addClassName('content').update();
				div.insert('<div class="buttonBar" id="multiQuoteSelectedQuotes"><div style="padding-top: 10px">' + eval(this.options.langSelectedQuotes) + '</div></div>');
			}
		} else {
			if ($('multiQuoteSelectedQuotes')) $('multiQuoteSelectedQuotes').remove();
		}
	},
	
	/**
	 * Removes a quote from storage.
	 */
	removeQuote: function(id) {
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openPost('index.php?action=MessageQuoteRemove&t='+SECURITY_TOKEN+SID_ARG_2ND, 'quoteID='+id);
		this.quotes.unset(id);
		if ($('quote-' + id)) $('quote-' + id).fade({ duration: 0.5, afterFinish: this.showQuotes.bind(this) });
	},
	
	/**
	 * Removes all quotes of a message.
	 */
	removeQuotes : function(objectType, objectID) {
		var quoteIDs = this.quotes.keys();
		for (var i = 0; i < quoteIDs.length; i++) {
			var quoteID = quoteIDs[i];
			var quote = this.quotes.get(quoteID);
			
			if (quote.objectID == objectID && quote.objectType == objectType) {
				this.quotes.unset(quoteID);
			}
		}
	},
	
	/**
	 * Inserts all quotes of a parent element in the message.
	 */
	insertParentQuotes: function(objectType, parentID) {
		var quoteIDs = this.quotes.keys();
		for (var i = 0; i < quoteIDs.length; i++) {
			var quoteID = quoteIDs[i];
			var quote = this.quotes.get(quoteID);
			
			if (quote.parentID == parentID && quote.objectType == objectType) {
				quote.used = 1;
				this.insertQuote(quoteID);
			}
		}
		this.showSelectedQuotesMessage();
	}
});