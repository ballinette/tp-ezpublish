{if $node.is_main}

 <ContentObject contentClass="{$node.class_identifier}" section="{$node.object.section_id}" remoteID="{$node.remote_id}" objectID="{$node.contentobject_id}" owner="{$node.object.owner_id}" creator="{$node.creator.id}" sort_field="{$node.sort_field}" sort_order="{$node.sort_order}">

   {include uri="design:xmlexport/attributes.tpl" node=$node object_data_list=$object_data_list}

     <SetReference attribute="object_id" value="{$node.url_alias}" />
     <SetReference attribute="node_id" value="{$node.url_alias}" />
     
    {if gt($node.children_count,0)}

        {def $subChildren=fetch( 'content', 'list',hash( 'parent_node_id',$node.node_id,
                                    'depth',1))}

        <Childs>
        	{foreach $subChildren as $subChildNode}
        	    {if eq($subChildNode.children_count,0)}
                    {include uri="design:xmlexport/object.tpl" node=$subChildNode object_data_list=$object_data_list}
                {else}
                    {include uri="design:xmlexport/parent.tpl" node=$subChildNode object_data_list=$object_data_list}
                {/if}
        	{/foreach}
    	</Childs>
    {/if}

</ContentObject>
{/if}
