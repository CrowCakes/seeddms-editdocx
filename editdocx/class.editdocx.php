<?php
/**
 * Example extension
 *
 * @author  Uwe Steinmann <uwe@steinmann.cx>
 * @package SeedDMS
 * @subpackage  example
 */
class SeedDMS_EditDocx extends SeedDMS_ExtBase {

	/**
	 * Initialization
	 *
	 * Use this method to do some initialization like setting up the hooks
	 * You have access to the following global variables:
	 * $GLOBALS['settings'] : current global configuration
	 * $GLOBALS['settings']->_extensions['example'] : configuration of this extension
	 * $GLOBALS['LANG'] : the language array with translations for all languages
	 * $GLOBALS['SEEDDMS_HOOKS'] : all hooks added so far
	 */
	function init() { /* {{{ */
		$GLOBALS['SEEDDMS_HOOKS']['initDMS'][] = new SeedDMS_EditDocx_InitDMS;
		$GLOBALS['SEEDDMS_HOOKS']['view']['viewDocument'][] = new SeedDMS_EditDocx_ViewDocument;
	} /* }}} */

	function main() { /* {{{ */
	} /* }}} */
}

/**
 * Class containing methods for hooks when the dms is initialized
 *
 * @author  Uwe Steinmann <uwe@steinmann.cx>
 * @package SeedDMS
 * @subpackage  example
 */
class SeedDMS_EditDocx_InitDMS { /* {{{ */

	/**
	 * Hook after initializing the application
	 *
	 * This method sets the callback 'onAttributeValidate' in SeedDMS_Core
	 */
	public function addRoute($arr) { /* {{{ */
		$dms = $arr['dms'];
		$settings = $arr['settings'];
		$app = $arr['app'];

		$container = $app->getContainer();
		
		$container['HomeController'] = function($c) use ($dms, $settings) {
//			$view = $c->get("view"); // retrieve the 'view' from the container
			return new SeedDMS_EditDocx_HomeController($dms, $settings);
		};
		
		$container['FileController'] = function($c) use ($dms, $settings) {
//			$view = $c->get("view"); // retrieve the 'view' from the container
			return new SeedDMS_EditDocx_FileController($dms, $settings);
		};
		
		$app->get('/ext/editdocx/home', 'HomeController:home');
		
		$app->get('/ext/editdocx/download', 'FileController:downloadfile');
		
		$app->post('/ext/editdocx/receiver', 'FileController:receiver');

		$app->get('/ext/editdocx/echos',
			function ($request, $response, $args) use ($app) {
				echo "Output of route /ext/editdocx/echo ".$_GET[message];
			}
		);
		return null;
	} /* }}} */

} /* }}} */

class SeedDMS_EditDocx_HomeController { /* {{{ */

	protected $dms;

	protected $settings;

	public function __construct($dms, $settings) {
		$this->dms = $dms;
		$this->settings = $settings;
	}

	public function home($request, $response, $args) {
		$response->getBody()->write('Output of route /ext/example/home'.get_class($this->dms));
		return $response;
	}

	public function echos($request, $response, $args) {
		$response->getBody()->write('Output of route /ext/example/echo');
		return $response;
	}
} /* }}} */

class SeedDMS_EditDocx_FileController { /* {{{ */

	protected $dms;

	protected $settings;

	public function __construct($dms, $settings) {
		$this->dms = $dms;
		$this->settings = $settings;
	}
	
	public function downloadfile($request, $response, $args) {
	    // check if the request included the code parameter
        if (!isset($_GET["code"]) || $_GET["code"] == '') {
            $newResponse = $response->withStatus(400);
            $newResponse->getBody()->write('Bad request');
            return $newResponse;
        }
        
	    $code = $_GET["code"];
	    //$this->sendlog("Got code ".$code, "receiver.log");
        
        // extract the DocumentID by retrieving the corresponding database entry
		// also delete the one-time-use code
		$documentid = $this->getDocumentIDFromLink($this->dms, $code);
        $deleteresult = $this->deleteLink($this->dms, $code);
		
		// check if the function got something or not
		if (!$documentid) {
            $newResponse = $response->withStatus(500);
            $newResponse->getBody()->write('No corresponding id found');
            return $newResponse;
        }
		
		// retrieve corresponding Document object from DMS
        $document = $this->dms->getDocument($documentid);
        
        // check if a corresponding Document was found or not
        if (!is_object($document)) {
            $newResponse = $response->withStatus(500);
            $newResponse->getBody()->write('Invalid code');
            return $newResponse;
        }
        
        // get that Document's content
        $content = $document->getLatestContent();
        
        // not sure how an error would happen
        if (!is_object($content)) {
            $newResponse = $response->withStatus(500);
            $newResponse->getBody()->write('Error retrieving files');
            return $newResponse;
        }
        
        // get file path of the Document's file
        $file = $this->dms->contentDir . $content->getPath();
        
        try {
            // attempt to open the file
            if(!($fh = @fopen($file, 'rb'))) {
        		$newResponse = $response->withStatus(500);
                $newResponse->getBody()->write('Error reading files');
                return $newResponse;
        	}
        	else {
        	    // FYI, this runs on Slim Framework
        	    // it is never stated anywhere in the base SeedDMS build
        	    $stream = new \Slim\Http\Stream($fh); // create a stream instance for the response body
        	    
        	    // get the file's original name
        	    $efilename = rawurlencode($content->getOriginalFileName());
        	    
        	    // send the header over
        	    return $response->withHeader('Content-Type', $content->getMimeType())
                              ->withHeader('Content-Description', 'File Transfer')
                              ->withHeader('Content-Transfer-Encoding', 'binary')
                              ->withHeader('Content-Disposition', 'attachment; filename="' . $efilename . '"')
                              ->withHeader('Content-Length', filesize($file))
                              ->withHeader('Expires', '0')
                              ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                              ->withHeader('Pragma', 'no-cache')
                              ->withBody($stream);
        
        		//sendFile($dms->contentDir . $content->getPath());
        	}
        }
        catch (Exception $e) {
            $newResponse = $response->withStatus(500);
            $newResponse->getBody()->write('Error sending files');
            return $newResponse;
        }
        finally {
            // remove the one-use code
            $this->deleteLink($this->dms, $code);
        }
	}
	
