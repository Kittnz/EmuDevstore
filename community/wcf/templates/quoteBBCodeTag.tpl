<blockquote class="quoteBox container-4"{if $quoteLink} cite="{$quoteLink}"{/if}>
	<div class="quoteHeader">
		<h3><img src="{icon}quoteS.png{/icon}" alt="" />
		{if $quoteAuthor}
			{if $quoteLink}
				<a href="{@$quoteLink}">{lang}wcf.bbcode.quote.title{/lang}</a>
			{else}
				{lang}wcf.bbcode.quote.title{/lang}
			{/if}
		{else}
			{lang}wcf.bbcode.quote.title{/lang}
		{/if}
		</h3>
	</div>
	<div class="quoteBody">
		{@$content}
	</div>
</blockquote>
