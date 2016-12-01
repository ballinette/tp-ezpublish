{def $always_available_locale = first_set($always_available_locale, 'fre-FR')}
{def $always_available_attribute = concat(' always-available="', $always_available_locale, '"')}

{def $names_attributes = ''}
{if $attr_name_locale|null|not}
    {* loop through translations *}
    {foreach $attr_name_locale as $locale => $attr_name}
        {set $names_attributes = concat($names_attributes, ' ', $locale, '="', $attr_name, '"')}
    {/foreach}
{elseif $attr_name|null|not}
    {* by default, set name for french locale *}
    {set $names_attributes = concat(' fre-FR="', $attr_name, '"')}
{/if}

<Attribute
        datatype="eztags"
        identifier="{$identifier}"
        required="{first_set( $required, 'false' )}"
        searchable="{first_set( $searchable, 'true' )}"
        informationCollector="{first_set( $info_collector, 'false' )}"
        translatable="{first_set( $translatable, 'true' )}"
		category="{first_set( $category, 'content' )}"
		description="{first_set( $description, '' )}"
        {if is_set($placement)}placement="{$placement}"{/if}>
    <Names{$names_attributes}{$always_available_attribute}/>
    <DatatypeParameters>
        <subtree-limit>{first_set( $subtree, '0' )}</subtree-limit>
        <max-tags>{first_set( $maxTags, '5' )}</max-tags>
        <dropdown>{first_set( $dropdown, 'false' )}"</dropdown>
        <hide-root-tag>{first_set( $hideRootTag, 'false' )}</hide-root-tag>
    </DatatypeParameters>
</Attribute>