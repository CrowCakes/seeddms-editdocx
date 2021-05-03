# seeddms-editdocx
Extension for SeedDMS that allows for the interactive editing of docx files. Requires a running OnlyOffice Document server listening on port 3090, although this can be edited in the viewer.php, editor.php, viewersetup.js, and editorsetup.js files.

To install, 
1) Edit the viewer.php and editor.php inside the editdocx folder to use the URL of your SeedDMS installation.
2) Edit the viewersetup.js and editorsetup.js files inside the js folder to use the URL of your SeedDMS installation.
3) Copy the editdocx folder to the ext folder of SeedDMS.
4) Copy the files inside the js folder to the directory "<seeddms root>/styles/bootstrap/onlyoffice" or whichever theme you will use. This has only been tested to work on the default bootstrap styles folder.
5) Run the SQL commands in tables.sql on the database that your SeedDMS installation is using.

If installed correctly, the options **Read DOCX** and **Edit DOCX** should appear when you visit the document page of a DOCX file.
