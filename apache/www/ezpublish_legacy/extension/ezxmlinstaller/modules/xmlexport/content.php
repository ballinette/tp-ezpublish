<?php

$module = $Params['Module'];
$http       = eZHTTPTool::instance();

$tpl = eZTemplate::factory();

$list = eZContentClass::fetchList( );

$nodeID = $Params['NodeId'];
//eZDebug::writeError($nodeID);
$storageDir = 'extension/ezxmlinstaller/data/media';

$node_id_array = array();

// checking whether node is set
if ( !$nodeID )
{
    // if no node is set, export specific content trees
    $node_id_array[]=2;//content
    $node_id_array[]=43;//Media
}
else
{
    // checking whether node is numeric
    if ( !is_numeric( $nodeID ) )
    {
        exit(1);
    }

    // fetching node and checking whether node exists
    $node = eZContentObjectTreeNode::fetch( $nodeID );
    if ( !$node )
    {
        exit(1);
    }

    $node_id_array[]=$nodeID;
}


// fetching node subtree
//$subTreeCount = $node->subTreeCount();
//$subTree = $node->subTree();
$subTree=eZContentObjectTreeNode::subTreeByNodeID(false,$node_id_array);

// preparing variables for looping
//$openedFPs = array();
$objectList = array();
$objectDataList = array();
$nodeList = array();
$addLocationList = array();

// looping through subtree
while ( list( $key, $childNode ) = each( $subTree ) )
{
    $status = true;
    $object = $childNode->attribute( 'object' );
    $classIdentifier = $object->attribute( 'class_identifier' );

// looping through attributes
    foreach ( $object->attribute( 'contentobject_attributes' ) as $attribute )
    {
        $attributeStringContent = $attribute->toString();

        if ( $attributeStringContent != '' )
        {
            switch ( $datatypeString = $attribute->attribute( 'data_type_string' ) )
            {
                case 'ezimage':
                {
                    $content = $attribute->attribute( 'content' );
                    //$displayText = $content->displayText();
                    $imageAlias = $content->imageAlias('original');
                    $imagePath = $imageAlias['url'];
                    if( $imagePath != '')
                    {
                        $imageFile = $imageAlias['filename'];
                        // here it would be nice to add a check if such file allready exists

                        if(file_exists ( $storageDir . '/' . $imageFile))
                        {
                            $imageFile = $imageAlias['basename'].'_'.$attribute->attribute('id').'.'.$imageAlias['suffix'];

                            //print_r('Image '.$imageFile . ' already exists !');
                            //print_r('Nom ->'.$childNode->attribute('name'). ' - attribut -->'.$attribute->attribute('id'));
                            //print_r($content);
                            //exit;
                        }

                        $success = eZFileHandler::copy( $imagePath, $storageDir . '/' . $imageFile );
                        if ( !$success )
                        {
                            $status = false;
                        }
                        $attributeStringContent = $imageFile;
                    }
                } break;

                case 'ezbinaryfile':
                case 'ezmedia':
                {
                    $binaryData = explode( '|', $attributeStringContent );
                    $success = eZFileHandler::copy( $binaryData[0], $storageDir . '/' . $binaryData[1] );
                    if ( !$success )
                    {
                        $status = false;
                    }
                    $attributeStringContent = $binaryData[1];
                } break;

                case 'ezselection' : 
                {
                    $attributeStringContent = $attribute->content();
                    $attributeStringContent=$attributeStringContent[0];
                } break;

/*
                case 'ezobjectrelation' : 
                {
                    $objectId = $attributeStringContent;

                } break;

                case 'ezobjectrelationlist' : 
                {
                    if($classIdentifier == 'mr_code')
                    {

                        print_r('images mr code '.$childNode->attribute('name'));
                        $attrContent=$attribute->content();

                        print_r($attrContent['relation_list'][0]);

                        exit;
                    }
                    $objectId = $attributeStringContent;

                } break;
*/
                
                default:
            }
        }

// cleaning up information and moving attribute content to template variables
        $attributeStringContent = str_replace( '<?xml version="1.0" encoding="utf-8"?>', '', $attributeStringContent );
        $attributeStringContent = str_replace( '<?xml version="1.0"?>', '', $attributeStringContent );
        $objectDataList[$object->attribute( 'id' )][$attribute->attribute( 'id' )] = $attributeStringContent;
    }

// moving object to template variables
    $objectList[$object->attribute( 'id' )] = $object;
    $nodeList[$childNode->attribute( 'node_id' )] = $childNode;


    //exporting object locations when current node is main location
   
    //var_dump($childNode->object()->assignedNodes());

    if( $childNode->isMain() && count($childNode->object()->assignedNodes()) > 1 )
    {

        $objectInternalRef="internal:".$childNode->object()->attribute('id');

        foreach($childNode->object()->assignedNodes() as $assignedNode)
        {
            $assignmentInternalRef='';

            //echo "objectInternalRef : " . $objectInternalRef. "\n";


            //exporting all locations except main location
            if($assignedNode->attribute('node_id') != $childNode->object()->mainNodeID())
            {
                //$assignedObjectParentDataMap = $assignedNode->fetchParent()->object()->attribute('data_map');
                //$mr_code = $assignedObjectParentDataMap['mr_code']->DataText;

                //if($mr_code != '')
                //{
                //$assignmentInternalRef="internal:".$mr_code;
                 $assignmentInternalRef="internal:".$assignedNode->fetchParent()->attribute('node_id');


                // echo "assignmentInternalRef : " . $assignmentInternalRef. "\n";


                //print_r('adding location for node id -->'.$assignedNode->attribute('node_id'));
                if( !array_key_exists($objectInternalRef,$addLocationList) )
                {
                    $nodeIds=array();
                }
                else
                {
                    $nodeIds = $addLocationList[$objectInternalRef];
                }

                //$nodeIds[]=$assignedNode->attribute('node_id');
               $nodeIds[]=$assignmentInternalRef;
               $addLocationList[$objectInternalRef]=$nodeIds;
                //}
            }
        }
    }

   
}



