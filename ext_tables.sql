#
# Table structure for table 'sys_workspace'
#
CREATE TABLE sys_workspace (
	tx_irreworkspaces_action_recipient_mode int(11) DEFAULT '0' NOT NULL,
	tx_irreworkspaces_stage_editing_recipient_mode int(11) DEFAULT '0' NOT NULL,
	tx_irreworkspaces_stage_publish_recipient_mode int(11) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'sys_workspace_stage'
#
CREATE TABLE sys_workspace_stage (
	tx_irreworkspaces_recipient_mode int(11) DEFAULT '0' NOT NULL
);