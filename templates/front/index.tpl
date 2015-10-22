<h1>{lang key='tagcloud'}</h1>
<div class="ia-items">
	<div class="tag-page clearfix">
	{foreach $tags as $tag}
		<a href="{$tag.url}" style="font-size: {$tag.size}px;">{$tag.tag}</a>
	{/foreach}
	</div>
</div>

{ia_print_css files='_IA_URL_plugins/tagcloud/templates/front/css/style'}