{if $boards|count > 0}
	<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/BoardMarkAsRead.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var boards = new Hash();
		document.observe("dom:loaded", function() {
			new BoardMarkAsRead(boards);
		});
	//]]>
	</script>
	
	{cycle name='boardlistCycle' values='1,2' advance=false print=false}
	<ul id="boardlist">
		{foreach from=$boards item=child}
			{* define *}
			{assign var="depth" value=$child.depth}
			{assign var="open" value=$child.open}
			{assign var="hasChildren" value=$child.hasChildren}
			{assign var="openParents" value=$child.openParents}
			{assign var="board" value=$child.board}
			{assign var="boardID" value=$board->boardID}
			{counter assign=boardNo print=false}
			{if $board->isBoard()}
				{* board *}

				<li{if $depth == 1} class="board border"{/if}>
					<div class="boardlistInner container-{cycle name='boardlistCycle'} board{@$boardID}"{if $board->imageShowAsBackground}{if $board->image || $newPosts.$boardID && $board->imageNew} style="background-image: url({if $newPosts.$boardID && $board->imageNew}{$board->imageNew}{else}{$board->image}{/if}); background-repeat: {$board->imageBackgroundRepeat}"{/if}{/if}>
						<div class="boardlistTitle{if BOARD_LIST_ENABLE_LAST_POST && BOARD_LIST_ENABLE_STATS} boardlistCols-3{else}{if BOARD_LIST_ENABLE_LAST_POST || BOARD_LIST_ENABLE_STATS} boardlistCols-2{/if}{/if}">
							<div class="containerIcon">
								<img id="boardIcon{@$boardNo}" src="{if $newPosts.$boardID && $board->imageNew && !$board->imageShowAsBackground}{$board->imageNew}{elseif $board->image && !$board->imageShowAsBackground}{$board->image}{else}{icon}{@$board->getIconName()}{if $newPosts.$boardID}New{/if}M.png{/icon}{/if}" alt="" {if $newPosts.$boardID}title="{lang}wbb.board.markAsReadByDoubleClick{/lang}" {/if}/>
							</div>
							
							<div class="containerContent">
								{if $depth > 3}<h6 class="boardTitle">{else}<h{@$depth+2} class="boardTitle">{/if}
									<a id="boardLink{@$boardNo}" {if $newPosts.$boardID}class="new" {/if}href="index.php?page=Board&amp;boardID={@$boardID}{@SID_ARG_2ND}">{lang}{$board->title}{/lang}{if $unreadThreadsCount.$boardID|isset}<span>&nbsp;({#$unreadThreadsCount.$boardID})</span>{/if}</a>
								{if $depth > 3}</h6>{else}</h{@$depth+2}>{/if}
								{if $newPosts.$boardID}
									<script type="text/javascript">
										//<![CDATA[
										boards.set({@$boardNo}, {
											'boardNo': {@$boardNo},
											'boardID': {@$boardID},
											'icon': '{if $board->image && !$board->imageShowAsBackground}{$board->image}{else}{icon}{@$board->getIconName()}M.png{/icon}{/if}'
										});
										//]]>
									</script>
								{/if}
								
								{if $board->description}
									<p class="boardlistDescription">
										{lang}{if $board->allowDescriptionHtml}{@$board->description}{else}{$board->description}{/if}{/lang}
									</p>
								{/if}
								
								{if $subBoards.$boardID|isset}
									<div class="boardlistSubboards">
										<ul>{foreach name='subBoards' from=$subBoards.$boardID item=subBoard}{assign var="subBoardID" value=$subBoard->boardID}{counter assign=boardNo print=false}<li{if $tpl.foreach.subBoards.last} class="last"{/if}>{if $depth > 4}<h6>{else}<h{@$depth+3}>{/if}<img id="boardIcon{@$boardNo}" src="{icon}{if $subBoard->isBoard()}board{if $newPosts.$subBoardID}New{/if}{elseif $subBoard->isCategory()}category{else}boardRedirect{/if}S.png{/icon}" alt="" {if $subBoard->isBoard() && $newPosts.$subBoardID}title="{lang}wbb.board.markAsReadByDoubleClick{/lang}" {/if}/>{*
														*}&nbsp;<a id="boardLink{@$boardNo}" {if $newPosts.$subBoardID}class="new" {/if}{if $subBoard->isExternalLink()}class="externalURL" {/if}href="index.php?page=Board&amp;boardID={@$subBoardID}{@SID_ARG_2ND}">{lang}{$subBoard->title}{/lang}{if $unreadThreadsCount.$subBoardID|isset} <span>({#$unreadThreadsCount.$subBoardID})</span>{/if}</a>{if $depth > 4}</h6>{else}</h{@$depth+3}>{/if}{*
													*}{if $newPosts.$subBoardID}<script type="text/javascript">
														//<![CDATA[
														boards.set({@$boardNo}, {
															'boardNo': {@$boardNo},
															'boardID': {@$subBoardID},
															'icon': '{icon}{@$subBoard->getIconName()}S.png{/icon}'
														});
														//]]>
													</script>{/if}</li>{/foreach}</ul>
									</div>
								{/if}
								
								{if $boardUsersOnline.$boardID.users|isset || $boardUsersOnline.$boardID.guests|isset}
									<p class="boardlistUsersOnline">
										<img src="{icon}usersS.png{/icon}" alt="" />
										{if $boardUsersOnline.$boardID.users|isset}
											{implode from=$boardUsersOnline.$boardID.users item=userOnline}<a href="index.php?page=User&amp;userID={@$userOnline.userID}{@SID_ARG_2ND}">{@$userOnline.username}</a>{/implode}
										{/if}
										{if $boardUsersOnline.$boardID.guests|isset}
											{lang}wbb.index.boardUsersOnline.guests{/lang}
										{/if}
									</p>
								{/if}
								
								{if $moderators.$boardID|isset}
									<p class="moderators">
										<img src="{icon}moderatorS.png{/icon}" alt="" />
										{implode from=$moderators.$boardID item=moderator}{if $moderator->userID}<a href="index.php?page=User&amp;userID={@$moderator->userID}{@SID_ARG_2ND}">{$moderator}</a>{else}{$moderator}{/if}{/implode}
									</p>
								{/if}
								
								{if $child.additionalBoxes|isset}{@$child.additionalBoxes}{/if}
							</div>
						</div>
						
						{if $lastPosts.$boardID|isset}
							<div class="boardlistLastPost">								
								<div class="containerIconSmall"><a href="index.php?page=Thread&amp;threadID={@$lastPosts.$boardID->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a></div>
								<div class="containerContentSmall">
									<p>
										<span class="prefix"><strong>{lang}{$lastPosts.$boardID->prefix}{/lang}</strong></span>
										<a href="index.php?page=Thread&amp;threadID={@$lastPosts.$boardID->threadID}&amp;action=firstNew{@SID_ARG_2ND}">{$lastPosts.$boardID->topic}</a>
									</p>
									<p>{lang}wbb.board.threads.postBy{/lang}
										{if $lastPosts.$boardID->lastPosterID != 0}
											<a href="index.php?page=User&amp;userID={@$lastPosts.$boardID->lastPosterID}{@SID_ARG_2ND}">{$lastPosts.$boardID->lastPoster}</a>
										{else}
											{$lastPosts.$boardID->lastPoster}
										{/if}
										<span class="light">({@$lastPosts.$boardID->lastPostTime|shorttime})</span>
									</p>
								</div>
							</div>
						{/if}
						
						{if $boardStats.$boardID|isset}
							<div class="boardlistStats">
								<dl>
									<dt>{lang}wbb.board.stats.threads{/lang}</dt>
									<dd>{#$boardStats[$boardID]['threads']}</dd>
									<dt>{lang}wbb.board.stats.posts{/lang}</dt>
									<dd>{#$boardStats[$boardID]['posts']}</dd>
								</dl>
							</div>
						{/if}
						<!--[if IE 7]><span> </span><![endif]-->
					</div>
			{/if}
			
			{if $board->isCategory()}
				{* category *}
				{cycle name='boardlistCycle' advance=false print=false reset=true}
				<li{if $depth == 1} class="category border"{/if}>
					<div class="containerHead boardlistInner board{@$boardID}"{if $board->imageShowAsBackground}{if $board->image || $newPosts.$boardID && $board->imageNew} style="background-image: url({if $newPosts.$boardID && $board->imageNew}{$board->imageNew}{else}{$board->image}{/if}); background-repeat: {$board->imageBackgroundRepeat}"{/if}{/if}>
						<div class="boardlistTitle">
							<div class="containerIcon">
								{if $open}
									{capture assign=showCategoryTitle}{lang}wbb.index.showCat{/lang}{/capture}
									{capture assign=hideCategoryTitle}{lang}wbb.index.hideCat{/lang}{/capture}
									<a href="{$selfLink}&amp;closeCategory={@$boardID}{@SID_ARG_2ND}#boardLink{@$boardNo}" onclick="return !openList('category{@$boardID}', { save: true, openTitle: '{@$showCategoryTitle|encodeJS}', closeTitle: '{@$hideCategoryTitle|encodeJS}' })"><img id="category{@$boardID}Image" src="{icon}minusS.png{/icon}" alt="" title="{lang}wbb.index.hideCat{/lang}" /></a>
								{else}
									<a href="{$selfLink}&amp;openCategory={@$boardID}{@SID_ARG_2ND}#boardLink{@$boardNo}"><img src="{icon}plusS.png{/icon}" alt="" title="{lang}wbb.index.showCat{/lang}" /></a>
								{/if}
							</div>
							<div class="containerContent">
								{if $depth > 3}<h6 class="boardTitle">{else}<h{@$depth+2} class="boardTitle">{/if}
									<a id="boardLink{@$boardNo}" {if $newPosts.$boardID}class="new" {/if}href="index.php?page=Board&amp;boardID={@$boardID}{@SID_ARG_2ND}">{lang}{$board->title}{/lang}{if $unreadThreadsCount.$boardID|isset} ({#$unreadThreadsCount.$boardID}){/if}</a>
								{if $depth > 3}</h6>{else}</h{@$depth+2}>{/if}
								{if $board->description}
									<p class="boardlistDescription">
										{lang}{if $board->allowDescriptionHtml}{@$board->description}{else}{$board->description}{/if}{/lang}
									</p>
								{/if}
								
								{if $subBoards.$boardID|isset}
									<div class="boardlistSubboards">
										<ul>{foreach name='subBoards' from=$subBoards.$boardID item=subBoard}{assign var="subBoardID" value=$subBoard->boardID}{counter assign=boardNo print=false}<li{if $tpl.foreach.subBoards.last} class="last"{/if}>{if $depth > 4}<h6>{else}<h{@$depth+3}>{/if}<img id="boardIcon{@$boardNo}" src="{icon}{if $subBoard->isBoard()}board{if $newPosts.$subBoardID}New{/if}{elseif $subBoard->isCategory()}category{else}boardRedirect{/if}S.png{/icon}" alt="" {if $subBoard->isBoard() && $newPosts.$subBoardID}title="{lang}wbb.board.markAsReadByDoubleClick{/lang}" {/if}/>{*
															*}&nbsp;<a id="boardLink{@$boardNo}" {if $newPosts.$subBoardID}class="new" {/if}{if $subBoard->isExternalLink()}class="externalURL" {/if}href="index.php?page=Board&amp;boardID={@$subBoardID}{@SID_ARG_2ND}">{lang}{$subBoard->title}{/lang}{if $unreadThreadsCount.$subBoardID|isset} <span>({#$unreadThreadsCount.$subBoardID})</span>{/if}</a>{if $depth > 4}</h6>{else}</h{@$depth+3}>{/if}{*
														*}{if $newPosts.$subBoardID}<script type="text/javascript">
															//<![CDATA[
															boards.set({@$boardNo}, {
																'boardNo': {@$boardNo},
																'boardID': {@$subBoardID},
																'icon': '{icon}{@$subBoard->getIconName()}S.png{/icon}'
															});
															//]]>
														</script>{/if}</li>{/foreach}</ul>
									</div>
								{/if}
								
								{if $child.additionalBoxes|isset}{@$child.additionalBoxes}{/if}
							</div>
						</div>
					</div>
			{/if}
			
			{if $board->isExternalLink()}	
				{* external url *}
				<li{if $depth == 1} class="link border"{/if}>
					<div class="container-{cycle name='boardlistCycle'} boardlistInner board{@$boardID}"{if $board->imageShowAsBackground && $board->image} style="background-image: url({$board->image}); background-repeat: {$board->imageBackgroundRepeat}"{/if}>
						<div class="boardlistTitle{if BOARD_LIST_ENABLE_LAST_POST && BOARD_LIST_ENABLE_STATS} boardlistCols-3{else}{if BOARD_LIST_ENABLE_LAST_POST || BOARD_LIST_ENABLE_STATS} boardlistCols-2{/if}{/if}">
							<div class="containerIcon">
								<img src="{if $board->image && !$board->imageShowAsBackground}{$board->image}{else}{icon}boardRedirectM.png{/icon}{/if}" alt="" />
							</div>
							<div class="containerContent">
								{if $depth > 3}<h6 class="boardTitle">{else}<h{@$depth+2} class="boardTitle">{/if}
									<a href="index.php?page=Board&amp;boardID={@$boardID}{@SID_ARG_2ND}" class="externalURL">{lang}{$board->title}{/lang}</a>
								{if $depth > 3}</h6>{else}</h{@$depth+2}>{/if}
								
								{if $board->description}
									<p class="boardlistDescription">
										{lang}{if $board->allowDescriptionHtml}{@$board->description}{else}{$board->description}{/if}{/lang}
									</p>
								{/if}
								
								{if $child.additionalBoxes|isset}{@$child.additionalBoxes}{/if}
							</div>
						</div>
						
						{if $boardStats.$boardID|isset}
							<div class="boardlistStats">
								<dl>
									<dt>{lang}wbb.board.clicks{/lang}</dt>
									<dd>{#$board->getClicks()}</dd>
								</dl>
							</div>
						{/if}
					</div>
			{/if}
			
			{if $hasChildren}<ul id="category{@$boardID}">{else}</li>{/if}
			{if $openParents > 0}{@"</ul></li>"|str_repeat:$openParents}{/if}
		{/foreach}
	</ul>
{/if}