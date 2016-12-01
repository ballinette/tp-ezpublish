<?xml version = '1.0' encoding = 'UTF-8'?>
{def $parent_node_depth = $sub_tree[0].depth}
{def $current_node_depth = $parent_node_depth}
{def $unset_child = false()}
{def $children=""}
{def $parentNode=""}
{*
parent_node : {$parent_node}
children count  : {$children|count()}
parent_node_depth : {$parent_node_depth}
*}

<eZXMLImporter data_source="{$storage_dir}">

    
    {*$parent_node_list|attribute(show)*}

    {*foreach $parent_nodes as $parent_node_id*}
    {*foreach $parent_node_list as $dest_node_id => $parent_node_id*}
    {foreach $parent_node_list as $exportInfo}

      {set $parentNode = fetch( 'content', 'node', hash( 'node_id',$exportInfo.node_id))}


       {*set $children=fetch( 'content', 'list',hash( 'parent_node_id',$parent_node_id,
                                    'depth',1,
                                    'sort_by',array(array('path_string',true()),
                                                    array('depth',true())) ))*}
        <CreateContent parentNode="{$exportInfo.dest_node_id}">

            {include uri="design:xmlexport/parent.tpl" node=$parentNode object_data_list=$object_data_list}

            {*foreach $children as $index => $childNode}

                    {if eq($childNode.children_count,0)}
                        {include uri="design:xmlexport/object.tpl" node=$childNode object_data_list=$object_data_list}
                    {else}
                        {include uri="design:xmlexport/parent.tpl" node=$childNode object_data_list=$object_data_list}
                    {/if}

            {/foreach*}
         </CreateContent>
    {/foreach}

{foreach $addLocationList as $objectId => $nodeIds}
    {foreach $nodeIds as $node_id}
       <AddLocation contentObject="{$objectId}" addToNode="{$node_id}" />
    {/foreach}
{/foreach}

</eZXMLImporter>
{undef}