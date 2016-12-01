<?php

class XmlInstallerOperator
{
    private $Operators;

    function __construct()
    {
        $this->Operators = array(
            'get_node_id_from_remote_id',
            'get_object_id_from_remote_id',
            'get_role_id_from_name',
            'workflow_modify_internal_array',
            'get_state_id_from_identifier',
            'xml_data_encode'
        );
    }

    public function operatorList()
    {
        return $this->Operators;
    }

    /*!
     * \return true to tell the template engine that the parameter list
     * exists per operator type, this is needed for operator classes
     * that have multiple operators.
     */
    public function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        $requiredEmptyString = array( 'type' => 'string', 'required' => true , 'default' => '' );
        $requiredEmptyArray = array( 'type' => 'array', 'required' => true , 'default' => array() );

        return array(
            'get_node_id_from_remote_id'     => array('remote_id' => $requiredEmptyString),
            'get_object_id_from_remote_id'   => array('remote_id' => $requiredEmptyString),
            'get_role_id_from_name'          => array('role_name' => $requiredEmptyString),
            'workflow_modify_internal_array' => array('value' => $requiredEmptyArray),
            'get_state_id_from_identifier'   => array('groupIdentifier' => $requiredEmptyString, 'stateIdentifier' => $requiredEmptyString),
            'xml_data_encode'                => array('value' => $requiredEmptyString)
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'get_node_id_from_remote_id':
                $operatorValue = $this->get_node_id_from_remote_id($namedParameters['remote_id']);
                break;
            case 'get_object_id_from_remote_id':
                $operatorValue = $this->get_object_id_from_remote_id($namedParameters['remote_id']);
                break;
            case 'get_role_id_from_name':
                $operatorValue = $this->get_role_id_from_name($namedParameters['role_name']);
                break;
            case 'workflow_modify_internal_array':
                $operatorValue = $this->workflow_modify_internal_array($namedParameters['value']);
                break;
            case 'get_state_id_from_identifier':
                $operatorValue = $this->get_state_id_from_identifier($namedParameters['groupIdentifier'], $namedParameters['stateIdentifier']);
                break;
            case 'xml_data_encode':
                $operatorValue = $this->xml_data_encode($namedParameters['value']);
        }
    }

    /**
     * Fetches a node by remote id (used in ezxmlinstaller for relation attribute's default placement)
     * @param string $remoteId : node's remoteId to fetch
     * @return string node_id
     */
    function get_node_id_from_remote_id($remoteId)
    {
        $object = eZContentObject::fetchByRemoteID($remoteId);

        if(is_object($object))
        {
            return $object->mainNode()->attribute('node_id');
        }
        else
        {
            return '';
        }
    }

    /**
     * Fetches an object by remote id (used in ezxmlinstaller)
     * @param string $remoteId : node's remoteId to fetch
     * @return string object_id
     */
    function get_object_id_from_remote_id($remoteId)
    {
        $object = eZContentObject::fetchByRemoteID($remoteId);

        if(is_object($object))
        {
            return $object->ID;
        }
        else
        {
            return '';
        }
    }

    /**
     * Return the ID of a role from its name
     * @param $roleName
     * @return int
     */
    function get_role_id_from_name($roleName)
    {
        $role = eZRole::fetchByName($roleName);
        $result = null;
        if ($role != null) {
            $result = $role->ID;
        }
        return $result;
    }

    /**
     * Returns the input array with brackets around the values starting with "internal:"
     * This operator is used with workflow events
     * @param $values
     * @return array
     */
    function workflow_modify_internal_array($values)
    {
        foreach ($values as $key => $value) {
            if (substr($value, 0, 9) == 'internal:') {
                $values[$key] = '['.$value.']';
            }
        }
        return $values;
    }

    /**
     * Return the ID of a state from its identifier
     * @param $groupIdentifier, $stateIdentifier
     * @return int
     */
    function get_state_id_from_identifier($groupIdentifier, $stateIdentifier)
    {
        $group = eZContentObjectStateGroup::fetchByIdentifier($groupIdentifier);
        $state = eZContentObjectState::fetchByIdentifier($stateIdentifier, $group->ID);
        $result = null;
        if ($state != null) {
            $result = $state->ID;
        }
        return $result;
    }

    /**
     * Encode xml data to avoid parsing error
     * @param $value
     * @return mixed
     */
    function xml_data_encode($value)
    {
        $value = str_replace('&', '&amp;', $value);
        $value = str_replace('"', '&quot;', $value);

        return $value;
    }

}
