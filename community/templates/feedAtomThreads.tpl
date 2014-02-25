<?xml version="1.0" encoding="{@CHARSET}"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>{lang}wbb.board.feed.title{/lang}</title>
	<id>{@PAGE_URL}/</id>
	<updated>{@'c'|gmdate:TIME_NOW}</updated>
	<link href="{@PAGE_URL}/" />
	<generator uri="http://www.woltlab.com/" version="{@PACKAGE_VERSION}">
		WoltLab Burning Board
	</generator>
	<subtitle>{lang}wbb.board.feed.description{/lang}</subtitle>
	
	{foreach from=$threads item=$thread}
		<entry>
			<title>{if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic}</title>
			<id>{@PAGE_URL}/index.php?page=Thread&amp;threadID={@$thread->threadID}</id>
			<updated>{@'c'|gmdate:$thread->time}</updated>
			<author>
				<name>{$thread->username}</name>
			</author>
			<content type="html"><![CDATA[{@$thread->getFormattedMessage()}]]></content>
			<link href="{@PAGE_URL}/index.php?page=Thread&amp;threadID={@$thread->threadID}" />
		</entry>
	{/foreach}
</feed>