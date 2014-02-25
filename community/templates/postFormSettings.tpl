{if $this->user->userID}
	<div class="formField">
		<label><input type="checkbox" name="subscription" value="1" {if $subscription == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.subscription{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.subscription.description{/lang}</p>
	</div>
{/if}
{if $board->getModeratorPermission('canCloseThread')}
	<div class="formField">
		<label><input type="checkbox" name="closeThread" value="1" {if $closeThread == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.closeThread{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.closeThread.description{/lang}</p>
	</div>
{/if}

{if $this->request->form == 'PostAddForm' && MODULE_THREAD_MARKING_AS_DONE && $board->enableMarkingAsDone && !$thread->isDone && ($board->getModeratorPermission('canMarkAsDoneThread') || ($this->user->userID && $this->user->userID == $thread->userID && $board->getPermission('canMarkAsDoneOwnThread')))}
	<div class="formField">
		<label><input type="checkbox" name="markAsDone" value="1" {if $markAsDone == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.markAsDone{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.markAsDone.description{/lang}</p>
	</div>
{/if}

{if $hideEditNote|isset && $board->getPermission('canHideEditNote')}
	<div class="formField">
		<label><input type="checkbox" name="hideEditNote" value="1" {if $hideEditNote == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.hideEditNote{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.hideEditNote.description{/lang}</p>
	</div>
{/if}
{if $this->request->form == 'ThreadAddForm' && $board->getModeratorPermission('canEnableThread')}
	<div class="formField">
		<label><input type="checkbox" name="disableThread" value="1" {if $disableThread == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.disableThread{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.disableThread.description{/lang}</p>
	</div>
{/if}
{if $this->request->form == 'PostAddForm' && $board->getModeratorPermission('canEnablePost')}
	<div class="formField">
		<label><input type="checkbox" name="disablePost" value="1" {if $disablePost == 1}checked="checked" {/if}/> {lang}wbb.threadAdd.settings.disablePost{/lang}</label>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wbb.threadAdd.settings.disablePost.description{/lang}</p>
	</div>
{/if}