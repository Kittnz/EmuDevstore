<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/ThreadListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/PostListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/StringUtil.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// data array
	var threadData = new Hash();

	// constants
	var ENABLE_RECYCLE_BIN = {@THREAD_ENABLE_RECYCLE_BIN};

	// ids
	var boardID = {if $board|isset}{@$board->boardID}{else}0{/if};
	var threadID = {if $thread|isset}{@$thread->threadID}{else}0{/if};
	
	// prefixes
	var prefixes = new Object();
	{if $board|isset}
		{foreach from=$board->getPrefixOptions() key=prefixName item=prefixTitle}
			prefixes['{@$prefixName|encodeJS}'] = '{@$prefixTitle|encodeJS}';
		{/foreach}
		var prefixRequired = {@$board->prefixRequired};
	{/if}
	
	// marking as done
	var enableMarkingAsDone = {if MODULE_THREAD_MARKING_AS_DONE && $board|isset}{@$board->enableMarkingAsDone}{else}0{/if}

	// language
	language['wcf.global.button.mark']			= '{lang}wcf.global.button.mark{/lang}';
	language['wcf.global.button.unmark'] 			= '{lang}wcf.global.button.unmark{/lang}';
	language['wcf.global.button.delete'] 			= '{lang}wcf.global.button.delete{/lang}';
	language['wcf.global.button.deleteCompletely'] 		= '{lang}wcf.global.button.deleteCompletely{/lang}';
	language['wbb.board.threads.button.recover'] 		= '{lang}wbb.board.threads.button.recover{/lang}';
	language['wbb.board.threads.button.enable'] 		= '{lang}wbb.board.threads.button.enable{/lang}';
	language['wbb.board.threads.button.disable'] 		= '{lang}wbb.board.threads.button.disable{/lang}';
	language['wbb.board.threads.button.open'] 		= '{lang}wbb.board.threads.button.open{/lang}';
	language['wbb.board.threads.button.close'] 		= '{lang}wbb.board.threads.button.close{/lang}';
	language['wbb.board.threads.button.editTitle'] 		= '{lang}wbb.board.threads.button.editTitle{/lang}';
	language['wbb.board.threads.button.editPrefix'] 	= '{lang}wbb.board.threads.button.editPrefix{/lang}';
	language['wbb.board.threads.markedThreads'] 		= '{lang}wbb.board.threads.markedThreads{/lang}';
	language['wbb.board.threads.delete.sure'] 		= '{lang}wbb.board.threads.delete.sure{/lang}';
	language['wbb.board.threads.deleteCompletely.sure'] 	= '{lang}wbb.board.threads.deleteCompletely.sure{/lang}';
	language['wbb.board.threads.deleteMarked.sure'] 	= '{lang}wbb.board.threads.deleteMarked.sure{/lang}';
	language['wbb.board.threads.button.move'] 		= '{lang}wbb.board.threads.button.move{/lang}';
	language['wbb.board.threads.button.moveWithLink'] 	= '{lang}wbb.board.threads.button.moveWithLink{/lang}';
	language['wbb.board.threads.button.copy'] 		= '{lang}wbb.board.threads.button.copy{/lang}';
	language['wbb.board.threads.button.moveAndInsert'] 	= '{lang}wbb.board.threads.button.moveAndInsert{/lang}';
	language['wbb.board.threads.button.copyAndInsert'] 	= '{lang}wbb.board.threads.button.copyAndInsert{/lang}';
	language['wbb.board.threads.markedPosts'] 		= '{lang}wbb.board.threads.markedPosts{/lang}';
	language['wbb.board.posts.delete.sure'] 		= '{lang}wbb.board.posts.delete.sure{/lang}';
	language['wbb.board.posts.deleteCompletely.sure'] 	= '{lang}wbb.board.posts.deleteCompletely.sure{/lang}';
	language['wbb.board.posts.deleteMarked.sure'] 		= '{lang}wbb.board.posts.deleteMarked.sure{/lang}';
	language['wbb.board.threads.button.merge'] 		= '{lang}wbb.board.threads.button.merge{/lang}';
	language['wbb.board.threads.button.copyAndMerge'] 	= '{lang}wbb.board.threads.button.copyAndMerge{/lang}';
	language['wbb.board.threads.button.deleteLink'] 	= '{lang}wbb.board.threads.button.deleteLink{/lang}';
	language['wbb.board.threads.deleteLink.sure'] 		= '{lang}wbb.board.threads.deleteLink.sure{/lang}';
	language['wbb.board.threads.button.editPost'] 		= '{lang}wbb.board.threads.button.editPost{/lang}';
	language['wbb.board.threads.button.stick'] 		= '{lang}wbb.board.threads.button.stick{/lang}';
	language['wbb.board.threads.button.unstick'] 		= '{lang}wbb.board.threads.button.unstick{/lang}';
	language['wbb.board.threads.button.showMarked'] 	= '{lang}wbb.board.threads.button.showMarked{/lang}';
	language['wbb.board.threads.button.removeReport'] 	= '{lang}wbb.board.threads.button.removeReport{/lang}';
	language['wbb.board.posts.removeReport.sure'] 		= '{lang}wbb.board.posts.removeReport.sure{/lang}';
	language['wbb.board.posts.removeReports.sure'] 		= '{lang}wbb.board.posts.removeReports.sure{/lang}';
	language['wbb.board.posts.merge.sure']			= '{lang}wbb.board.posts.merge.sure{/lang}';
	language['wbb.board.posts.button.merge'] 		= '{lang}wbb.board.posts.button.merge{/lang}';
	language['wbb.board.posts.delete.reason'] 		= '{lang}wbb.board.posts.delete.reason{/lang}';
	language['wbb.board.threads.delete.reason'] 		= '{lang}wbb.board.threads.delete.reason{/lang}';
	language['wbb.board.posts.deleteMarked.reason'] 	= '{lang}wbb.board.posts.deleteMarked.reason{/lang}';
	language['wbb.board.threads.deleteMarked.reason'] 	= '{lang}wbb.board.threads.deleteMarked.reason{/lang}';
	language['wbb.board.threads.button.undone'] 		= '{lang}wbb.board.threads.button.undone{/lang}';
	language['wbb.board.threads.button.done'] 		= '{lang}wbb.board.threads.button.done{/lang}';
	language['wbb.board.threads.done'] 			= '{lang}wbb.board.threads.done{/lang}';
	language['wbb.board.threads.undone'] 			= '{lang}wbb.board.threads.undone{/lang}';
	language['wcf.global.button.submit']			= '{lang}wcf.global.button.submit{/lang}';
	language['wcf.global.button.reset']			= '{lang}wcf.global.button.reset{/lang}';
	
	// permissions
	var permissions = new Object();
	
	// post editing
	permissions['canDeletePost'] = {@$permissions.canDeletePost};
	permissions['canReadDeletedPost'] = {@$permissions.canReadDeletedPost};
	permissions['canDeletePostCompletely'] = {@$permissions.canDeletePostCompletely};
	permissions['canClosePost'] = {@$permissions.canClosePost};
	permissions['canEnablePost'] = {@$permissions.canEnablePost};
	permissions['canMarkPost'] = {@$permissions.canMarkPost};
	permissions['canMovePost'] = {@$permissions.canMovePost};
	permissions['canCopyPost'] = {@$permissions.canCopyPost};
	permissions['canEditPost'] = {@$permissions.canEditPost};
	permissions['canMergePost'] = {@$permissions.canMergePost};
	
	// thread editing
	permissions['canDeleteThread'] = {@$permissions.canDeleteThread};
	permissions['canReadDeletedThread'] = {@$permissions.canReadDeletedThread};
	permissions['canDeleteThreadCompletely'] = {@$permissions.canDeleteThreadCompletely};
	permissions['canCloseThread'] = {@$permissions.canCloseThread};
	permissions['canEnableThread'] = {@$permissions.canEnableThread};
	permissions['canMarkThread'] = {@$permissions.canMarkThread};
	permissions['canMoveThread'] = {@$permissions.canMoveThread};
	permissions['canCopyThread'] = {@$permissions.canCopyThread};
	permissions['canPinThread'] = {@$permissions.canPinThread};
	permissions['canMarkAsDoneThread'] = {@$permissions.canMarkAsDoneThread};

	// init
	onloadEvents.push(function() {
		threadListEdit = new ThreadListEdit(threadData, {@$markedThreads}, '{@$pageType}');
		postListEdit = new PostListEdit(postData, {@$markedPosts}, '{@$pageType}');
	});
	//]]>
</script>