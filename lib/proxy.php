<?php
	/**
	* 
	*/
	class ProxyRequest
	{
		private $response;
		private $logger;

		function __construct($service, $urlTokens){
		$this->logger = Logger::getLogger("main");
			$this->config = $this->getConfig();	
			foreach($this->config as $config){
				if (in_array($service, $config["services"])){

				    $this->logger->debug("Found request service ".$service." in config services");
				    if (isset($urlTokens[$config["token"]])){
				        $this->logger->debug("Found request service token ".$config["token"]." in request");
				        if ($config["decodeToken"] == "true"){
						    $partnerRequestData = json_decode($urlTokens[$config["token"]]);
						} else {
						    $partnerRequestData = $urlTokens[$config["token"]];
						}
						$this->get($config["type"], $config["method"], $config["redirectTo"], $partnerRequestData);
						$this->setData($config["dataStores"]);
					}
				}	
			}
		}

		function getConfig(){
		    global $gProxyConfig;
			$data = file_get_contents($gProxyConfig, FILE_USE_INCLUDE_PATH);
			return json_decode($data, TRUE);
		}

		function getDtoConfig($dtoName) {
			$dataStoreName = 'DTO_'.$dtoName;//strtolower(get_class($this));
			//if (!apc_exists($dataStoreName)) {
				//echo "Load from file: ".$dataStoreName;
				$data = file_get_contents('./TVinci/'.$dtoName/*strtolower(get_class($this))*/.".json", FILE_USE_INCLUDE_PATH);
				//apc_store($dataStoreName, $data);
			//} else {
			//	echo "Load from cache: ".$dataStoreName;
			//	$data = apc_fetch($dataStoreName);
			//}

			return json_decode($data, TRUE);
		}
	
		function get($type, $method, $url, $params){
            $this->logger->info("Routing request to ".$url);

            $data = json_encode($this->objectToArray($params), true);

            $this->logger->debug("Routing type: ".$type.", method: ".$method);
            $this->logger->debug("Routing request with params=". $data);

            $start = microtime(true);
			switch($type){
			    case "file":
			        $result = $this->getFile($method, $url, $data);
                    break;
			    case "rest":
			        $result = $this->getRest($method, $url, $data);
                    break;
			}
            $this->response = json_decode($result, true);

            if (empty($this->response) ||
                is_array($this->response) && count($this->response)){
                $this->logger->warn("Response is empty");
            }

            $this->logger->debug("Response=". $result);
            $total = microtime(true) - $start;
            $this->logger->info("Response time = ".$total. " seconds");
		}

		function setData($dataStores){
		    $this->logger->info("Set data");
			foreach ($dataStores as $dataStore => $container) {
			    $this->logger->debug("Set ".$dataStore." data in container ". $container);
				DataStore::getInstance()->setData($dataStore, $container, $this->response);
			}
		}

		function getRest($method, $url, $data = "")
        {
            $curl = curl_init();

            switch ($method)
            {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);

                    if ($data)
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:
                    if ($data)
                        $url = sprintf("%s?%s", $url, http_build_query($data));
            }

            // Optional Authentication:
            //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            return curl_exec($curl);

		}

		function getFile($method, $url, $data = ""){
            $fileParts = pathinfo($url);
            $data = trim($data, '"');
            if ($fileParts['filename'] == "*"){
                $file = $fileParts['dirname']."/".$data.".json";
            } else {
                $file = $fileParts['dirname']."/".$fileParts['filename']."_".$data.".".$fileParts["extension"];
            }
            $data = @file_get_contents($file, FILE_USE_INCLUDE_PATH);
            return $data;
        }

		function resolveObject($base, $extend){
			$newObj = array();			
			foreach ($base as $key=>$val) {
				if (is_array($val)){
					$newObj[$key] = $this->resolveObject($val, $extend[$key]);
				} else {
					$newObj[$key] = isset($extend[$key]) ? $extend[$key] : $val == "NULL" ? NULL : $val;
				}
			}			
			return $newObj;
		}

		function objectToArray($d) { 
			if (is_object($d)) { 
				$d = get_object_vars($d); 
			}   
			if (is_array($d)) { 
				return array_map(__METHOD__, $d); 
			} else { 
				return $d; 
			} 
		}
	}
?>