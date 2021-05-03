CREATE TABLE `tblExtEditDocxLink` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `documentid` int(11) NOT NULL,
 `code` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
;

CREATE TABLE `tblExtEditDocxKey` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `documentid` int(11) NOT NULL,
 `documentkey` varchar(255) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `documentid` (`documentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
;