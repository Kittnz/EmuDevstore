<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Wysiwyg.class.js"></script>
<script type="text/javascript">
//<![CDATA[
// language
var language = new Object();
language['undo.desc'] = "{lang}wcf.wysiwyg.undo.desc{/lang}";language['redo.desc'] = "{lang}wcf.wysiwyg.redo.desc{/lang}";
language['b.desc'] = "{lang}wcf.wysiwyg.bold.desc{/lang}";language['i.desc'] = "{lang}wcf.wysiwyg.italic.desc{/lang}";language['u.desc'] = "{lang}wcf.wysiwyg.underline.desc{/lang}";language['s.desc'] = "{lang}wcf.wysiwyg.strikeThrough.desc{/lang}";
language['toolbar.focus'] = "{lang}wcf.wysiwyg.toolbar.focus{/lang}";
language['link.desc'] = "{lang}wcf.wysiwyg.link.desc{/lang}";language['link.insert.url'] = "{lang}wcf.wysiwyg.link.insert.url{/lang}";language['link.insert.url.optional']= "{lang}wcf.wysiwyg.link.insert.url.optional{/lang}";language['link.insert.name'] = "{lang}wcf.wysiwyg.link.insert.name{/lang}";language['unlink.desc'] = "{lang}wcf.wysiwyg.unlink.desc{/lang}";language['insertText'] = "{lang}wcf.wysiwyg.insertText{/lang}";
language['textAlignLeft.desc'] = "{lang}wcf.wysiwyg.textAlignLeft.desc{/lang}";language['textAlignCenter.desc'] = "{lang}wcf.wysiwyg.textAlignCenter.desc{/lang}";language['textAlignRight.desc'] = "{lang}wcf.wysiwyg.textAlignRight.desc{/lang}";language['textJustify.desc'] = "{lang}wcf.wysiwyg.textJustify.desc{/lang}";
language['bullist.desc'] = "{lang}wcf.wysiwyg.bullist.desc{/lang}";language['numlist.desc'] = "{lang}wcf.wysiwyg.numlist.desc{/lang}";
language['cut.desc'] = "{lang}wcf.wysiwyg.cut.desc{/lang}";language['copy.desc'] = "{lang}wcf.wysiwyg.copy.desc{/lang}";language['paste.desc'] = "{lang}wcf.wysiwyg.paste.desc{/lang}";
language['img.desc'] = "{lang}wcf.wysiwyg.image.desc{/lang}";language['image.insert'] = "{lang}wcf.wysiwyg.image.insert{/lang}";
language['color.desc'] = "{lang}wcf.wysiwyg.forecolor.desc{/lang}";language['fontsize.default'] = "{lang}wcf.wysiwyg.fontsize{/lang}";language['fontFamily.default'] = "{lang}wcf.wysiwyg.font.default{/lang}";
language['quotation.desc'] = "{lang}wcf.wysiwyg.quotation.desc{/lang}";language['quote.desc'] = "{lang}wcf.wysiwyg.quote.desc{/lang}";language['code.desc'] = "{lang}wcf.wysiwyg.code.desc{/lang}";
language['view.wysiwyg'] = "{lang}wcf.wysiwyg.view.wysiwyg{/lang}";language['view.code'] = "{lang}wcf.wysiwyg.view.code{/lang}";
language['noFormElement'] = "{lang}wcf.wysiwyg.error.noFormElement{/lang}";language['extraBBCodeNotValid'] = "{lang}wcf.wysiwyg.message.bbcodeAttributeMissmatch{/lang}"; 

// language direction
var languageDirection = "{lang}wcf.global.pageDirection{/lang}";

// smileys
var smilies = new Object();
{foreach from=$defaultSmileys item=smiley}
	smilies['{@$smiley->smileyCode|encodeJS}'] = new Array('{@$smiley->getURL()|encodeJS}', '{lang}{@$smiley->smileyTitle|encodeJS}{/lang}');
{/foreach}

// bbcodes
var coreBBCodes = new Object();
var extraBBCodes = new Object();
var sourceCodes = new Object();
{if $wysiwygBBCodes|isset && $wysiwygBBCodes|count > 0}
	{foreach from=$wysiwygBBCodes item='bbCode'}
		var tmpBBCode = { wysiwyg:{@$bbCode.wysiwyg}, bbCode:'{@$bbCode.bbcodeTag}', htmlOpen:'{@$bbCode.htmlOpen|encodeJS}', htmlClose:'{@$bbCode.htmlClose|encodeJS}', icon:'{@$bbCode.wysiwygIcon|encodeJS}', sourceCode:{@$bbCode.sourceCode}, attributes:[{implode from=$bbCode.attributes item='attribute'}{ attributeHTML:'{@$attribute.attributeHtml|encodeJS}', validationPattern:'{@$attribute.validationPattern|encodeJS}', required:{@$attribute.required} }{/implode}] };
		{if $bbCode.isCoreBBCode}coreBBCodes['{@$bbCode.bbcodeTag}'] = tmpBBCode;{else}extraBBCodes['{@$bbCode.bbcodeTag}'] = tmpBBCode;{/if}
		{if $bbCode.sourceCode}sourceCodes['{@$bbCode.bbcodeTag}'] = '{@$bbCode.bbcodeTag}';{/if}
		language['{@$bbCode.bbcodeTag}.title'] = "{staticlang}wcf.bbcode.{@$bbCode.bbcodeTag}.title{/staticlang}";
		{foreach from=$bbCode.attributes key='index' item='attribute'}
			language['{@$bbCode.bbcodeTag}.attribute{@$index+1}.promptText'] = "{staticlang}wcf.bbcode.{@$bbCode.bbcodeTag}.promptText{/staticlang}";
		{/foreach}
	{/foreach}
{/if}
{if $errorField|isset && $errorField == 'text'}errorField = true;{else}errorField = false;{/if}

// build editor. pass neccessary variables
tinyMCE.init({
	// set active view flag (code or wysiwyg) ($editorIsActive) (default:wysiwyg)
	editorIsActive : {if $wysiwygEditorMode|isset}{@$wysiwygEditorMode}{else}1{/if},
	
	// set available views (default: both views available)
	editorEnableWysiwygView : {if $editorEnableWysiwygView|isset}{@$editorEnableWysiwygView}{else}1{/if},
	editorEnableCodeView : {if $editorEnableCodeView|isset}{@$editorEnableCodeView}{else}1{/if},	
		
	// set some url vars
	iconURL : "{@RELATIVE_WCF_DIR}icon/",
	imageURL : "{@RELATIVE_WCF_DIR}icon/wysiwyg/",
	blankHTML : "{@RELATIVE_WCF_DIR}js/blank.htm",
	cssFile : "{@RELATIVE_WCF_DIR}style/style-{@$this->getStyle()->styleID}.css",
		
	// set editor height var ($wysiwygHeight)
	height: {if $wysiwygEditorHeight|isset}{@$wysiwygEditorHeight}{else}-1{/if},
	
	// set page default font color var
	defaultPageFontColor: '{@$this->getStyle()->getVariable('page.font.color')|encodeJS}'
});
//]]>
</script>