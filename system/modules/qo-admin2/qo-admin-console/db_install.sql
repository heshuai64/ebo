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
-- Add audit table object
CREATE TABLE `qo_admin_audit` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
	, `qo_members_id` INT UNSIGNED NOT NULL
	, `audit_date` TIMESTAMP NOT NULL DEFAULT current_timestamp
	, `audit_state` VARCHAR(15)
	, `audit_text` TEXT NOT NULL
) ENGINE = MYISAM ;