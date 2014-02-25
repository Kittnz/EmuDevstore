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
	
	{foreach from=$posts item=$post}
		<entry>
			<title>{$post->subject}</title>
			<id>{@PAGE_URL}/index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}</id>
			<updated>{@'c'|gmdate:$post->time}</updated>
			<author>
				<name>{$post->username}</name>
			</author>
			<content type="html"><![CDATA[{@$post->getFormattedMessage()}]]></content>
			<link href="{@PAGE_URL}/index.php?page=Thread&amp;postID={@$post->postID}#post{@$post->postID}" />
		</entry>
	{/foreach}
</feed>