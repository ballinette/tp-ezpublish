{*include this file for policy creation inside a role*}
{def $functions = array()}
{if is_set($function)}
    {if is_array($function)}
        {set $functions = $function}
    {else}
        {set $functions = array($function)}
    {/if}
{/if}
{foreach $functions as $_function}
    <Policy module="{$module}" function="{$_function}">
        {if first_set($class, $section, $parent_class, $owner, $node_id, $siteaccess, $subtree, $function_list, $state, $newstate, $stategroup, $language, false())}
        <Limitations>
            {if is_set($node_id)}
                {if is_array($node_id)}
                    {foreach $node_id as $node}
                        <Node>{$node}</Node>
                    {/foreach}
                {else}
                    <Node>{$node_id}</Node>
                {/if}
            {/if}

            {if is_set($subtree)}
                {if is_array($subtree)}
                    {foreach $subtree as $_subtree}
                        <Subtree>{$_subtree}</Subtree>
                    {/foreach}
                {else}
                    <Subtree>{$subtree}</Subtree>
                {/if}
            {/if}

            {if is_set($class)}
                {if is_array($class)}
                    {foreach $class as $str}
                        <Class>{include uri='design:xmlinstaller/class.tpl' identifier=$str}</Class>
                    {/foreach}
                {else}
                    <Class>{include uri='design:xmlinstaller/class.tpl' identifier=$class}</Class>
                {/if}
            {/if}

            {if is_set($section)}
                {if is_array($section)}
                    {foreach first_set($section, array()) as $_section}
                        <Section>{first_set($sectionMap[$_section], $_section)}</Section>
                    {/foreach}
                {else}
                    <Section>{first_set($sectionMap[$section], $section)}</Section>
                {/if}
            {/if}

            {if is_set($siteaccess)}
                {if is_array($siteaccess)}
                    {foreach $siteaccess as $site}
                        <SiteAccess>{$site}</SiteAccess>
                    {/foreach}
                {else}
                    <SiteAccess>{$siteaccess}</SiteAccess>
                {/if}
            {/if}

            {if and( is_set($owner), eq($owner,"self"))}
                <Owner>1</Owner>
            {/if}

            {if is_set($parent_class)}
                {if is_array($parent_class)}
                    {foreach $parent_class as $_class}
                        <ParentClass>{include uri='design:xmlinstaller/class.tpl' identifier=$_class}</ParentClass>
                    {/foreach}
                {else}
                    <ParentClass>{include uri='design:xmlinstaller/class.tpl' identifier=$parent_class}</ParentClass>
                {/if}
            {/if}

			{if is_set($function_list)}
				{if is_array($function_list)}
					{foreach $function_list as $_function_list}
						<FunctionList>{$_function_list}</FunctionList>
					{/foreach}
				{else}
					<FunctionList>{$function_list}</FunctionList>
				{/if}
			{/if}

            {if and(is_set($state), is_set($stategroup))}
                {if is_array($state)}
                    {foreach $state as $_state}
                        <StateGroup_{$stategroup}>{$_state}</StateGroup_{$stategroup}>
                    {/foreach}
                {else}
                    <StateGroup_{$stategroup}>{$state}</StateGroup_{$stategroup}>
                {/if}
            {/if}

            {if is_set($newstate)}
                {if is_array($newstate)}
                    {foreach $newstate as $_newstate}
                        <NewState>{$_newstate}</NewState>
                    {/foreach}
                {else}
                    <NewState>{$newstate}</NewState>
                {/if}
            {/if}

            {if is_set($language)}
                {if is_array($language)}
                    {foreach $language as $_language}
                        <Language>{$_language}</Language>
                    {/foreach}
                {else}
                    <Language>{$language}</Language>
                {/if}
            {/if}


        </Limitations>
        {/if}
    </Policy>
{/foreach}
