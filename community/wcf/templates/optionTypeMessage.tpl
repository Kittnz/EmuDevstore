{include file='wysiwyg' once=true}
<textarea id="{$optionData.optionName}" cols="40" rows="10" name="values[{$optionData.optionName}]">{$optionData.optionValue}</textarea>
<script type="text/javascript">
//<![CDATA[
// language
tinyMCE.elements.push('{$optionData.optionName}');
//]]>
</script>