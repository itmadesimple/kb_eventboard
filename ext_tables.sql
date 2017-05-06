#
# Table structure for table 'tx_kbeventboard_locations'
#
CREATE TABLE tx_kbeventboard_locations (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	locationname tinytext NOT NULL,
	street tinytext NOT NULL,
	zip tinytext NOT NULL,
	city tinytext NOT NULL,
	logo blob NOT NULL,
	alttext tinytext NOT NULL,
    titletext tinytext NOT NULL,
    subtitletext tinytext NOT NULL,
	locationdescription text NOT NULL,
	homepage tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kbeventboard_category'
#
CREATE TABLE tx_kbeventboard_category (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	category tinytext NOT NULL,
	categorydescription tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_kbeventboard_events'
#
CREATE TABLE tx_kbeventboard_events (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	eventname tinytext NOT NULL,
	datebegin int(11) DEFAULT '0' NOT NULL,
	dateend int(11) DEFAULT '0' NOT NULL,
	location int(11) DEFAULT '0' NOT NULL,
	category text NOT NULL,
	startingtime tinytext NOT NULL,
	delaytime tinytext NOT NULL,
	price tinytext NOT NULL,
	teaserdescription text NOT NULL,
	teaserimages blob NOT NULL,
	teaserimagesalt mediumtext NOT NULL,
    teaserimagestitle mediumtext NOT NULL,
    teaserimagessubtitle mediumtext NOT NULL,
    imagesalt mediumtext NOT NULL,
    imagestitle mediumtext NOT NULL,
    imagessubtitle mediumtext NOT NULL,
	images blob NOT NULL,
	eventdescription text NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);