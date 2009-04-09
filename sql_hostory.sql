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


  INSERT INTO `ebaybo`.`qo_modules_actions` (
        `id` ,
        `qo_modules_id` ,
        `name` ,
        `description`
        )
        VALUES (
        NULL , '9', 'getAllEbaySeller', '获取所有eBay账户信息'
        );
        
        INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
        `id` ,
        `qo_privileges_id` ,
        `qo_modules_actions_id`
        )
        VALUES (
        NULL , '3', '36'
----------------------------------------------------------------------------------------------------

   INSERT INTO `ebaybo`.`qo_modules_actions` (
        `id` ,
        `qo_modules_id` ,
        `name` ,
        `description`
        )
        VALUES (
        NULL , '9', 'updateEbaySeller', '更新eBay账户信息'
        ), (
        NULL , '9', 'deleteEbaySeller', '删除eBay账户'
        );
        
        INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
        `id` ,
        `qo_privileges_id` ,
        `qo_modules_actions_id`
        )
        VALUES (
        NULL , '3', '37'
        ), (
        NULL , '3', '38'
        );
-------------------------------------------------------------------------------------------------------

 CREATE TABLE `ebaybo`.`qo_ebay_proxy` (
`id` INT NOT NULL ,
`ebay_seller_id` VARCHAR( 60 ) NOT NULL ,
`proxy_host` VARCHAR( 200 ) NOT NULL ,
`proxy_port` INT NOT NULL
) ENGINE = MYISAM 

-------------------------------------------------------------------------------------------------------


INSERT INTO `ebaybo`.`qo_modules_actions` (
`id` ,
`qo_modules_id` ,
`name` ,
`description`
)
VALUES (
NULL , '9', 'addEbaySeller', '添加eBay帐号'
), (
NULL , '9', NULL , NULL
), (
NULL , '9', 'getAllEbayProxy', '获取所有eBay代理'
), (
NULL , '9', 'addEbayProxy', '添加eBay代理'
), (
NULL , '9', 'updateEbayProxy', '更新eBay代理'
), (
NULL , '9', 'deleteEbayProxy', '更新eBay代理'
);


INSERT INTO `ebaybo`.`qo_privileges_has_module_actions` (
`id` ,
`qo_privileges_id` ,
`qo_modules_actions_id`
)
VALUES (
NULL , '3', '41'
), (
NULL , '3', '42'
), (
NULL , '3', '43'
), (
NULL , '3', '44'
), (
NULL , '3', '45'
), (
NULL , '3', '46'
);


-------------------------------------------------------------------------------------------------------
