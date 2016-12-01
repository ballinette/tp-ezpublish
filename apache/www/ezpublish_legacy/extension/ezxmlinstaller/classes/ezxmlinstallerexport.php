<?php
//
//
// SOFTWARE NAME: eZ XML Installer extension for eZ Publish
// SOFTWARE RELEASE: 0.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2012 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

class eZXMLInstallerExport
{
    const OUTPUT_EXPORTDATA_DOWNLOAD  = 'download';
    const OUTPUT_EXPORTDATA_FILE      = 'file';
    const OUTPUT_EXPORTDATA_ROW       = 'row';

    private $output;
    private $pathfile;

    function __construct( $output = self::OUTPUT_EXPORTDATA_DOWNLOAD, $pathfile = '' )
    {
        $this->output = $output;
        $this->pathfile = $pathfile;
    }

    private function disableDebug()
    {
        eZTemplate::setIsDebugEnabled( eZTemplate::DEBUG_INTERNALS );
    }


    public function exportContent($node_id_list,$dest_node_id_list)
    {
        $this->disableDebug();

        $storageDir = 'extension/ezxmlinstaller/data/media';

        // fetching nodes subtrees
        $subTree=eZContentObjectTreeNode::subTreeByNodeID(false,$node_id_list);

        // preparing variables for looping
        $objectList = array();
        $objectDataList = array();
        $nodeList = array();
        $addLocationList = array();

        //Adding parent nodes to sbutree
        foreach ($node_id_list as $nodeId)
        {
            $parentNode = eZContentObjectTreeNode::fetch($nodeId);
            array_push($subTree,$parentNode);
        }

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

                                if(!file_exists ( $storageDir . '/' . $imageFile))
                                {
                                    $success = eZFileHandler::copy( $imagePath, $storageDir . '/' . $imageFile );
                                    if ( !$success )
                                    {
                                        echo "error copying file";
                                        $status = false;
                                    }
                                }

                                
                                $attributeStringContent = $imageFile;
                            }
                        } break;

                        case 'ezbinaryfile':
                        {
                            //TODO : export files

                        }
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
           
            if( $childNode->isMain() && count($childNode->object()->assignedNodes()) > 1 )
            {
                $objectInternalRef="internal:".$childNode->urlAlias();

                foreach($childNode->object()->assignedNodes() as $assignedNode)
                {
                    //exporting all locations except main location
                    if($assignedNode->attribute('node_id') != $childNode->object()->mainNodeID())
                    {
                        $assignmentInternalRef="internal:".$assignedNode->fetchParent()->attribute('node_id');

                        if( !array_key_exists($objectInternalRef,$addLocationList) )
                        {
                            $nodeIds=array();
                        }
                        else
                        {
                            $nodeIds = $addLocationList[$objectInternalRef];
                        }

                        $nodeIds[]=$assignmentInternalRef;
                        $addLocationList[$objectInternalRef]=$nodeIds;
                    }
                }
            }
        }


        $parentNodeList=array();
        foreach ($dest_node_id_list as $nodeId => $destNodeId)
        {
            $parentNodeList[]=array ('node_id' => $nodeId, 'dest_node_id' => $destNodeId);
        }

        $tpl = eZTemplate::factory();

        // set variables to template here
        $tpl->setVariable( 'object_list', $objectList );
        $tpl->setVariable( 'object_data_list', $objectDataList );
        $tpl->setVariable( "parent_node_list", $parentNodeList);

        $tpl->setVariable( "storage_dir", $storageDir );
        $tpl->setVariable( "sub_tree", $subTree );
        $tpl->setVariable( "addLocationList", $addLocationList );

        $tpl->setIsCachingAllowed( false );
        $result = $tpl->fetch( 'design:xmlexport/content.tpl' );

        $this->outputExportData( $result );
    }

    public function exportRole()
    {
        # code...
    }

    public function exportAssignsRole( $exportRoles = false )
    {
        $this->disableDebug();

        $rolesList = array();

        // fetch roles
        if ( $exportRoles === false )
        {
            $rolesList = eZRole::fetchList();
        }
        else
        {
            $exportRoles = explode( ',', $exportRoles );

            foreach ( $exportRoles as $roleId )
            {
                $rolesList[] = eZRole::fetch( $roleId );
            }
        }

        // fetch assignations
        $assignsRoleList = array();
        foreach ( $rolesList as $role )
        {
            $assignsRoleList[$role->ID] = $role->fetchUserByRole();
        }

        $tpl = eZTemplate::factory();

        // export assignations
        $tpl->setIsCachingAllowed( false );
        $tpl->setVariable( 'assignsrole_list', $assignsRoleList );
        $result = $tpl->fetch( 'design:xmlexport/assignroles.tpl' );

        $this->outputExportData( $result );
    }

    private function outputExportData( $dataExport )
    {
        $doc = new DOMDocument;
        $doc->loadXML( $dataExport );
        $dataExportXML = $doc->saveXML();

        switch ( $this->output ) 
        {
            case self::OUTPUT_EXPORTDATA_DOWNLOAD:

                // Download exported datas in file
                eZExecution::cleanup();
                eZExecution::setCleanExit();
                
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

                echo $dataExportXML;
                break;

            case self::OUTPUT_EXPORTDATA_FILE:
                file_put_contents($this->pathfile, $dataExportXML);
                // Write exported datas in file
//                eZFile::create( $this->pathfile, false, $dataExportXML );
                break;

            case self::OUTPUT_EXPORTDATA_ROW:
            default:

                echo $dataExportXML;
                break;
        }
    }
}

?>