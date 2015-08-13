{if !empty($profile->id) && !empty($storage_schema_stats)}
<div class="ui-state-error ui-corner-all" style="margin: 0 0 .5em 0; padding: 0 .7em;"> 
	<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
	<b>Warning!</b>  You are changing the configuration of an active storage profile.  Unless you are very careful you may lose content.  You cannot delete this profile until you've migrated its content to another location.
	</p>
</div>
{/if}

<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formStorageProfilePeek" name="formStorageProfilePeek" onsubmit="return false;">
<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="storage_profiles">
<input type="hidden" name="action" value="saveStorageProfilePeek">
<input type="hidden" name="id" value="{$profile->id}">
<input type="hidden" name="view_id" value="{$view_id}">
<input type="hidden" name="do_delete" value="0">
<input type="hidden" name="_csrf_token" value="{$session.csrf_token}">

<b>{'common.name'|devblocks_translate|capitalize}:</b><br>
<input type="text" name="name" value="{$profile->name}" style="width:98%;" autofocus="true"><br>
<br>

<fieldset>

{if empty($profile->id)}
	<legend>Create a new storage profile</legend>
	
	<b>Storage Engine:</b> 
	<select name="extension_id" onchange="genericAjaxGet('divStorageEngineSettings','c=config&a=handleSectionAction&section=storage_profiles&action=showStorageProfileConfig&ext_id='+escape(selectValue(this))+'&id='+escape(this.form.id.value));">
		{foreach from=$engines item=engine_mft key=engine_id}
		<option value="{$engine_id}" {if $profile->extension_id==$engine_id}selected="selected"{/if}>{$engine_mft->name}</option>
		{/foreach}
	</select>
{else}
	{$profile_extid = $profile->extension_id}
	{if isset($engines.$profile_extid)}
		<legend>{$engines.$profile_extid->name} ({$profile->extension_id})</legend>
	{else}
		<legend>{$profile->extension_id}</legend>
	{/if}
	<input type="hidden" name="extension_id" value="{$profile->extension_id}">
{/if}

<div id="divStorageEngineSettings" style="margin:5px 0px 0px 10px;display:{if 1}block{else}none{/if};">
	{if !empty($storage_engine) && $storage_engine instanceof Extension_DevblocksStorageEngine}
		{$storage_engine->renderConfig($profile)}
	{/if}
</div>

</fieldset>

{if !empty($storage_schema_stats)}
Used by:<br>
{foreach from=$storage_schema_stats item=stats key=schema_id}
	<b>{$storage_schemas.{$schema_id}->name}</b>: {$stats.count} objects ({$stats.bytes|devblocks_prettybytes})<br>
{/foreach}
<br>
{/if}

<div class="status"></div>

{if $active_worker->is_superuser}
	<button type="button" value="saveStorageProfilePeek" onclick="$(this.form).find('input:hidden[name=action]').val($(this).val());genericAjaxPopupPostCloseReloadView(null,'formStorageProfilePeek', '{$view_id}');"><span class="glyphicons glyphicons-circle-ok" style="color:rgb(0,180,0);"></span> {'common.save_changes'|devblocks_translate}</button> 
	{if !empty($profile->id) && empty($storage_schema_stats)}<button type="button" onclick="if(confirm('Are you sure you want to delete this storage profile?')) { this.form.do_delete.value='1';genericAjaxPopupPostCloseReloadView(null,'formStorageProfilePeek', '{$view_id}'); } "><span class="glyphicons glyphicons-circle-remove" style="color:rgb(200,0,0);"></span> {'common.delete'|devblocks_translate|capitalize}</button>{/if} 
{else}
	<div class="error">{'error.core.no_acl.edit'|devblocks_translate}</div>	
{/if}
<button type="button" class="tester" value="testProfileJson"><span class="glyphicons glyphicons-cogwheel"></span> Test</button>

</form>

<script type="text/javascript">
$(function() {
	var $popup = genericAjaxPopupFetch('peek');
	
	$popup.one('popup_open', function(event,ui) {
		$(this).dialog('option','title',"Storage Profile");

		$('#formStorageProfilePeek BUTTON.tester')
		.click(function() {
			$frm = $(this.form);
			$frm.find('input:hidden[name=action]').val($(this).val());
			
			genericAjaxPost('formStorageProfilePeek',null,null,function(json) {
				$o = $.parseJSON(json);
				if(false == $o || false == $o.status) {
					Devblocks.showError('#formStorageProfilePeek div.status',$o.error);
				} else {
					Devblocks.showSuccess('#formStorageProfilePeek div.status',$o.message);
				}
				
				$this.show();
			});			
		})
		;
	});
});
</script>
