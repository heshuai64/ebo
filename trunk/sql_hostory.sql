INSERT INTO `ebaybo`.`qo_modules_actions` (
`id` ,
`qo_modules_id` ,
`name` ,
`description`
)
VALUES (
NULL , '12', 'verifyShipment', '验证货运信息'
);

INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
`id` ,
`qo_privileges_id` ,
`qo_modules_actions_id`
)
VALUES (
NULL , '6', '36'
);

----------------------------------------------------------------------------------------------------

INSERT INTO `ebaybo`.`qo_modules_actions` (
`id` ,
`qo_modules_id` ,
`name` ,
`description`
)
VALUES (
NULL , '12', 'packShipment', '包裹货物'
);


INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
`id` ,
`qo_privileges_id` ,
`qo_modules_actions_id`
)
VALUES (
NULL , '6', '37'
);

----------------------------------------------------------------------------------------------------