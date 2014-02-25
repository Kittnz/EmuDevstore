<?xml version="1.0" encoding="{@CHARSET}"?>
<rss version="2.0">
	<channel>
		<title>{lang}wbb.board.feed.title{/lang}</title>
		<link>{@PAGE_URL}/</link>
		<description>{lang}wbb.board.feed.description{/lang}</description>
		
		<pubDate>{@'r'|gmdate:TIME_NOW}</pubDate>
		<lastBuildDate>{@'r'|gmdate:TIME_NOW}</lastBuildDate>
		<generator>WoltLab Burning Board {@PACKAGE_VERSION}</generator>
		<ttl>60</ttl>
		
		{foreach from=$threads item=$thread}
			<item>
				<title>{if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic}</title>
				<author>{$thread->username}</author>
				<link>{@PAGE_URL}/index.php?page=Thread&amp;threadID={@$thread->threadID}</link>
				<guid>{@PAGE_URL}/index.php?page=Thread&amp;threadID={@$thread->threadID}</guid>
				<pubDate>{@'r'|gmdate:$thread->time}</pubDate>
				<description><![CDATA[{@$thread->getFormattedMessage()}]]></description>
			</item>
		{/foreach}
	</channel>
</rss>