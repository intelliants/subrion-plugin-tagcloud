<div class="ia-items">
	{foreach $items as $item}
		<div class="media ia-item ia-item-bordered">
			{if !empty($item.title)}
				{ia_url item=$type data=$item type='link' text=$item.title}
			{/if}

			{foreach $fields as $field}
				{if 'plan_id' != $field.name}
					{include file='field-type-content-view.tpl' wrappedValues=true}
				{/if}
			{/foreach}

			<div class="ia-item-panel">
				{ia_url item=$type data=$item type='icon' classname='btn-info'}

				{if $member}
					{printFavorites item=$item itemtype=$type classname='btn btn-sm btn-info'}
					{accountActions item=$item itemtype=$type classname='btn btn-sm btn-info'}
				{/if}
			</div>
		</div>
	{/foreach}
</div>