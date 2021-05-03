# seeddms-editdocx
Extension for SeedDMS that allows for the interactive editing of docx files. Requires a running OnlyOffice server.

To install, 
1) Edit the viewer.php and editor.php files inside the editdocx folder to use the URL of your SeedDMS installation.
2) Copy the editdocx folder to the ext folder of SeedDMS.
3) Copy the files inside the js folder to the directory "<seeddms root>/styles/bootstrap/onlyoffice" or whichever theme you will use. This has only been tested to work on the default bootstrap styles folder.
4) Run the SQL commands in tables.sql on the database that your SeedDMS installation is using.
If installed correctly, the options **Read DOCX** and **Edit DOCX** should appear when you visit the document page of a DOCX file.
