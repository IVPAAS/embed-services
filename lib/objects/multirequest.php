<?php
class Multirequest extends BaseObject{

    public $requireSerialization = true;

	function __construct() {
	}

	function isValidService($data) {
         if (isset($data["1:service"]) && $data["1:service"] == "baseEntry"){
            return true;
         } else {
            return false;
        }
    }

	function get() {
	    $baseEntry = new Baseentry();
	    $baseEntry->setClientConfiguration($this->rawDataString);
	    $entryContextData = new EntryContextData();
	    $entryContextData->setClientConfiguration($this->rawDataString);
	    $metaData = new Metadata();
	    $metaData->setClientConfiguration($this->rawDataString);
	    $cuePoints = new Cuepoints();
	    $cuePoints->setClientConfiguration($this->rawDataString);
		return array(
			$baseEntry ->get(),
			$entryContextData->get(),
			$metaData->get(),
			$cuePoints->get()
		);
	}
}