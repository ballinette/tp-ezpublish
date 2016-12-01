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
        datatype="ezmatrix"
        identifier="{$identifier}"
        required="{first_set( $required, 'false' )}"
        searchable="{first_set( $searchable, 'false' )}"
        informationCollector="{first_set( $info_collector, 'false' )}"
        translatable="{first_set( $translatable, 'true' )}"
		category="{first_set( $category, 'content' )}"
		description="{first_set( $description, '' )}"
        {if is_set($placement)}placement="{$placement}"{/if}>
    <Names{$names_attributes}{$always_available_attribute}/>
    <DatatypeParameters>
        <default-name>{first_set( $default_name, $attr_name )}</default-name>
        <default-row-count>{first_set( $default_row_count, 0 )}</default-row-count>
        <columns>
                {def $row = false()}
                {foreach first_set($rows, array())|explode('|') as $item}
                    {set $row = $item|explode( ':' )}
                    <column identifier="{$row.0}" name="{$row.1}" />
                {/foreach}
        </columns>
    </DatatypeParameters>
</Attribute>
{undef}