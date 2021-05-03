<?php
    include("../../inc/inc.Settings.php");
    include("../../inc/inc.Utils.php");
    include("../../inc/inc.Language.php");
    include("../../inc/inc.Init.php");
    include("../../inc/inc.Extension.php");
    include("../../inc/inc.DBInit.php");
    include("../../inc/inc.ClassUI.php");
    include("../../inc/inc.Authentication.php");
    
    function checkTempLink($dms, $code) {
        $db = $dms->getDB();
        
        $queryStr = "SELECT * FROM `tblExtEditDocxLink` WHERE code = ".$db->qstr($code);
        $result = $db->getResultArray($queryStr);
        if (!$result || empty($result)) {
		    echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        //return true;
	        return $result[0]['documentid']; 
	    };
    }
    
    function getDocKey($dms, $documentid) {
        $db = $dms->getDB();
        
        $queryStr = "SELECT * FROM `tblExtEditDocxKey` WHERE documentid = ".$db->qstr($documentid);
        $result = $db->getResultArray($queryStr);
        if (!$result || empty($result)) {
		    echo $db->getErrorMsg();
			return false;
		}
	    else {
	        //print_r($db->getResultArray($queryStr));
	        //return true;
	        return $result[0]['documentkey']; 
	    };
    }
    
    $docname = '';
    if (isset($_GET["docname"]) && $_GET["docname"] != '') {
        $docname = rawurlencode($_GET["docname"]);
    }
    
    // check if the request included the code parameter
    if (!isset($_GET["code"]) || $_GET["code"] == '') {
        UI::exitError(getMLText("document_title", array("documentname" => $docname)),getMLText("error_occured"));
    }
    
    // check if the code is a valid one
    $documentid = checkTempLink($dms, $_GET["code"]);
    if (!$documentid) {
    	UI::exitError(getMLText("document_title", array("documentname" => getMLText("invalid_doc_id"))),getMLText("invalid_doc_id"));
    }
    
    // check if a key exists for this particular document (i.e. no modifications have been saved to the document)
    $documentkey = getDocKey($dms, $documentid);
    if (!$documentkey || empty($documentkey)) {
        echo "Error getting document key";
        UI::exitError(getMLText("document_title", array("documentname" => $docname)),getMLText("error_occured"));
    }
    
?>
<html>
    <head>
        <script type="text/javascript" src="../../views/bootstrap/vendors/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="http://<YOUR SEEDDMS URL HERE>:3090/web-apps/apps/api/documents/api.js"></script>
        <script type="text/javascript" src="../../styles/bootstrap/onlyoffice/editorsetup.js"></script>
    </head>
    <body>
        <div class="well">
            <form>
                <input type="hidden" id="code" value=<?php echo $_GET["code"] ?>></input>
                <input type="hidden" id="doc_name" value=<?php echo $docname ?>></input>
                <input type="hidden" id="doc_key" value=<?php echo $documentkey ?>></input>
                <input type="hidden" id="doc_id" value=<?php echo $documentid ?>></input>
            </form>
            <div id="placeholder"></div>
        </div>
    </body>
</html>