// we need to clean up variables here
$tpl->setVariable( 'object_list', $objectList );
$tpl->setVariable( 'object_data_list', $objectDataList );
$tpl->setVariable( 'node_list', $nodeList );
//$tpl->setVariable( "parent_node", $nodeID );
$tpl->setVariable( "parent_nodes", $node_id_array );
$tpl->setVariable( "storage_dir", $storageDir );
$tpl->setVariable( "sub_tree", $subTree );
//$tpl->setVariable( "sub_tree_count", $subTreeCount );

//var_dump($objectDataList);
//exit;


$tpl->setVariable( "addLocationList", $addLocationList );

$result = $tpl->fetch( 'design:xmlexport/content.tpl' );

$doc = new DOMDocument;
$doc->loadXML( $result );

eZExecution::cleanup();
eZExecution::setCleanExit();

header('Content-Type: text/xml');
//header('Content-Type: text/html');
//header('Content-Type: text/txt');
header('Pragma: no-cache' );
header('Expires: 0' );

//ob_end_clean();

//echo $doc->saveXML();

echo $result;

/*
$contentType = 'application/octet-stream';
$userAgent = eZSys::serverVariable( 'HTTP_USER_AGENT' );

if(preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $userAgent))
{
    $contentType = 'application/octetstream';
}
elseif(preg_match('/MSIE ([0-9].[0-9]{1,2})/', $userAgent))
{
    $contentType = 'application/force-download';
}
ob_clean();

header('X-Powered-By: eZ Publish - ezxmlinstaller content export');
header('Content-Type: '.$contentType);
header('Expires: 0');
header('Cache-Control: private');
header('Pragma: no-cache' );
header('Content-Disposition: attachment; filename="content.xml"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Connection: close');

ob_end_clean();

echo $result;
*/
eZExecution::cleanExit();

//eZDebug::writeError($subTree);
//eZDebug::writeError($objectDataList);
//eZDebug::writeError($result);

exit(0);

?>