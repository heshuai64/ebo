-- ----------------------------------------------------------------------------
-- Alter table objects
-- ----------------------------------------------------------------------------
-- alter qo_files to add column default_file.  This allows for the exclusion of
-- qWikiOffice defined objects from alteration.
ALTER TABLE `qo_files` ADD `default_file` VARCHAR( 5 ) NOT NULL DEFAULT 'FALSE';
update `qo_files` set `default_file` = 'TRUE';
-- Adding indexes to tables to enforce correct uniqueness
ALTER TABLE `qo_members` ADD UNIQUE `qo_members_uk1` ( `email_address` );
ALTER TABLE `qo_groups` ADD UNIQUE `qo_groups_uk1` ( `name` );
ALTER TABLE `qo_modules` ADD UNIQUE `qo_modules_uk1` ( `moduleName` );
ALTER TABLE `qo_modules` ADD UNIQUE `qo_modules_uk2` ( `moduleId` );
ALTER TABLE `qo_modules_has_files` ADD UNIQUE `qo_modules_has_files_uk1` ( `qo_modules_id` , `name` );
ALTER TABLE `qo_files` ADD UNIQUE `qo_files_uk1` ( `name` , `path` ( 255 ) );
-- ----------------------------------------------------------------------------
-- Create new table
-- ----------------------------------------------------------------------------
CREATE TABLE `qo_admin_audit` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
	, `qo_members_id` INT UNSIGNED NOT NULL
	, `audit_date`  TIMESTAMP NOT NULL DEFAULT current_timestamp
	, `audit_state` VARCHAR(15)
	, `audit_text` TEXT NOT NULL
) ENGINE = MYISAM ;
-- ----------------------------------------------------------------------------
-- Add modules to qo_modules
-- ----------------------------------------------------------------------------
INSERT INTO `qo_modules` (
	`moduleName`
	, `moduleType`
	, `moduleId`
	, `version`
	, `author`
	, `description`
	, `path`
	, `active`
) VALUES (
	'QoDesk.QoAdminConsole'
	, 'system'
	, 'qo-admin-console'
	, '2.0.0'
	, 'Paul Simmons'
	, 'Administation console for qWikiOffice'
	, 'system/modules/qo-admin2/qo-admin-console/'
	, 'true'
), (
	'QoDesk.QoAdminMyProfile'
	, 'system'
	, 'qo-admin-my-profile'
	, '2.0.0'
	, 'Paul Simmons'
	, 'Member profile administration module'
	, 'system/modules/qo-admin2/qo-admin-my-profile/'
	, 'true'
), (
	'QoDesk.QoAdminMyGroups'
	, 'system'
	, 'qo-admin-my-groups'
	, '2.0.0'
	, 'Paul Simmons'
	, 'Group administation module for members that have groups assigned to them with admin privileges'
	, 'system/modules/qo-admin2/qo-admin-my-groups/'
	, 'true'
);

-- ----------------------------------------------------------------------------
-- Add files to qo_modules_has_files
-- ----------------------------------------------------------------------------
-- QoAdminConsole
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-console') a
   , (select 'qo-admin-console.js' as name, 'javascript' as type) b;
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-console') a
   , (select 'qo-admin-console.php' as name, 'php' as type) b;
-- QoAdminMyProfile
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-my-profile') a
   , (select 'qo-admin-my-profile.js' as name, 'javascript' as type) b;
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-my-profile') a
   , (select 'qo-admin-my-profile.php' as name, 'php' as type) b;
-- QoAdminMyGroups
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-my-groups') a
   , (select 'qo-admin-my-groups.js' as name, 'javascript' as type) b;
insert into `qo_modules_has_files` (`qo_modules_id`, `name`, `type`)
select a.id, b.name, b.type 
from (select id from qo_modules where moduleId = 'qo-admin-my-groups') a
   , (select 'qo-admin-my-groups.php' as name, 'php' as type) b;
-- ----------------------------------------------------------------------------
-- Add files to qo_files
-- ----------------------------------------------------------------------------
insert into `qo_files` (`name`, `path`, `type`)
values ('qo-admin.css', 'system/modules/qo-admin2/common/','css');
-- ----------------------------------------------------------------------------
-- Add modules to administrator group
-- ----------------------------------------------------------------------------
insert into `qo_groups_has_modules` (`qo_groups_id`, `qo_modules_id`, `active`)
select g.id, m.id, 'true'
from (select id from qo_groups where ucase(name) = 'ADMINISTRATOR') g
   , (select id from qo_modules where moduleId like 'qo-admin%') m;
-- ----------------------------------------------------------------------------
-- Add My Profile/Groups to other groups
-- ----------------------------------------------------------------------------
insert into `qo_groups_has_modules` (`qo_groups_id`, `qo_modules_id`, `active`)
select g.id, m.id, 'true'
from (select id from qo_groups where ucase(name) <> 'ADMINISTRATOR') g
   , (select id from qo_modules where moduleId like 'qo-admin-my-%') m;
-- ----------------------------------------------------------------------------
-- Add Modules to launchers
-- ----------------------------------------------------------------------------
insert into `qo_modules_has_launchers` (`qo_modules_id`, `qo_launchers_id`, `sort_order`)
select m.id, l.id, 30
from (select id from qo_modules where moduleId = 'qo-admin-console') m
   , (select id from qo_launchers where name = 'startmenutool') l;
insert into `qo_modules_has_launchers` (`qo_modules_id`, `qo_launchers_id`, `sort_order`)
select m.id, l.id, 10
from (select id from qo_modules where moduleId = 'qo-admin-my-profile') m
   , (select id from qo_launchers where name in ('startmenutool', 'contextmenu')) l;
insert into `qo_modules_has_launchers` (`qo_modules_id`, `qo_launchers_id`, `sort_order`)
select m.id, l.id, 20
from (select id from qo_modules where moduleId = 'qo-admin-my-groups') m
   , (select id from qo_launchers where name = 'startmenutool') l;