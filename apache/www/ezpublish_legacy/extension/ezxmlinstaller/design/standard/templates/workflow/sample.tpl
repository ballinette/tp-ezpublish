<ProccessInformation comment="Creating workflow" />
<CreateWorkflow>
    <WorkflowGroup name="Standard" groupExistAction="keep">
        <Workflow name="Validation" workflowExistAction="extend" workflowTypeString="group_ezserial">
            {include uri='design:xmlinstaller/workflow/event/ezapprove.tpl' description='Content validation'
                lang_id=0 version='publish' excluded_groups=array('internal:USER_GROUP_CONTRIBUTOR_OBJECT_ID','internal:USER_GROUP_EDITOR_OBJECT_ID')
                approving_groups=array('internal:USER_GROUP_CONTRIBUTOR_OBJECT_ID',13)}
        </Workflow>
    </WorkflowGroup>
</CreateWorkflow>