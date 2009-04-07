-- -------------------------------------------------------
-- WARNING!!!
-- EXECUTING THIS FILE WILL REMOVE ALL DATABASE REFERENCES
-- TO THE QoAdmin MODULES AND ANY PRIVILEGES ASSOCIATED TO
-- THEM
-- -------------------------------------------------------

-- delete data content
delete from `qo_groups_has_modules`
where `qo_modules_id` in (
  select `id`
  from `qo_modules`
  where `moduleName` like 'QoDesk.QoAdmin%'
);

delete from `qo_modules_has_files`
where `qo_modules_id` in (
  select `id`
  from `qo_modules`
  where `moduleName` like 'QoDesk.QoAdmin%'
);

delete from `qo_modules_has_launchers`
where `qo_modules_id` in (
  select `id`
  from `qo_modules`
  where `moduleName` like 'QoDesk.QoAdmin%'
);

delete from `qo_modules`
where `moduleName` like 'QoDesk.QoAdmin%';

delete from `qo_files`
where name = 'qo-admin.css';

commit;

-- remove object alterations
ALTER TABLE `qo_files`  DROP `default_file`;
ALTER TABLE `qo_members` DROP INDEX `qo_members_uk1`;
ALTER TABLE `qo_groups` DROP INDEX `qo_groups_uk1`;
ALTER TABLE `qo_modules` DROP INDEX `qo_modules_uk1`;
ALTER TABLE `qo_modules` DROP INDEX `qo_modules_uk2`;
ALTER TABLE `qo_modules_has_files` DROP INDEX `qo_modules_has_files_uk1`;
ALTER TABLE `qo_files` DROP INDEX `qo_files_uk1`;
DROP TABLE `qo_admin_audit`;
commit;