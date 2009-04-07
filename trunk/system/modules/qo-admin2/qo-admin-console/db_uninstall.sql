-- alter qo_files to remove column default_file.
ALTER TABLE `qo_files`  DROP `default_file`;
-- removing indexes to tables to enforce correct uniqueness
ALTER TABLE `qo_members` DROP INDEX `qo_members_uk1`;
ALTER TABLE `qo_groups` DROP INDEX `qo_groups_uk1`;
ALTER TABLE `qo_modules` DROP INDEX `qo_modules_uk1`;
ALTER TABLE `qo_modules` DROP INDEX `qo_modules_uk2`;
ALTER TABLE `qo_modules_has_files` DROP INDEX `qo_modules_has_files_uk1`;
ALTER TABLE `qo_files` DROP INDEX `qo_files_uk1`;
-- removing audit table
DROP TABLE `qo_admin_audit`;