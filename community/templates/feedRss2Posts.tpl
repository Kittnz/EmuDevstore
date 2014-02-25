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
		
		{foreach from=$posts item=$post}
			<item>
				<title>{$post->subject}</title>
				<author>{$post->username}</author>
				<link>{@PAGE_URL}/index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}</link>
				<guid>{@PAGE_URL}/index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}</guid>
				<pubDate>{@'r'|gmdate:$post->time}</pubDate>
				<description><![CDATA[{@$post->getFormattedMessage()}]]></description>
			</item>
		{/foreach}
	</channel>
</rss>