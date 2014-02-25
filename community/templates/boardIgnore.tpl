{include file="documentHeader"}
<head>
	<title>{lang}wbb.board.ignoredBoards{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	{capture append=userMessages}
		{if $success|isset}
			<p class="success">{lang}wbb.board.ignoredBoards.edit.success{/lang}</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=BoardIgnore">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.board.ignoredBoards{/lang}</h3>
				
				<ol class="itemList">
					{foreach from=$boards item=child}
						{* define *}
						{assign var="board" value=$child.board}
						{assign var="depth" value=$child.depth}
						
						<li>
							<h{if $depth > 2}5{else}{@$depth+3}{/if} class="itemListTitle{if $board->isCategory()} itemListCategory{/if}">
								<label>
									<img src="{icon}{@$board->getIconName()}S.png{/icon}" alt="" />
									<input type="checkbox" name="unignoredBoardIDArray[]" value="{@$board->boardID}" {if !$board->ignorable || $board->boardID|in_array:$unignoredBoardIDArray}checked="checked" {/if}{if !$board->ignorable}disabled="disabled" {/if}/> {lang}{$board->title}{/lang}
								</label>
							</h{if $depth > 2}5{else}{@$depth+3}{/if}>
						
						{if $child.hasChildren}<ol>{else}</li>{/if}
						{if $child.openParents > 0}{@"</ol></li>"|str_repeat:$child.openParents}{/if}
					{/foreach}
				</ol>
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
		</div>
	</form>
</div>

{include file='footer' sandbox=false}
</body>
</html>