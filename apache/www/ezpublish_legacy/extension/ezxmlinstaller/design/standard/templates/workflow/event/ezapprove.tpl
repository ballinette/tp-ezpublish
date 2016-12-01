{def $description = $description|default('')}

{* language ID, 0 = all. See backend /content/translations *}
{def $lang_id = $lang_id|default(0)}

{* the ID of the section, -1 = all. "Standard" is 1 *}
{def $section_id = $section_id|default(-1)}

{* Excluded user groups *}
{def $excluded_groups = $excluded_groups|default(array())}
{def $excluded_groups = workflow_modify_internal_array($excluded_groups)}

{* Groups who approve content*}
{def $approving_groups = $approving_groups|default(array())}
{def $approving_groups = workflow_modify_internal_array($approving_groups)}

{* Users who approve content *}
{def $approving_users = $approving_users|default(array())}
{def $approving_users = workflow_modify_internal_array($approving_users)}

{* version can be 'all', 'publish' or 'update'. See backend interface for details *}
{def $version = $version|default('all')}
{def $versions = hash(
    'all', 0,
    'publish', 1,
    'update', 2
)}


<Event workflowTypeString="event_ezapprove"
{if isset($placement)}
    placement="{$placement}"
{/if}
>
    <Data>
        <description>{$description}</description>
        {* Id de la langue, 0 = toutes *}
        <data_int2>{$lang_id}</data_int2>
        {* Versions concernées : 1 = Publication d'un nouvel objet / 2 = Mise à jour de l'objet existant *}
        <data_int3>{$versions[$version]}</data_int3>
        {* ID de la section concernée, -1 = toutes *}
        <data_text1>{$section_id}</data_text1>
        {* groupes d'utilisateurs qui ne sont pas concernés par la validation *}
        <data_text2>{$excluded_groups|implode(',')}</data_text2>
        {* utilisateurs qui peuvent approuver le contenu *}
        <data_text3>{$approving_users|implode(',')}</data_text3>
        {* groupes d'utilisateurs qui peuvent approuver du contenu *}
        <data_text4>{$approving_groups|implode(',')}</data_text4>
    </Data>
</Event>
