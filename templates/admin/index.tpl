{include file='box-header.tpl' title=$gTitle}
<form action="{$smarty.const.IA_SELF}" method="post">
{preventCsrf}
<table border="0" cellspacing="0" cellpadding="0" class="striped">
	{foreach from=$items item=item}
	<tr>
		<td width="100"><label for="opt_{$item.value}">{$item.title}</label></td>
		<td><input type="checkbox" name="items[]" value="{$item.value}" id="opt_{$item.value}" {if $item.checked}checked="checked"{/if}/></td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="2"><input type="submit" name="save" class="common" value="{lang key='save_changes'}"></td>
	</tr>
</table>
</form>
{include file='box-footer.tpl' class='box'}