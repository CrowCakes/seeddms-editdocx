<?php
    include("../../inc/inc.Settings.php");
    include("../../inc/inc.Utils.php");
    include("../../inc/inc.Language.php");
    include("../../inc/inc.Init.php");
    include("../../inc/inc.Extension.php");
    include("../../inc/inc.DBInit.php");
    include("../../inc/inc.ClassUI.php");
    include("../../inc/inc.Authentication.php");
    
    // check if the request included the document id parameter
    if (!isset($_GET["readdocx_documentid"]) || !is_numeric($_GET["readdocx_documentid"]) || intval($_GET["readdocx_documentid"])<1) {
        //echo is_numeric($_GET["documentid"]) ? "true" : "false";
        UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
    }
    
    $documentname = $_GET["readdocx_documentname"];
    $documentid = $_GET["readdocx_documentid"];
    $document = $dms->getDocument($documentid);
    
    // check if the document id is a valid one
    if (!is_object($document)) {
    	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
    }
    
    // check for appropriate access rights
    if ($document->getAccessMode($user) < M_READ) {
    	UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("access_denied"));
    }
    
    $code = createTempLink($dms, $documentid);
    
    if ($code != '') {
        createKey($dms, $documentid);
        header("Location:./viewer.php?code=".$code."&docname=".$documentname);
    }
    else UI::exitError(getMLText("document_title", array("documentname" => $document->getName())),getMLText("error_occured"));

    function createTempLink($dms, $documentid) {
        $db = $dms->getDB();
        
        $generatedCode = createCode($documentid);
        
        $queryStr = "INSERT INTO `tblExtEditDocxLink` (`documentid`, `code`) VALUES (".$db->qstr($documentid).", ".$db->qstr($generatedCode).")";
        //echo $queryStr;
        if (!$db->getResult($queryStr)) {
		    echo $db->getErrorMsg();
			return '';
		}
        else return $generatedCode;
    }
    
    function createKey($dms, $documentid) {
        $db = $dms->getDB();
        
        $generatedKey = createCode("key".$documentid);
        
        $queryStr = "INSERT INTO `tblExtEditDocxKey` (`documentid`, `documentkey`) VALUES (".$db->qstr($documentid).", ".$db->qstr($generatedKey).")";
        
        if (!$db->getResult($queryStr)) {
		    //echo $db->getErrorMsg();
			return false;
		}
        else return true; //return $generatedKey;
    }
    
    function createCode($documentid) {
        $date = date("D M d, Y G:i");
        return sha1($documentid . $date);
    }
?>