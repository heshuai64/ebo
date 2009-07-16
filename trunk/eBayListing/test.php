<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="../../ext-3.0.0/resources/css/ext-all.css" />
    <script src="../../ext-3.0.0/adapter/ext/ext-base.js"></script>
    <script src="../../ext-3.0.0/ext-all.js"></script>
    <title>Login</title>
    <script>
        Ext.onReady(function(){
            Ext.Ajax.request({
                url: 'service.php?action=testComet',
                success: function(a, b, c){
                    console.log("success");
                    console.log([a, b, c]);
                },
                failure: function(a, b, c){
                    console.log("failure");
                    console.log([a, b, c]);
                },
                timeout:20000,
                params: { foo: 'bar' }
            });
        })
    </script>
</head>
<body>
    <div>
        
    </div>
</body>
</html>
