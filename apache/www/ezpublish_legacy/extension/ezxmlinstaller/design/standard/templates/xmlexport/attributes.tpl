 {def $relatedIds=array()
      $relatedObject=''}
      
<Attributes>
{foreach $node.data_map as $attribute}
    {switch match=$attribute.data_type_string}
        {case match='ezpage'}
        <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
        {/case}
        {case match='ezimage'}
            <{$attribute.contentclass_attribute_identifier} src="{$attribute.content.original.filename}" title="{xml_data_encode($attribute.content.alternative_text)}">{$object_data_list[$node.contentobject_id][$attribute.id]}</{$attribute.contentclass_attribute_identifier}>
        {/case}
        {case match='ezmultioption'}
        <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
        {/case}
        {case match='ezobjectrelation'}
            <{$attribute.contentclass_attribute_identifier}><![CDATA[internal:{$attribute.content.main_node.url_alias}]]></{$attribute.contentclass_attribute_identifier}>
        {/case}
        {case match='ezobjectrelationlist'}
                {if gt($attribute.content.relation_list|count(),0)}
                    {set $relatedIds=array()}
                    {foreach $attribute.content.relation_list as $relation }
                       {set $relatedObject = fetch( 'content', 'object', hash( 'object_id', $relation.contentobject_id ))}
                       {set $relatedIds=$relatedIds|append(concat('internal:',$relatedObject.data_map.name.content))}
                    {/foreach}
                 <{$attribute.contentclass_attribute_identifier}><![CDATA[{$relatedIds|implode(',')}]]></{$attribute.contentclass_attribute_identifier}>
            {/if}
        {/case}
        {case}
            
            <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
        {/case}
    {/switch}
{/foreach}
</Attributes>