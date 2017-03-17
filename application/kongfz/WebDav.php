<?php

namespace kongfz;

/**
 * WebDav 操作工具类 
 * @author zhengyin <zhengyin@kongfz.com> dongnan <dongyh@126.com>
 */
class WebDav {

    private $serverUrl;
    private $timeout = 3;
    private $errCode = 0;
    private $errInfo = '';

    public function __construct($serverUrl) {
        $this->serverUrl = rtrim($serverUrl, '/') . '/';
    }

    public function setTimeout($timeout) {
        $this->timeout = intval($timeout) > 0 ? $timeout : $this->timeout;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrInfo() {
        return $this->errInfo;
    }

    /**
     * 获取文件
     * @param String $filename        	
     * @return Ambigous <mixed>|string
     * 	#curl示例: curl -v http://dav.kongfz.com/newpc/20160527/1034285/2kj66fn3mx_b.jpg
     */
    public function get($filename) {
        $result = $this->exec($filename, "GET");
        if ($result['response']['http_code'] == 200) {
            return $result['body'];
        }

        $this->errCode = 2;
        $this->errInfo = 'response error ' . json_encode($result['response']);
        return '';
    }

    /**
     * 提交一个新文件
     * @param String $srcfile,$filename        	
     * @return boolean
     * 	#curl示例: curl -v -X PUT -F@filename=favicon.png http://dav.kongfz.com/newpc/20160527/1034285/2kj66fn3mx_b.jpg
     */
    public function put($srcfile, $filename) {
        if (!file_exists($srcfile)) {
            $this->errCode = 1;
            $this->errInfo = "file not exists , path:" . $srcfile;
            return false;
        }
        $result = $this->exec($filename, "PUT", $srcfile);
        if ($result['response']['http_code'] == 204 || $result['response']['http_code'] == 201) {
            return true;
        }
        $this->errCode = 2;
        $this->errInfo = 'response error ' . json_encode($result['response']);
        $this->errInfo = json_encode($result);
        return false;
    }

    /**
     * 删除新文件
     * @param String $filename        	
     * @return boolean
     * #curl 示例: curl -X delete http://dav.kongfz.com/newpc/20160527/1034285/2kj66fn3mx_b.jpg
     */
    public function delete($filename) {
        $result = $this->exec($filename, "DELETE");
        if ($result['response']['http_code'] == 204) {
            return true;
        }
        $this->errCode = 2;
        $this->errInfo = 'response error ' . json_encode($result['response']);
        return false;
    }

    public function fileExists($filename) {
        $result = $this->exec($filename, "GET");
        if ($result['response']['http_code'] == 200) {
            return true;
        }
        return false;
    }

    /**
     * 执行 curl
     * 
     * @param String $filename        	
     * @param String $operation        	
     * @param String $srcfile        	
     */
    private function exec($filename, $operation, $srcfile = null) {
        $remoteUrl = $this->serverUrl . ltrim($filename, '/');
        $ch        = curl_init();
        switch (strtoupper($operation)) {
            case "GET" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT" :
                /*
                  $cFile = curl_file_create($srcfile,'',''); // php 5.5 +
                  $fields = array('cfile'=>$cFile);
                  curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
                  curl_setopt ( $ch, CURLOPT_INFILESIZE, filesize ( $srcfile ) );
                  curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
                  break;
                 */
                $fileHandler = fopen($srcfile, "r");
                curl_setopt($ch, CURLOPT_UPLOAD, true);
                curl_setopt($ch, CURLOPT_INFILE, $fileHandler);
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($srcfile));
                curl_setopt($ch, CURLOPT_PUT, true);
                break;
            case "DELETE" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        curl_setopt($ch, CURLOPT_URL, $remoteUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = array();

        try {
            $result['body']     = curl_exec($ch);
            $result['response'] = curl_getinfo($ch);
        } catch (\Exception $e) {
            $this->errCode = $e->getCode();
            $this->errInfo = $e->getMessage();
        }

        if (!empty($fileHandler)) {
            fclose($fileHandler);
        }

        curl_close($ch);
        return $result;
    }

}
