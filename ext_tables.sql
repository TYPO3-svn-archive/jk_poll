#
# Table structure for table 'tx_jkpoll_poll'
#
CREATE TABLE tx_jkpoll_poll (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(10) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	image blob NOT NULL,
	question text NOT NULL,
	votes text NOT NULL,
	answers text NOT NULL,
	colors text NOT NULL,	
	valid_till int(11) DEFAULT '0' NOT NULL,
	title_tag text NOT NULL,
    alternative_tag text NOT NULL,
    width int(11) DEFAULT '0' NOT NULL,
    height int(11) DEFAULT '0' NOT NULL,
    link tinytext NOT NULL,
    clickenlarge tinyint(3) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_jkpoll_iplog'
#
CREATE TABLE tx_jkpoll_iplog (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	ip varchar(15) DEFAULT '127.0.0.1' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_jkpoll_userlog'
#
CREATE TABLE tx_jkpoll_userlog (
	uid int(11) DEFAULT '0' NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	fe_user int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);