	public function receiver($request, $response, $args) {
	    //$this->sendlog("Track START", "receiver.log");
        //$this->sendlog("_GET params: " . serialize( $_GET ), "receiver.log");
	    
	    if (($body_stream = file_get_contents("php://input"))===FALSE){
	        $responsejson = array("error" => "Bad request");
            $newResponse = $response->withJSON($responsejson);
		    return $newResponse;
		    
		    //echo "{\"error\": \"Bad request\"}";
        }
        
        $data = json_decode($body_stream, TRUE);
        
        if ($data === NULL) {
            $responsejson = array("error" => "Invalid JSON");
            $newResponse = $response->withJSON($responsejson);
            return $newResponse;
            
            //echo "{\"error\": \"Invalid JSON\"}";
        }
        
        //$this->sendlog("InputStream data: " . serialize($data), "receiver.log");
        
        if ($data["status"] == 2 || $data["status"] == 3){
            $downloadUri = $data["url"];
            $saved = 1;
            
            // try to download the modified document file from the document editing service
            if (($new_data = file_get_contents($downloadUri))===FALSE){
                $responsejson = array("error" => "Couldn't download file", "url" => $downloadUri);
                $newResponse = $response->withJSON($responsejson);
		        return $newResponse;
		        
		        //echo "{\"error\": \"Bad request\"}";
		        //$saved = 0;
            } 
            else if (empty($data["key"])) {
                $responsejson = array("error" => "Missing key");
                $newResponse = $response->withJSON($responsejson);
		        return $newResponse;
            }
            else {
                // get the document's file path
                $documentid = $this->getDocumentIDFromKey($this->dms, $data["key"]);
                $document = $this->dms->getDocument($documentid);
                $content = $document->getLatestContent();
                $file = $this->dms->contentDir . $content->getPath();
            
                file_put_contents($file, $new_data, LOCK_EX);
            }
            
            $result["c"] = "saved";
            $result["status"] = $saved;
        }
        
        //$this->sendlog("track result: " . serialize($result), "receiver.log");
        
        $responsejson = array("error" => 0);
        $newResponse = $response->withJSON($responsejson);
        return $newResponse;
        
        //echo "{\"error\": 0}";
	}
	
	private function getDocumentIDFromLink($dms, $code) {
        $db = $dms->getDB();
        
        $queryStr = "SELECT * FROM `tblExtEditDocxLink` WHERE code = ".$db->qstr($code);
        $result = $db->getResultArray($queryStr);
        if (!$result || empty($result)) {
		    //echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        return $result[0]['documentid'];
	    };
    }
    
    private function getDocumentIDFromKey($dms, $documentkey) {
        $db = $dms->getDB();
        
        $queryStr = "SELECT * FROM `tblExtEditDocxKey` WHERE documentkey = ".$db->qstr($documentkey);
        $result = $db->getResultArray($queryStr);
        if (!$result || empty($result)) {
		    echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        $documentid = $result[0]['documentid'];
	        $this->deleteKey($dms, $documentid);
	        return $documentid;
	    };
    }
    
    private function deleteLink($dms, $code) {
        $db = $dms->getDB();
        
        $queryStr = "DELETE FROM tblExtEditDocxLink WHERE code = ".$db->qstr($code);
        $result = $db->getResult($queryStr);
        if (!$result) {
		    //echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        return true;
	    };
    }
    
    private function deleteKey($dms, $documentid) {
        $db = $dms->getDB();
        
        $queryStr = "DELETE FROM `tblExtEditDocxKey` WHERE documentid = ".$db->qstr($documentid);
        $result = $db->getResult($queryStr);
        if (!$result) {
		    //echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        return true;
	    };
    }
    
    private function sendlog($msg, $logFileName) {
        $logsFolder = __DIR__ . "/logs/";
        $date = date("D M d, Y G:i");
        if (!file_exists($logsFolder)) {
            mkdir($logsFolder);
        }
        file_put_contents($logsFolder . $logFileName, $date . ": ". $msg . PHP_EOL, FILE_APPEND);
    }
} /* }}} */

class SeedDMS_EditDocx_ViewDocument {
    
    function extraVersionViews($view, $latestContent) {
        if ($latestContent->getFileType() == '.docx')
            $htmlcontent = array(
                array('link' => '../ext/editdocx/op.CreateReadLink.php?readdocx_documentid='.$latestContent->getDocument()->getId().'&readdocx_documentname='.rawurlencode($latestContent->getOriginalFileName()), 
                    'label' => 'Read DOCX'), 
                array('link' => '../ext/editdocx/op.CreateLink.php?editdocx_documentid='.$latestContent->getDocument()->getId().'&editdocx_documentname='.rawurlencode($latestContent->getOriginalFileName()), 
                    'label' => 'Edit DOCX')
            );
        else $htmlcontent = array();
        return $htmlcontent;
    }
}
?>