<?php
/**
 * File containing the eZ Publish view implementation.
 *
 * @copyright Kaliop
 * @version 1.0.0
 * @package ezxmlinstaller
 */
//setting up the eZ template object:
$tpl = eZTemplate::factory(); //this line of code is for ez publish 4.3, replace it with the following lines for versions prior to that
$http   = eZHTTPTool::instance();

$selectedNodeIds=array();

if ( $Module->isCurrentAction( 'Add' )  )
{
  if( $http->hasPostVariable('exportNodeIds') )
  {
    $selectedNodeIds = $http->postVariable('exportNodeIds');
    $http->setSessionVariable('selectedNodeIds', $selectedNodeIds) ;
  }

  if( $http->hasPostVariable('destinationNodeIds') )
  {
    $destinationNodeIds = $http->postVariable('destinationNodeIds');
    $http->setSessionVariable('destinationNodeIds', $destinationNodeIds) ;
  }

  $ignoreNodesSelect=$selectedNodeIds;
 	return eZContentBrowse::browse( array( 'action_name' => 'AddNodeExport',
                                           'description_template' => 'design:content/browse_bookmark.tpl',
                                           'from_page' => "/xmlexport/selection",
                                           'ignore_nodes_select' => $ignoreNodesSelect ),
                                    $Module );
}
else if ( $Module->isCurrentAction( 'AddNodeExport' )  )
{
    $nodeList = eZContentBrowse::result( 'AddNodeExport' );
    if($http->hasSessionVariable('selectedNodeIds'))
    {
      $selectedNodeIds = $http->sessionVariable('selectedNodeIds');

      if ( $nodeList )
      {
        foreach ($nodeList as $key => $nodeId)
        {
          array_push($selectedNodeIds, $nodeId);
        }
      }
    }
    
    if($http->hasSessionVariable('destinationNodeIds'))
    {
      $destinationNodeIds = $http->sessionVariable('destinationNodeIds');  
    }
    
  
   $tpl->setVariable( 'nodeList', $selectedNodeIds );
   $tpl->setVariable( 'destinationNodeIds', $destinationNodeIds );

}
else if( $Module->isCurrentAction( 'Export' )  )
{

  if( $http->hasPostVariable('exportNodeIds') )
  {
     $exportNodeIds = $http->postVariable('exportNodeIds');
  }

   if( $http->hasPostVariable('destinationNodeIds') )
  {
    $destinationNodeIds = $http->postVariable('destinationNodeIds');
  }
   
  $xmlExport = new eZXMLInstallerExport();
  $xmlExport->exportContent($exportNodeIds, $destinationNodeIds);

   eZExecution::cleanExit();
   exit(0);
}
else
{
  $http->setSessionVariable('selectedNodeIds', array());
  $http->setSessionVariable('destinationNodeIds', array());
}

  // setting up what to render to the user:
  $Result = array();
  $Result['content'] = $tpl->fetch( 'design:xmlexport/selection.tpl' ); //main tpl file to display the output
  //$Result['left_menu'] = "design:newsletter/leftmenu.tpl"; //uncomment this line if you want to use a custom left navigation for this view.

  $Result['path'] = array( array( 'url' => false,
                                  'text' => 'Xml Export Selection' ) ); //what to show in the Title bar for this URL
 

?>
