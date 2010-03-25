<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Standard Style</title>
    <script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		//theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		//content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		//external_link_list_url : "lists/link_list.js",
		//external_image_list_url : "lists/image_list.js",
		//media_external_list_url : "lists/media_list.js",

		// Replace values for the template plugin
                /*
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
                */
	})
    </script>
    
</head>
<body>
    <form action="service.php?action=saveStandardStyleTemplate" method="post">
        <input id="standard_style_template_id" name="standard_style_template_id" type="hidden" value="<?=$_GET['id']?>"/>
        <textarea id="content" name="content" rows="30" cols="80" style="width: 100%">
        <?php

            $link = mysql_connect('localhost', 'root', '5333533');
            if (!$link) {
                die('Could not connect: ' . mysql_error($link));
            }
            
            mysql_query("SET NAMES 'UTF8'", $link);
            
            if (!mysql_select_db("ebaylisting", $link)) {
                echo "Unable to select ebaylisting: " . mysql_error($link);
                exit;
            }
            
            $sql = "select content from standard_style_template where id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
            $result = mysql_query($sql, $link);
            $row = mysql_fetch_assoc($result);
            echo html_entity_decode($row['content'], ENT_QUOTES);
            mysql_close($link);
            
        ?>        
        </textarea>
        <div style="text-align:center;">
            <?php 
                if($_GET['id'] == 0){
                    echo '<font color="red">Standard Style Template Name: </font><input type="text" id="standard_style_template_name" name="standard_style_template_name"/>';
                }
            ?>
            <input type="submit" value="Save"/>
            <input type="button" value="Close Window" onclick="self.close()">
        </div>
    </form>

</body>  
</html>