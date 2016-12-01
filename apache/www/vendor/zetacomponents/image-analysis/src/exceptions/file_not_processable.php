<?php
/**
 * File containing the ezcImageAnalyzerFileNotProcessableException.
 * 
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package ImageAnalysis
 * @version //autogen//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * The option name you tried to register is already in use.
 *
 * @package ImageAnalysis
 * @version //autogen//
 */
class ezcImageAnalyzerFileNotProcessableException extends ezcImageAnalyzerException
{
    /**
     * Creates a new ezcImageAnalyzerFileNotProcessableException.
     * 
     * @param string $file   Not processable file.
     * @param string $reason Reason that the file is not processable.
     * @return void
     */
    function __construct( $file, $reason = null )
    {
        $reasonPart = '';
        if ( $reason )
        {
            $reasonPart = " Reason: $reason.";
        }
        parent::__construct( "Could not process file '{$file}'.{$reasonPart}" );
    }
}

?>