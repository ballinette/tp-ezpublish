{if $node.is_main}
 <ContentObject contentClass="{$node.class_identifier}" section="{$node.object.section_id}" remoteID="{$node.remote_id}" objectID="{$node.contentobject_id}" owner="{$node.object.owner_id}" creator="{$node.creator.id}" sort_field="{$node.sort_field}" sort_order="{$node.sort_order}">
   
    {include uri="design:xmlexport/attributes.tpl" node=$node object_data_list=$object_data_list}

    <SetReference attribute="object_id" value="{$node.url_alias}" />
    <SetReference attribute="node_id" value="{$node.url_alias}" />

</ContentObject>

{/if}