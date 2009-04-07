Install Instructions:

The following steps will register all the Modules in the DB and setup
authority for any user that has the 'administrator' group granted to them.
An additional Menu will be found in the start called QO Admin

1. Extract rar file in modules directory of your qWikiOffice install

system/modules/

2. Execute uninstall_qo-admin.sql in the qWikiOffice DB.

3. Execute install_qo-admin_2.0.0.sql in the qWikiOffice DB.

4. (optional) Execute create_admin_user.sql in the qWikiOffice DB.
   This creates a user called admin password admin in qWikiOffice