<?php

// Operator autoloading
$eZTemplateOperatorArray 	= array();

$eZTemplateOperatorArray[] 	=  array(
    'script' => 'extension/ezxmlinstaller/classes/templateoperators/ezxmlinstaller_operators.php',
    'class' => 'XmlInstallerOperator',
    'operator_names' => array(
        'get_node_id_from_remote_id',
        'get_object_id_from_remote_id',
        'get_role_id_from_name',
        'workflow_modify_internal_array',
        'xml_data_encode'
    )
);
