<?php

require_once( 'autoload.php' );

$cli    = eZCLI::instance();
$script = eZScript::instance( array( 'description'     => ( "eZ Publish XML installer - Export\n\n" .
                                                            ""
                                                          ),
                                      'use-session'    => true,
                                      'use-modules'    => true,
                                      'use-extensions' => true
                                   )
                            );

$script->startup();

$options = $script->getOptions( "[export-type:][export-path:][export-node-ids:][destination-node-ids:][roles:]",
                                "",
                                array( 'export-type'          => 'type of the export [content, role, assignrole] ( ex : --export-type=content )',
                                       'export-path'          => 'export will be save in this file ( ex : --export-path=./ezxmlinstaller/data/myexport.xml )',
                                       'export-node-ids'      => 'node-ids list for export ( ex : 94510,95841,98658  )',
                                       'destination-node-ids' => 'node-ids destination list for import ( ex : 35215,38548,98547  ). Must have the same number of elements than export-node-ids option',
                                       'roles'                => 'list of roles ( ex : --roles=1,125,54 )'
                                     ),
                                false,
                                array( 'user' => true ));

$script->initialize();

if ( !$script->isInitialized() )
{
    $cli->error( 'Error initializing script: ' . $script->initializationError() . '.' );
    $script->shutdown( 0 );
}

$cli->output( "Checking requirements..." );

// check options
if( !( isset( $options['export-type'] ) || isset( $options['export-type'] ) ) )
{
    $cli->error( "Please select your export type." );
    $script->shutdown( 1 );
}
else
{
    $exportType = $options['export-type'];
}

$exportStatus  = false;
$exportPath    = isset( $options['export-path'] ) ? $options['export-path'] : "./ezxmlexport_" . $exportType . ".xml";
$roles         = isset( $options['roles'] ) ? $options['roles'] : false;
$exportNodeIds = isset( $options['export-node-ids'] ) ? $options['export-node-ids'] : false;
$destNodesIds  = isset( $options['destination-node-ids'] ) ? $options['destination-node-ids'] : false;

// Export data
$installerExport = new eZXMLInstallerExport( eZXMLInstallerExport::OUTPUT_EXPORTDATA_FILE, $exportPath );

switch ( $exportType )
{
    case 'content':

        $contentProcess = true;

        // check value content
        if (!$exportNodeIds || !$destNodesIds) {
            $cli->error("Option 'export-node-ids' and 'destination-node-ids' must be defined");
            $contentProcess = false;
        }

        // explode option value
        $exportNodeIds = explode(',', $exportNodeIds);
        $destNodesIds  = explode(',', $destNodesIds);

        // check option elements number
        if (count($exportNodeIds) != count($destNodesIds)) {
            $cli->error("Number of elements for 'export-node-ids' and 'destination-node-ids' must be equal");
            $contentProcess = false;
        }

        if ($contentProcess) {
            $cli->output( "Exporting content..." );

            $destNodesIds = array_combine($exportNodeIds, $destNodesIds);

            $installerExport->exportContent($exportNodeIds, $destNodesIds);
            $exportStatus = true;
        }
        break;
    
    case 'role':
        $cli->output( "Exporting roles..." );
        # code...
        break;

    case 'assignrole':
        $cli->output( "Exporting roles assignations..." );
        $installerExport->exportAssignsRole( $roles );
        $exportStatus = true;
        break;

    default:
        # code...
        break;
}

if ($exportStatus) {
    $cli->output( "Export was write in " . $exportPath . "." );
}

$cli->output( "Finished." );

$script->shutdown();

?>