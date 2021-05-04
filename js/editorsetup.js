$(document).ready( function () {
    var code = $('#code').val();
    var doc_name = $('#doc_name').val();
    var doc_id = $('#doc_id').val();
    var doc_key = $('#doc_key').val();
    var user_id = $('#user_id').val();
    var user_name = $('#user_name').val();
    doc_name = doc_name.replace('%20', ' ');
    
    var docEditor;

        var innerAlert = function (message) {
            if (console && console.log)
                console.log(message);
        };

        var onAppReady = function () {
            innerAlert("Document editor ready");
        };
        
        var onDocumentStateChange = function (event) {
            var title = document.title.replace(/\*$/g, "");
            document.title = title + (event.data ? "*" : "");
        };
        
        var onError = function (event) {
            if (event)
                innerAlert(event.data);
        };

        var onOutdatedVersion = function (event) {
            location.reload(true);
        };
        
        var replaceActionLink = function(href, linkParam) {
            var link;
            var actionIndex = href.indexOf("&action=");
            if (actionIndex != -1) {
                var endIndex = href.indexOf("&", actionIndex + "&action=".length);
                if (endIndex != -1) {
                    link = href.substring(0, actionIndex) + href.substring(endIndex) + "&action=" + encodeURIComponent(linkParam);
                } else {
                    link = href.substring(0, actionIndex) + "&action=" + encodeURIComponent(linkParam);
                }
            } else {
                link = href + "&action=" + encodeURIComponent(linkParam);
            }
            return link;
        }
        
        var onMakeActionLink = function (event) {
            var actionData = event.data;
            var linkParam = JSON.stringify(actionData);
            docEditor.setActionLink(replaceActionLink(location.href, linkParam));
        };
        
        var connectEditor = function () {

            docEditor = new DocsAPI.DocEditor("placeholder", {"width": "100%",
                "height": "100%",
                "type": "desktop",
                "documentType": "text",
                "document": {
                    "title": doc_name,
                    "url": "https://seed.jhc.yxe.mybluehost.me/ext/editdocx/download?code=".concat(code),
                    //"url": "https://seed.jhc.yxe.mybluehost.me/ext/editdocx/op.GetFile.php?editdocx_documentid=".concat(editdocx_documentid).concat("&editdocx_documentversion=").concat(editdocx_documentversion),
                    "fileType": "docx",
                    "key": doc_key,
                    "permissions": {
                        "comment": false,
                        "download": false,
                        "edit": true,
                        "print": false,
                        "fillForms": false,
                        "modifyFilter": true,
                        "modifyContentControl": true,
                        "review": false
                    }
                },
                
                "editorConfig": {
                    "actionLink": null,
                    "mode": "edit",
                    "lang": "en",
                    "callbackUrl": "https://seed.jhc.yxe.mybluehost.me/ext/editdocx/receiver",
                    "user": {
                        "id": user_id,
                        "name": user_name
                    },
                    "customization": {
                        "about": false,
                        "chat": false,
                        "comments": false,
                        "feedback": false,
                        "forcesave": false,
                        "goback": {
                            "blank": false,
                            "requestClose": false,
                            "text": "Go back to SeedDMS",
                            "url": "https://seed.jhc.yxe.mybluehost.me/out/out.ViewDocument.php?documentid=".concat(doc_id)
                        }
                    },
                    "fileChoiceUrl": "",
                    "plugins": {"pluginsData":[]}
                },
                
                events: {
                    "onAppReady": onAppReady,
                    "onDocumentStateChange": onDocumentStateChange,
                    "onError": onError,
                    "onOutdatedVersion": onOutdatedVersion,
                    "onMakeActionLink": onMakeActionLink,
                }
            });
        
            fixSize();
        };
        
        var fixSize = function () {
            var wrapEl = document.getElementsByClassName("form");
            if (wrapEl.length) {
                wrapEl[0].style.height = screen.availHeight + "px";
                window.scrollTo(0, -1);
                wrapEl[0].style.height = window.innerHeight + "px";
            }
        };
                
        if (window.addEventListener) {
            window.addEventListener("load", connectEditor);
            window.addEventListener("resize", fixSize);
        } else if (window.attachEvent) {
            window.attachEvent("onload", connectEditor);
            window.attachEvent("onresize", fixSize);
        }

});
        
        