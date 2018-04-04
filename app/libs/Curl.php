<?php

//get data 
class Curl {
	private $ch;

	/**
	 * @function	construct function
	 **/
    public function __construct()
    {   
        $this->ch = curl_init();
    }   
	
	/**
	 * @function	destruct function
	 **/
    public function __destruct()
    {   
        curl_close($this->ch);
    } 	


	/**
	 * @function	get the data
	 * @param		$url:url
	 * @param		$timeOut:timeout
	 * @return		array():json data
	 **/
	public function get($url, $timeOut){
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeOut) ;
        $result = curl_exec($this->ch) ;
        if(!empty($result)){
			$result = json_decode($result,true);
		}
		return $result;
    }

	/**
	 * @function	post the data
	 * @param		$url:url
	 * @param		$postData:the data to be posted
	 * @param		$timeOut:timeout
	 * @return		array():json data
	 **/
	public function post($url, $postData, $timeOut){
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($this->ch, CURLOPT_POST, true) ;
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true) ;
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeOut) ;
        $result = curl_exec($this->ch) ;
        if(!empty($result)){
			$result = json_decode($result,true);
		}
		return $result;
    }

}

?>
