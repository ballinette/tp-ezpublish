<?xml version = '1.0' encoding = 'UTF-8'?>
<eZXMLImporter>
  <AssignRoles>
{def $limitationType = ""}
{def $limitationValue = ""}
{foreach $assignsrole_list as $role_id => $assigns_list}
    {foreach $assigns_list as $assignation}
        {if eq( $assignation.limit_ident, 'Subtree' )}
            {set $limitationType = 'subtreeLimitation'}
            {set $limitationValue = $assignation.limit_value|explode('/')}
            {set $limitationValue = $limitationValue|extract(count($limitationValue)|sub(2),1)}
            {set $limitationValue = $limitationValue.0}
        {elseif eq( $assignation.limit_ident, 'Section' )}
            {set $limitationType = 'sectionLimitation'}
            {set $limitationValue = $assignation.limit_value}
        {else}
            {set $limitationType = ''}
        {/if}
        <RoleAssignment roleID="{$role_id}" assignTo="{$assignation.user_object.id}" {if ne( $limitationType, '')}{$limitationType}="{$limitationValue}"{/if}/>
    {/foreach}
{/foreach}
    </AssignRoles>
</eZXMLImporter>
