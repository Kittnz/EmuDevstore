<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiQuote.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var quoteData = new Hash();
	document.observe("dom:loaded", function() {
		multiQuoteObj = new MultiQuote(quoteData, '{if $formURL|isset}{@$formURL|encodeJS}{/if}', {
			langButtonQuote			: '{lang}wcf.multiQuote.button.quote{/lang}',
			langButtonQuoteMultiple		: '{lang}wcf.multiQuote.button.quoteMultiple{/lang}',
			langQuoteDirectly		: '{lang}wcf.multiQuote.quote.quoteDirectly{/lang}',
			langMarkToQuote			: '{lang}wcf.multiQuote.quote.markToQuote{/lang}',
			langQuoteTextDirectly		: '{lang}wcf.multiQuote.quote.quoteTextDirectly{/lang}',
			langMarkTextToQuote		: '{lang}wcf.multiQuote.quote.markTextToQuote{/lang}',
			langRemoveQuotes		: '{lang}wcf.multiQuote.quote.removeQuotes{/lang}',
			langQuoteParagraphDirectly	: '{lang}wcf.multiQuote.quote.quoteParagraphDirectly{/lang}',
			langMarkParagraphToQuote	: '{lang}wcf.multiQuote.quote.markParagraphToQuote{/lang}',
			langQuoteParagraphsDirectly	: '{lang}wcf.multiQuote.quote.quoteParagraphsDirectly{/lang}',
			langMarkParagraphsToQuote	: '{lang}wcf.multiQuote.quote.markParagraphsToQuote{/lang}',
			iconMessageQuoteOptions		: '{icon}messageQuoteOptionsS.png{/icon}'
		});
	});
//]]>
</script>