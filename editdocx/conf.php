<?php
$EXT_CONF['editdocx'] = array(
	'title' => 'Edit DOCX',
	'description' => 'Allows users to view and edit DOCX files online',
	'disable' => false,
	'version' => '1.0.0',
	'releasedate' => '2021-02-26',
	'author' => array('name'=>'Jeremy Micah Choa', 'email'=>'jeremy.choa@mgenesis.com.ph'),
	'config' => array(
		'list' => array(
			'title'=>'Example select menu from options',
			'type'=>'select',
			'options' => array('Option 1', 'Option 2', 'Option 3'),
			'multiple' => true,
			'size' => 2,
		)
	),
	'constraints' => array(
		'depends' => array('php' => '7.4.0-', 'seeddms' => '6.0.0-'),
	),
	'icon' => 'icon.png',
	'class' => array(
		'file' => 'class.editdocx.php',
		'name' => 'SeedDMS_EditDocx'
	)
);
?>
