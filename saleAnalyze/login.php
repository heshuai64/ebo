<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="../ext-3.2.1/resources/css/ext-all.css" />
    <script type="text/javascript" src="../ext-3.2.1/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="../ext-3.2.1/ext-all.js"></script>
    <script type="text/javascript" src="zh_cn.js"></script>
    <title>Login</title>
    <script>
            Ext.onReady(function(){
                Ext.QuickTips.init();
                var login_form = new Ext.FormPanel({
                    labelWidth: 75, // label settings here cascade unless overridden
                    frame:true,
                    title: lang.Research_System,
                    bodyStyle:'padding:5px 5px 0',
                    width: 350,
                    defaults: {width: 230},
                    defaultType: 'textfield',
            
                    items: [{
                            id: 'user',
                            fieldLabel: lang.User_Name,
                            name: 'user',
                            allowBlank:false
                        },{
                            id: 'password',
                            fieldLabel: lang.Password,
                            name: 'password',
                            inputType: 'password',
                            allowBlank:false
                        }
                    ],
            
                    buttons: [{
                        text: lang.Submit,
                        handler: function(){
                            Ext.Ajax.request({
                                url: 'research.php?action=login'
                                , params: {
                                    name: Ext.get("user").dom.value,
                                    password: Ext.get("password").dom.value
                                }
                                , success: function(o){
                                    var d = Ext.decode(o.responseText);
                                    //console.log(d);
                                    //window.location = path;
                                    if(d.success){
                                        window.location = "index.php";
                                    }else{
                                        Ext.Msg.alert(lang.Warning, lang.User_Password_Error);
                                    }
                                }
                                , failure: function(){
                                        alert('Lost connection to server.');
                                }
                            });
                        }
                    }]
                });
        
                login_form.render("login-form");
                
                Ext.get("password").addListener('keypress', function(t, e){
						//console.log(t);
						//alert(t.getKey());
						if(t.getKey() == 13){
						    Ext.Ajax.request({
                                                        url: 'service.php?action=login'
                                                        , params: {
                                                            name: Ext.get("user").dom.value,
                                                            password: Ext.get("password").dom.value
                                                        }
                                                        , success: function(o){
                                                            var d = Ext.decode(o.responseText);
                                                            //console.log(d);
                                                            //window.location = path;
                                                            if(d.success){
                                                                window.location = "index.php";
                                                            }else{
                                                                Ext.Msg.alert(lang.Warning, lang.User_Password_Error);
                                                            }
                                                        }
                                                        , failure: function(){
                                                                alert('Lost connection to server.');
                                                        }
                                                    });
						}
					}
		)
            })           
        </script>
</head>
<body>
    <div id="login-form" style="position:absolute;top:50%;left:50%;margin:-65px 0px 0px -160px; "></div>
</body>
</html>