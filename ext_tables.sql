#
# Extend table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    nessa_team_member_element int(11) DEFAULT '0' NOT NULL,
    nessa_teaser_element int(11) DEFAULT '0' NOT NULL,
    nessa_hero_element int(11) DEFAULT '0' NOT NULL,
    nessa_copyright varchar(255) DEFAULT '0' NOT NULL,
		tx_starter_ctalink varchar(1024) DEFAULT '' NOT NULL,
		tx_starter_ctalink_text varchar(255) DEFAULT '' NOT NULL,
		nessa_column_layout varchar(10) DEFAULT 'col-md-4' NOT NULL
);

#
# Extend table structure for table 'sys_file_metadata'
#
CREATE TABLE sys_file_metadata (
    nessa_portfolio_link varchar(1024) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_starternessa_team_element'
#
CREATE TABLE tx_starternessa_team_element (
    tt_content_record int(11) DEFAULT '0' NOT NULL,
    realname varchar(255) DEFAULT '' NOT NULL,
    company_position varchar(255) DEFAULT '' NOT NULL,
    email varchar(1024) DEFAULT '' NOT NULL,
    assets int(11) DEFAULT '0' NOT NULL,
    bodytext text,

    KEY parent (pid,sorting),
    KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_starternessa_teaser_element'
#
CREATE TABLE tx_starternessa_teaser_element (
    tt_content_record int(11) DEFAULT '0' NOT NULL,
    header varchar(255) DEFAULT '' NOT NULL,
    icon varchar(30) DEFAULT '' NOT NULL,
    assets int(11) DEFAULT '0' NOT NULL,
    link varchar(1024) DEFAULT '' NOT NULL,
    link_text varchar(255) DEFAULT '' NOT NULL,
    bodytext text,

    KEY parent (pid,sorting),
    KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_starternessa_hero_element'
#
CREATE TABLE tx_starternessa_hero_element (
    tt_content_record int(11) DEFAULT '0' NOT NULL,
    header varchar(255) DEFAULT '' NOT NULL,
    assets int(11) DEFAULT '0' NOT NULL,
    bodytext text,
    ctalink varchar(1024) DEFAULT '' NOT NULL,
    ctalink_text varchar(255) DEFAULT '' NOT NULL,

    KEY parent (pid,sorting),
    KEY language (l10n_parent,sys_language_uid)
);
