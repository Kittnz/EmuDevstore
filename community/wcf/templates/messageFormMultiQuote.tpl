<div id="multiQuote" class="hidden">
	<fieldset class="noJavaScript">
		<legend class="noJavaScript">{lang}wcf.multiQuote.title{/lang}</legend>
		
		<div id="multiQuoteList"></div>
	</fieldset>
</div>

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiQuoteManager.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// show tab
	tabbedPane.addTab('multiQuote', false);
	var showQuoteTab = true;
	
	// quotes
	var quotes = new Hash();
	{foreach from=$quotes key=quoteID item=quote}
		quotes.set('{@$quoteID}', {
			quoteID: '{@$quoteID}',
			objectID: {@$quote.objectID},
			objectType: '{@$quote.objectType|encodeJS}',
			parentID: {@$quote.parentID},
			author: '{@$quote.author|encodeJS}',
			url: '{@$quote.url|encodeJS}',
			text: '{@$quote.text|encodeJS}',
			used: {@$quote.used}
		});
	{/foreach}
	document.observe("dom:loaded", function() { }); // ie fix
	document.observe("dom:loaded", function() {
		multiQuoteManagerObj = new MultiQuoteManager(quotes, {
			langInsertQuote: '{lang}wcf.multiQuote.quote.insertQuote{/lang}',
			langRemoveQuote: '{lang}wcf.multiQuote.quote.removeQuote{/lang}',
			langNoQuotes: '{lang}wcf.multiQuote.quote.noQuotes{/lang}',
			langSelectedQuotes: '{lang}wcf.multiQuote.quote.selectedQuotes{/lang}',
			imgInsertSrc: '{icon}messageS.png{/icon}',
			imgRemoveSrc: '{icon}deleteS.png{/icon}'
		});

		if (typeof(multiQuoteObj) != 'undefined') {
			multiQuoteObj.setManager(multiQuoteManagerObj);
		}

		if (!$('multiQuoteList').down('li') && !$('multiQuoteTab').hasClassName('activeTabMenu')) {
			showQuoteTab = false;
			//$('multiQuoteTab').setOpacity('0.5');
			$('multiQuoteTab').addClassName('disabled');
			var a = $('multiQuoteTab').down('a');
			if (Prototype.Browser.IE) a.setOpacity('0.5');
			a.onclick = '';
			a.setStyle({ 'cursor' : 'default' });
			a.observe('click', function(evt) {
				if (showQuoteTab) {
					tabbedPane.openTab('multiQuote');
				}
			});
		}
	});
	
	document.observe('wcf:MultiQuote:saveQuote', function() {
		showQuoteTab = true;
		$('multiQuoteTab').removeClassName('disabled');
		$('multiQuoteTab').down('a').setStyle({ 'cursor' : 'pointer' });
		if (Prototype.Browser.IE) $('multiQuoteTab').down('a').setOpacity('1');
	});
	//]]>
</script>