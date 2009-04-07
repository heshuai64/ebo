INSERT INTO `qo_members` (
	`first_name`
	, `last_name`
	, `email_address`
	, `password`
	, `active`
) VALUES (
	'admin'
	, 'admin'
	, 'admin'
	, 'admin'
	, 'true'
);

INSERT INTO `qo_members_has_groups` (
	`qo_members_id`
	, `qo_groups_id`
	, `active`
	, `admin_flag`
)
select m.id, g.id, 'true', 'true'
from (select `id` from `qo_groups` where ucase(`name`) = 'ADMINISTRATOR') g
   , (select `id` from `qo_members` where `email_address` = 'admin') m;