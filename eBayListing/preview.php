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

/*
switch($_GET['t']){
    case "s":
        echo $_GET['d'];
    break;
    
    case "t":
        $sql = "select Description from template where Id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        echo $row['Description'];
    break;

    case "i":
        $sql = "select Description from items where Id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        echo $row['Description'];
    break;
    
}
*/

//echo str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']);

/*
if(!empty($_SESSION[$_GET['type']][$_GET['id']]['description']) || $_GET['type'] == "sku"){
    echo str_replace("\\", "", $_SESSION[$_GET['type']][$_GET['id']]['description']);
}else{
    $sql = "select Description from ".$_GET['type']." where Id = ".$_GET['id'];
    $result = mysql_query($sql, $link);
    $row = mysql_fetch_assoc($result);
    echo $row['Description'];
}
*/
if($_GET['h'] == 's'){
    if(strpos($_GET['id'], ',') == false){
        $sql = "select Title,Description,SKU,UseStandardFooter,PrimaryCategoryCategoryName,StoreCategoryName,StandardStyleTemplateId from template where Id = '".$_GET['id']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        
        $sql_0 = "select * from account_sku_picture where account_id = '".$_COOKIE['account_id']."' and sku = '".$row['SKU']."'";
        $result_0 = mysql_query($sql_0, $link);
        $row_0 = mysql_fetch_assoc($result_0);
        echo '<div style="text-align:center; border: solid;">'.$row['PrimaryCategoryCategoryName'].'<br>'.
                    $row['StoreCategoryName'].'
            </div>';
        if($row['UseStandardFooter']){
            $sql_1 = "select content from standard_style_template where id = '".$row['StandardStyleTemplateId']."' and accountId = '".$_COOKIE['account_id']."'";
            $result_1 = mysql_query($sql_1, $link);
            $row_1 = mysql_fetch_assoc($result_1);
            echo str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
                             array($row['Title'], $row['SKU'], (!empty($row_0['picture_1']))?'<img src="'.$row_0['picture_1'].'" />':'', (!empty($row_0['picture_2']))?'<img src="'.$row_0['picture_2'].'" />':'', (!empty($row_0['picture_3']))?'<img src="'.$row_0['picture_3'].'" />':'', (!empty($row_0['picture_4']))?'<img src="'.$row_0['picture_4'].'" />':'', (!empty($row_0['picture_5']))?'<img src="'.$row_0['picture_5'].'" />':'', html_entity_decode($row['Description'])), html_entity_decode($row_1['content'], ENT_QUOTES));	
        }else{
            echo html_entity_decode($row['Description']);
        }
    }else{
        $t = explode(",", $_GET['id']);
        $sql = "select Title,Description,SKU,UseStandardFooter,PrimaryCategoryCategoryName,StoreCategoryName,StandardStyleTemplateId from template where Id = '".$t[0]."'";
	$result = mysql_query($sql, $link);
	$row = mysql_fetch_assoc($result);
        
        $sql_0 = "select * from account_sku_picture where account_id = '".$_COOKIE['account_id']."' and sku = '".$row['SKU']."'";
        $result_0 = mysql_query($sql_0, $link);
        $row_0 = mysql_fetch_assoc($result_0);
      
	if($row['UseStandardFooter']){
	    $sql_1 = "select content from standard_style_template where id = '".$row['StandardStyleTemplateId']."' and accountId = '".$_COOKIE['account_id']."'";
            $result_1 = mysql_query($sql_1, $link);
            $row_1 = mysql_fetch_assoc($result_1);
	    $x = str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
                             array($row['Title'], $row['SKU'], (!empty($row_0['picture_1']))?'<img src="'.$row_0['picture_1'].'" />':'', (!empty($row_0['picture_2']))?'<img src="'.$row_0['picture_2'].'" />':'', (!empty($row_0['picture_3']))?'<img src="'.$row_0['picture_3'].'" />':'', (!empty($row_0['picture_4']))?'<img src="'.$row_0['picture_4'].'" />':'', (!empty($row_0['picture_5']))?'<img src="'.$row_0['picture_5'].'" />':'', html_entity_decode($row['Description'])), html_entity_decode($row_1['content'], ENT_QUOTES));	
	}else{
	    $x = html_entity_decode($row['Description']);
	}
        
        $x = '<div style="text-align:center; border: solid;">'.$row['PrimaryCategoryCategoryName'].'<br>'.
                    $row['StoreCategoryName'].'
            </div>'.$x;
        ?>
            <script type="text/javascript" src="./ext-core.js"></script>
            <script type="text/javascript">
                var id = "<?=$_GET['id']?>";
                var array = id.split(",");
                var length = array.length;
                var index = 0;
                //console.log(array);
                function back(){
                    index--;
                    if(index < 0){
                        index++;
                        alert("this is first one.");
                        return false;   
                    }
                    Ext.Ajax.request({
                        url: 'service.php?action=getDescriptionById',
                        success: function(a, b, c){
                            document.getElementById("index").innerHTML = "Template " + array[index] + "(" + (index + 1) + "/"+ length + ")";
                            document.getElementById("preview").innerHTML = a.responseText;
                        },
                        params: { id: array[index]}
                    });
                }
                
                function next(){
                    index++;
                    if(index >= length){
                        index--;
                        alert("this is last one.");
                        return false;   
                    }
                    Ext.Ajax.request({
                        url: 'service.php?action=getDescriptionById',
                        success: function(a, b, c){
                            document.getElementById("index").innerHTML = "Template " + array[index] + "(" + (index + 1) + "/"+ length + ")";
                            document.getElementById("preview").innerHTML = a.responseText;
                        },
                        params: { id: array[index]}
                    });
                }
                
                Ext.Ajax.on('beforerequest',  function(){
                                Ext.get("process").show();
                            });
                    
                Ext.Ajax.on('requestcomplete', function(){
                                Ext.get("process").hide();
                            });
            </script>
            
            <div id="nav" style="text-align:center;">
                <img src="images/Back.png" onclick="back();" style="cursor: pointer;"/>
                <img width=30 height=1/>
                <span id="index" style="top: 0px;">Template <?=$t[0]?></span>
                <img width=30 height=1/>
                <img src="images/Next.png" onclick="next();" style="cursor: pointer; "/>
            </div>
            <div id="process" style="display: none;text-align:center;"><img src="images/ajax-loader.gif"/></div>
            <div id="preview"><?=$x?></div>
        <?php
    }
}elseif($_GET['h'] == 'x'){
    session_start();
    $sql_0 = "select content from account_style_template where account_id = '".$_GET['account_id']."'";
    $result_0 = mysql_query($sql_0, $link);
    $row_0 = mysql_fetch_assoc($result_0);
    
    $sql_1 = "select Title,SKU,Description from share_template where Id = ".$_GET['share_template_id'];
    $result_1 = mysql_query($sql_1, $link);
    $row_1 = mysql_fetch_assoc($result_1);
    
    $sql_01 = "select * from account_sku_picture where account_id = '".$_GET['account_id']."' and sku = '".$row_1['SKU']."'";
    $result_01 = mysql_query($sql_01, $link);
    $row_01 = mysql_fetch_assoc($result_01);
    
    if(!empty($_SESSION[$_GET['type']][$_GET['id']]['title'])){
	$row_1['Title'] = $_SESSION[$_GET['type']][$_GET['id']]['title'];
	$row_1['Description'] = $_SESSION[$_GET['type']][$_GET['id']]['description'];
    }
    
    echo str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
		     array($row_1['Title'], $row_1['SKU'], 
		       '<img src="'.$row_01['picture_1'].'" />', '<img src="'.$row_01['picture_2'].'" />', '<img src="'.$row_01['picture_3'].'" />', '<img src="'.$row_01['picture_4'].'" />', '<img src="'.$row_01['picture_5'].'" />',
		       html_entity_decode(str_replace("\\", "",$row_1['Description']))), html_entity_decode($row_0['content'], ENT_QUOTES));
}else{
    session_start();
    //print_r($_SESSION);
    if($_GET['u'] == "true"){
        $sql_0 = "select * from account_sku_picture where account_id = '".$_COOKIE['account_id']."' and sku = '".$_GET['sku']."'";
        $result_0 = mysql_query($sql_0, $link);
        $row_0 = mysql_fetch_assoc($result_0);
        
        $sql = "select content from standard_style_template where id = '".$_GET['standardStyleTemplateId']."' and accountId = '".$_COOKIE['account_id']."'";
        $result = mysql_query($sql, $link);
        $row = mysql_fetch_assoc($result);
        //echo $row['footer'];
        echo str_replace(array("%title%", "%sku%", "%picture-1%", "%picture-2%", "%picture-3%", "%picture-4%", "%picture-5%", "%description%"),
                         array($_SESSION[$_GET['type']][$_GET['id']]['title'], $_SESSION[$_GET['type']][$_GET['id']]['sku'], 
                               '<img src="'.$row_0['picture_1'].'" />', '<img src="'.$row_0['picture_2'].'" />', '<img src="'.$row_0['picture_3'].'" />', '<img src="'.$row_0['picture_4'].'" />', '<img src="'.$row_0['picture_5'].'" />', html_entity_decode(str_replace("\\", "",$_SESSION[$_GET['type']][$_GET['id']]['description']))), html_entity_decode($row['content'], ENT_QUOTES));
        mysql_close($link);
    }else{
        echo html_entity_decode(str_replace("\\", "",$_SESSION[$_GET['type']][$_GET['id']]['description']));
    }
}
?>