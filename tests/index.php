<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL) 
 * v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 * 
 * If using the Work as, or as part of, a network application, by 
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading, 
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */



session_start();

require(__DIR__ . "/../vendor/autoload.php");

use fiftyone\pipeline\core\pipelineBuilder;
use fiftyone\pipeline\engines\aspectDataDictionary;
use fiftyone\pipeline\engines\engine;
use fiftyone\pipeline\engines\sessionCache;

use PHPUnit\Framework\TestCase;

// Test creating engine

class exampleAspectEngine extends engine {

    public $dataKey = "example";

    public function processInternal($flowData){

        $data = new aspectDataDictionary($this, array("integer" => 5));

        $flowData->setElementData($data);

    }

    public $properties = array(
        "integer" => array(
            "type" => "int"
        ),
        "boolean" => array(
            "type" => "bool"
        )
    );

    public $restrictedProperties = ["integer"];

}

class EngineTests extends TestCase
{
    function createAndProcess()
    {
        $engine = new exampleAspectEngine();

        $engine->setCache(new sessionCache(2));

        // Simple pipeline

        $pipeline = (new pipelineBuilder())->add($engine)->build();

        $flowData = $pipeline->createFlowData();

        $flowData->process();

        return $flowData;
    }


    function testEngine(){
        $flowData = $this->createAndProcess();

        $this->assertTrue($flowData->get("example")->get("integer") === 5);
    }


    // Test missing property service
    function testMissingProperty()
    {
        $flowData = $this->createAndProcess();

        try {

            $flowData->get("example")->get("test");
            
        } catch(Exception $e){

            $missingPropertyError = true;

        }

        $this->assertTrue($missingPropertyError);
    }

    // Test restricted property list
    function testRestrictedProperty()
    {
        $flowData = $this->createAndProcess();

        try {

            $flowData->get("example")->get("boolean");
            
        } catch(Exception $e){

            $restrictedProperties = true;

        }

        $this->assertTrue($restrictedProperties);
    }

}
