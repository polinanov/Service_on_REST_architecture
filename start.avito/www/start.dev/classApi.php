<?php
define ("LENGTH", 50);
abstract class Api{
    public $apiName = ''; 

    protected $method = ''; 

    public $requestUri = [];
    public $requestParams = [];

    protected $action = ''; 

    public function __construct(){
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");
		
        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'],'/'));
		$postData = file_get_contents('php://input');
		if($temp = json_decode($postData, true)){
			$_POST = $temp;
			$this->requestParams = $_POST;		
			$this->method = 'POST';

		}else{
			$this->requestParams = $_REQUEST;		
			$this->method = $_SERVER['REQUEST_METHOD'];
		}
	}
    public function run(){
		if($_GET){
			$this->apiName = "retrieve";
		}
		else if ($_POST)
			$this->apiName = 'generate';
        if(array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== $this->apiName){
            throw new RuntimeException('API Not Found', 404);
        }
        $this->action = $this->getAction();
        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }

    protected function response($data, $status = 500){
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }

    private function requestStatus($code){
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code])?$status[$code]:$status[500];
    }

    protected function getAction(){
        $method = $this->method;
        switch ($method) {
            case 'GET':
                if($this->requestUri){
                    return 'retrieveAction';
                }
                break;
            case 'POST':
					return 'generateAction';
                break;
            default:
                return null;
        }
    }
	abstract protected function retrieveAction();
    abstract protected function generateAction();
}


class UsersApi extends Api{
	public $apiName;
	public function connectBD(){
		$link = mysqli_connect( 
            'mysql',
            'polinanov_avito',     
            'polinanov_avito',  
            'polinanov_avito');
		if (!$link){ 
		   return $this->response("Internal Error BD", 500);
		   exit; 
		}else return $link; 	
	}
	
	public function getGUID(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}
		else {
			mt_srand((double)microtime()*10000);
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
			return $uuid;
		}
	}
	
    public function retrieveAction(){
		$link = $this->connectBD();
		$id = $this->requestParams['id'];
		if(!ctype_digit($id)) return $this->response("Method Not Allowed", 405);
		if ($result = mysqli_query($link, "SELECT * FROM polinanov_avito WHERE id='$id'")){ 
			while( $row = mysqli_fetch_assoc($result) ){ 
				$response_string = $row['id']." : ". $row['num'];
				return $this->response($response_string, 200);
			}
			if(empty($row)){
				return $this->response("Data not found", 404);
			}
			mysqli_free_result($result); 
		}else{
			return $this->response("Internal Error BD", 500);
		}	 
		mysqli_close($link); 

	}

    public function generateAction(){
		$link = $this->connectBD();
		$flag = false;
		$generate_ = '';
		switch($this->requestParams['type']){
			case "guid":
				$flag = true;
				break;
			case "int":
			case "string":
			case "alphanumeric":
				if(!empty($this->requestParams['len']) && ctype_digit($this->requestParams['len']) && ($this->requestParams['len']<LENGTH)){
					$flag = true;
				}else return $this->response("Method Not Allowed", 405);
				//echo "int string alphanumeric " . $flag;
				break;
			case "setvalue":
				if(!empty($this->requestParams['len']) && ctype_digit($this->requestParams['len']) && !empty($this->requestParams['setvalue']) && ($this->requestParams['len']<LENGTH)){
					$flag = true;
				}else return $this->response("Method Not Allowed", 405);
				//echo "setvalue " . $flag;
				break;
			default: 
				return $this->response("Method Not Allowed", 405);
				break;
		}
		if(!$flag){
			$generate_ = rand();
		}else{
			srand((double)microtime() * 10000);
				switch ($this->requestParams['type']){
					case "string": 
						$array = array_merge(range('a','z'), range('A','Z'));
						for($i = 0; $i < $this->requestParams['len']; $i++){
									$generate_ .= $array[mt_rand(0, 51)];
						}
						break;
					case "int": 
						$array = range('0','9');
						for($i = 0; $i < $this->requestParams['len']; $i++){
									$generate_ .= $array[mt_rand(0, 9)];
						}
						break;
					case "guid": 
						$generate_ = $this->getGUID();
						break;
					case "alphanumeric": 
						$array = array_merge(range('a','z'), range('A','Z'), range('0','9'));
						for($i = 0; $i < $this->requestParams['len']; $i++){
									$generate_ .= $array[mt_rand(0, 61)];
						}
						break;
					case "setvalue": 
						$len = strlen($this->requestParams['setvalue']);
						$array = $this->requestParams['setvalue'];
						for($i = 0; $i < $this->requestParams['len']; $i++){
									$generate_ .= $array[mt_rand(0, ($len-1))];
						}
						break;
					default:  
						return $this->response("Method Not Allowed", 405);
						break;
				}
		}
		if ($result = mysqli_query($link, "INSERT INTO polinanov_avito (num) VALUE ('$generate_')")){ 
		} else{
			return $this->response("Internal Error BD", 500);
			}
		if ($result = mysqli_query($link, "SELECT * FROM polinanov_avito WHERE num='$generate_'")){ 
			while( $row = mysqli_fetch_assoc($result) ){  
				$response_string = $row['id']." : ". $row['num'];
				return $this->response($response_string, 200);
			}
			mysqli_free_result($result); 
		}else{
			return $this->response("Internal Error BD", 500);
		}
		mysqli_close($link); 	
	}
}
?>
