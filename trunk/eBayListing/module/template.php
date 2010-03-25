<?php
class Template{
    private $account_id;
    
    public function __construct($account_id){
        $this->account_id = $account_id;
    }
    /*
        0: normal
        1: lock
        2: under review
        3: out of stock
    */
    private function getCategoryPathById($SiteID, $CategoryID){
    	global $categoryPathArray, $nest;
	
	if(empty($CategoryID)){
	    return "";    
	}
	
    	$sql = "select CategoryName,CategoryParentID,CategoryLevel from categories where  CategorySiteID = ".$SiteID." and CategoryID = ".$CategoryID;
    	//echo $sql."\n";
    	$result = mysql_query($sql, eBayListing::$database_connect);
    	$row = mysql_fetch_assoc($result);
	$nest++;
	
    	if($row['CategoryLevel'] != 1){
		if($nest >= 30){
		    return 0;
		}
    		array_push($categoryPathArray, $row['CategoryName']);
    		return $this->getCategoryPathById($SiteID, $row['CategoryParentID']);
    	}else{
    		array_push($categoryPathArray, $row['CategoryName']);
    		//print_r($categoryPathArray);
    		$categoryPath = "";
    		for($i = count($categoryPathArray); $i > 0; $i--){
    			$categoryPath .= $categoryPathArray[$i-1] . " > ";
    		}
		$categoryPathArray = array();
    		$categoryPath = substr($categoryPath, 0, -3);
    		//print_r($categoryPath);
    		return $categoryPath;
    	}
    }
    
    private function getSkuPicture($sku=''){
	if(!empty($sku)){
	    $_POST['sku'] = $sku;
	}
	$sql = "select picture_1,picture_2,picture_3,picture_4,picture_5 from account_sku_picture 
	where account_id = '".$this->account_id."' and sku = '".$_POST['sku']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_array($result, MYSQL_NUM);
	if(!empty($sku)){
	    return $row['picture_1'];
	}else{
	    echo json_encode($row);
	}
    }
    
    private function getSiteTime($site, $date, $time, $num = 0, $interval = 0){
	switch($site){
	    case "US":
		$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "UK":
		$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "Germany":
		$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "Australia":
		$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	
	    case "France":
		$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$date.' '.$time) + ($num * $interval * 60));
	    break;
	}
	return $time;
    }
    
    private function log($type, $content, $level = 'normal'){
	//print_r($_COOKIE);
	$sql = "insert into log (level,type,content,account_id) values('".$level."','".$type."','".mysql_real_escape_string($content)."','".$this->account_id."')";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
    }
    
    //----------------------------------------------------------------------
    public function getTemplateTree(){
	$array = array();
	$i = 0;
	if(empty($_POST['node'])){
	    $parent_id = 0;
	}else{
	    $parent_id = $_POST['node'];
	}
	$sql = "select * from template_category where account_id = '".$this->account_id."' and parent_id = '".$parent_id."' order by name";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    
	    $sql_1 = "select count(*) as count from template_to_template_cateogry where template_category_id = '".$row['id']."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    
            $array[$i]['id'] = $row['id'];
	    $array[$i]['text'] = $row['name'] ." (".$row_1['count'].")";
	    $sql_2 = "select count(*) as count from template_category where parent_id = '".$row['id']."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	    if($row_2['count'] > 0){
		$array[$i]['leaf'] = false;
	    }else{
		$array[$i]['leaf'] = true;
	    }
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function addTemplateCateogry(){
	$sql = "insert into template_category (name,parent_id,account_id) values ('".$_POST['templateCategoryName']."','".$_POST['templateCateogryParentId']."','".$this->account_id."')";
	$result = mysql_query($sql, eBayListing::$database_connect);
	echo $result;
    }
    
    public function modifyTemplateCateogry(){
	$sql = "update template_category set name = '".$_POST['templateCategoryName']."' where id = '".$_POST['templateCateogryId']."' and account_id = '".$this->account_id."'";
	//echo $sql;
	$result = mysql_query($sql, eBayListing::$database_connect);
	echo $result;
    }
    
    public function deleteTemplateCateogry(){
	$sql = "delete from template_category where id = '".$_POST['templateCateogryId']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	
	$sql_1 = "delete form template_to_template_cateogry where template_category_id = '".$_POST['templateCateogryId']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	echo $result;
    }
    
    public function moveTemplateCateogry(){
	$sql_1 = "update template_category set parent_id = '".$_POST['newParent']."' where id = '".$_POST['id']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	echo $result_1;
    }
    
    public function getAllTemplate(){
	$array = array();
	
	if(empty($_POST) || $_POST['parent_id'] == '0'){
	    $sql = "select count(*) as count from template where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
	    
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
	    $sql = "select Id,Site,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration,shippingTemplateName,status from template where accountId = '".$this->account_id."' order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            $result = mysql_query($sql, eBayListing::$database_connect);
            
	}else{
	    $where = " where 1 = 1 ";
	    if(!empty($_POST['parent_id'])){
		$where .= " and tttc.template_category_id = '".$_POST['parent_id']."'";
	    }
	    $where .= "and t.accountId = '".$this->account_id."' ";
		
	    if(empty($_POST['start']) && empty($_POST['limit'])){
                $_POST['start'] = 0;
                $_POST['limit'] = 20;
            }
	    
            if(!empty($_POST['TID'])){
                $where .= " and Id = '".$_POST['TID']."'";
            }
            
            if(!empty($_POST['SKU'])){
                $where .= " and t.SKU like '%".mysql_real_escape_string($_POST['SKU'])."%'";
            }
            
            if(!empty($_POST['Title'])){
                $where .= " and t.Title like '%".mysql_real_escape_string($_POST['Title'])."%'";
            }
            
            if(!empty($_POST['ListingDuration'])){
                $where .= " and ListingDuration = '".$_POST['ListingDuration']."'";
            }
            
            $sql = "select count(*) as count from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id  ".$where;
            //echo $sql;
	    //exit;
	    $result = mysql_query($sql, eBayListing::$database_connect);
            $row = mysql_fetch_assoc($result);
            $totalCount = $row['count'];
            
            $sql = "select Id,Site,SKU,Title,BuyItNowPrice,ListingType,StartPrice,Quantity,ListingDuration,shippingTemplateName,status from template as t left join template_to_template_cateogry as tttc on t.Id = tttc.template_id ".$where." order by ".$_POST['sort']." ".$_POST['dir']." limit ".$_POST['start'].",".$_POST['limit'];
            //echo $sql;
            $result = mysql_query($sql, eBayListing::$database_connect);
	}
	
	//echo $sql;
	
	while($row = mysql_fetch_assoc($result)){
	    if($row['ListingType'] == "FixedPriceItem" || $row['ListingType'] == "StoresFixedPrice"){
		$row['Price'] = $row['StartPrice'];
	    }else{
		$row['Price'] = $row['BuyItNowPrice'];
	    }
	    /*
	    $sql_1 = "select ShippingServiceCost from template_international_shipping_service_option where templateId = '".$row['Id']."' order by ShippingServicePriority";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $row_1 = mysql_fetch_assoc($result_1);
	    $row['ShippingFee'] = $row_1['ShippingServiceCost'];
	    */
	    
	    $sql_2 = "select tc.name from template_to_template_cateogry as tttc left join template_category as tc on tttc.template_category_id = tc.id where tttc.template_id = '".$row['Id']."' and account_id = '".$this->account_id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $row_2 = mysql_fetch_assoc($result_2);
	    $row['Category'] = $row_2['name'];
	    
	    $array[] = $row;
	}
	
	echo json_encode(array('totalCount'=>$totalCount, 'records'=>$array));
	mysql_free_result($result);
    }
    
    public function getTemplateDurationStore(){
	$sql = "select ListingType from template where Id = '".$_POST['Id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$sql = "select id from listing_duration_type where name = '".$row['ListingType']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$sql = "select name from listing_duration where id = '".$row['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['name'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function deleteTemplate(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    foreach($ids as $id){
		$sql_1 = "delete from template where Id = '".$id."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		$sql_2 = "delete from template_picture_url where templateId = '".$id."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		$sql_3 = "delete from template_shipping_service_options where templateId = '".$id."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$sql_5 = "select * from template_attribute_set where templateId = '".$id."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $sql_6 = "delete from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}
    
		$sql_7 = "delete from template_attribute_set where templateId = '".$id."'";
		$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		
		$sql_8 = "delete from template_to_template_cateogry where template_id = '".$id."'";
		$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	    }
	}else{
	    $id = $_POST['ids'];
	    $sql_1 = "delete from template where Id = '".$id."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    $sql_2 = "delete from template_picture_url where templateId = '".$id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    
	    $sql_3 = "delete from template_shipping_service_options where templateId = '".$id."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_5 = "select * from template_attribute_set where templateId = '".$id."'";
	    $result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	    while($row_5 = mysql_fetch_assoc($result_5)){
		$sql_6 = "delete from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
		$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    }

	    $sql_7 = "delete from template_attribute_set where templateId = '".$id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	    
	    $sql_8 = "delete from template_to_template_cateogry where template_id = '".$id."'";
	    $result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	}
	//print_r(array($result_1, $result_2, $result_3, $result_4, $result_5, $result_7));
	
	if($result_1 && $result_2 && $result_3 && $result_4 && $result_5 && $result_7 && $result_8){
	    echo 1;   
	}else{
	    echo 0;
	}
    }

    public function importTemplateFromCSV(){
	//echo '{success:true, test:"'.print_r($_FILES, true).'"}';
	//exit;
	switch($_GET['type']){
	    case "spcsv":
		$handle = fopen($_FILES['spcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle)) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set StartPrice='".$data[1]."' where SKU = '".$data[0]."' and accountId = '".$this->account_id."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	
	    case "sqcsv":
		$handle = fopen($_FILES['sqcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle)) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set Quantity='".$data[1]."' where SKU = '".$data[0]."' and accountId = '".$this->account_id."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	    
	    case "stpcsv":
		$handle = fopen($_FILES['stpcsv']['tmp_name'], "r");
		while (($data = fgetcsv($handle)) !== FALSE) {
		    //print_r($data);
		    $sql = "update template set StartPrice='".$data[2]."' where SKU = '".$data[0]."' and Title = '".html_entity_decode($data[1], ENT_QUOTES)."' and accountId = '".$this->account_id."'";
		    $result = mysql_query($sql, eBayListing::$database_connect);
		}
		fclose($handle);
	    break;
	    
	    case "stcsv":
	    	$handle = fopen($_FILES['stcsv']['tmp_name'], "r");
                while (($data = fgetcsv($handle)) !== FALSE) {
                    //print_r($data);
                    $sql = "select Id from template where SKU = '".$data[0]."' and Title = '".html_entity_decode($data[1], ENT_QUOTES)."' and accountId = '".$this->account_id."'";
                    //echo $sql;
                    $result = mysql_query($sql, eBayListing::$database_connect);
                    while($row = mysql_fetch_assoc($result)){
                        $this->changeTemplateToItem($row['Id']);
                    }
                }
                fclose($handle);
	    break;
        
            case "tcsv":
                $handle = fopen($_FILES['tcsv']['tmp_name'], "r");
                while (($data = fgetcsv($handle)) !== FALSE) {
                    while($row = mysql_fetch_assoc($result)){
                        $this->changeTemplateToItem($data[0]);
                    }
                }
                fclose($handle);
            break;
	}
	if($result){
	    echo "{success:true}";
	}else{
	    echo "{success:false}";
	}
    }
    
    //-----------------------  Template change to item ------------------------------------
    private function changeTemplateToItem($template_id, $time = '', $local_time = '', $status = 0){
	$sql_0 = "select * from template where Id = '".$template_id."' and accountId = '".$this->account_id."'";
	$result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	$row_0 = mysql_fetch_assoc($result_0);
	
	if(empty($row_0['shippingTemplateName'])){
	    echo "[{success: false, msg: 'template ".$template_id." no set shipping template.'}]";
	    exit;
	}
	
        if($row_0['status'] == 1){
            echo "[{success: false, msg: 'template ".$template_id." is locked.'}]";
	    exit;
        }
        
	$sql_8 = "select * from shipping_template where name = '".$row_0['shippingTemplateName']."' and account_id = '".$this->account_id."'";
	$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	$row_8 = mysql_fetch_assoc($result_8);
	//debugLog("tempalteChangeToItem.txt", $sql_8);
	
	$sql_9 = "select PayPalEmailAddress from account where id = '".$this->account_id."'";
	$result_9 = mysql_query($sql_9, eBayListing::$database_connect);
	$row_9 = mysql_fetch_assoc($result_9);
	
	$sql_1 = "insert into items (TemplateID,AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,DispatchTimeMax,ListingDuration,ListingType,Location,PaymentMethods,PayPalEmailAddress,
	PostalCode,PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
	ReturnPolicyDescription,ReturnPolicyRefundOption,ReturnPolicyReturnsAcceptedOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	ReservePrice,CurrentPrice,ScheduleTime,ScheduleLocalTime,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,
	InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,Status,StandardStyleTemplateId,UseStandardFooter) select '".$template_id."',AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
	Description,'".$row_8['DispatchTimeMax']."',ListingDuration,ListingType,'".$row_8['Location']."','PayPal','".$row_9['PayPalEmailAddress']."',
	'".$row_8['PostalCode']."',PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
	'".$row_8['ReturnPolicyDescription']."','".$row_8['ReturnPolicyRefundOption']."','".$row_8['ReturnPolicyReturnsAcceptedOption']."','".$row_8['ReturnPolicyReturnsWithinOption']."','".$row_8['ReturnPolicyShippingCostPaidByOption']."',
	ReservePrice,CurrentPrice,'".$time."','".$local_time."',SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
	StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
	Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
	GalleryURL,PhotoDisplay,'".$row_8['ShippingServiceOptionsType']."','".$row_8['InsuranceOption']."','".$row_8['InsuranceFee']."',
	'".$row_8['InternationalShippingServiceOptionType']."','".$row_8['InternationalInsurance']."','".$row_8['InternationalInsuranceFee']."','".$status."',StandardStyleTemplateId,UseStandardFooter from template where Id = '".$template_id."'";
	
	//echo $sql_1."\n";
	//debugLog("tempalteChangeToItem.txt", $sql_1);
	
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$item_id = mysql_insert_id(eBayListing::$database_connect);
	
	//var_dump($item_id);
	//exit;
	$sql_2 = "insert into picture_url (ItemID,url)  select '".$item_id."',url from template_picture_url where templateId = '".$template_id."'";
	$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	
        //ShippingServiceCost1,ShippingServiceAdditionalCost1
        $sql_31 = "select * from s_template where template_id = '".$row_8['id']."'";
        $result_31 = mysql_query($sql_31, eBayListing::$database_connect);
        while($row_31 = mysql_fetch_assoc($result_31)){
            if(!empty($row_0['ShippingServiceCost'.$row_31['ShippingServicePriority']]) && $row_0['ShippingServiceCost'.$row_31['ShippingServicePriority']] > 0){
                $sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$item_id."',FreeShipping,ShippingService,'".$row_0['ShippingServiceCost'.$row_31['ShippingServicePriority']]."','".$row_0['ShippingServiceAdditionalCost'.$row_31['ShippingServicePriority']]."',ShippingServicePriority from s_template where id = '".$row_31['id']."'";
                $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
            }else{
                $sql_3 = "insert into shipping_service_options (ItemID,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$item_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from s_template where id = '".$row_31['id']."'";
                $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
            }
        }
	
        //InternationalShippingServiceCost1,InternationalShippingServiceAdditionalCost1
        $sql_41 = "select * from i_s_template where template_id = '".$row_8['id']."'";
        $result_41 = mysql_query($sql_41, eBayListing::$database_connect);
        while($row_41 = mysql_fetch_assoc($result_41)){
            if(!empty($row_0['InternationalShippingServiceCost'.$row_41['ShippingServicePriority']]) && $row_0['InternationalShippingServiceCost'.$row_41['ShippingServicePriority']] > 0){
                $sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$item_id."',ShippingService,'".$row_0['InternationalShippingServiceCost'.$row_41['ShippingServicePriority']]."','".$row_0['InternationalShippingServiceAdditionalCost'.$row_41['ShippingServicePriority']]."',ShippingServicePriority,ShipToLocation from i_s_template where id = '".$row_41['id']."'";
                $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
            }else{
                $sql_4 = "insert into international_shipping_service_option (ItemID,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$item_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from i_s_template where id = '".$row_41['id']."'";
                $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
            }
        }

	$sql_5 = "select * from template_attribute_set where templateId = '".$template_id."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	while($row_5 = mysql_fetch_assoc($result_5)){
	    $template_attribute_set_id = $row_5['attribute_set_id'];
	    $sql_6 = "insert into attribute_set (item_id,attributeSetID) values ('".$item_id."','".$row_5['attributeSetID']."')";
	    //echo $sql_6."\n";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
	    
	    $sql_7 = "insert into attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
	    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
	    where attribute_set_id = '".$template_attribute_set_id."'";
	    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	}
	//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));

	if($result_1){
	    return $item_id;
	}else{
	    return false;
	}
    }
    
    public function scheduleUploadTemplate(){
	if(strpos($_POST['ids'], ',')){
	    $ids = explode(',', $_POST['ids']);
	    $item_id = "";
	    foreach($ids as $id){
		$sql_10 = "select * from template where Id = '$id'";
		$result_10 = mysql_query($sql_10, eBayListing::$database_connect);
		$row_10 = mysql_fetch_assoc($result_10);
		//$num_rows = mysql_num_rows($result_10);
		if(empty($row_10['scheduleTemplateName'])){
		    echo "[{success: false, msg: 'template ".$_POST['ids']." no set schedule template.'}]";
		    return 1;
		}
		
		$sql_11 = "select * from schedule_template where name = '".$row_10['scheduleTemplateName']."' and account_id = '".$this->account_id."'";
		$result_11 = mysql_query($sql_11, eBayListing::$database_connect);
		while($row_11 = mysql_fetch_assoc($result_11)){
		    $startTimestamp = strtotime($row_10['ScheduleStartDate']);
		    $endTimestamp = strtotime($row_10['ScheduleEndDate']);	
		    while($startTimestamp <= $endTimestamp){
			if(date("D", $startTimestamp) == $row_11['day']){
			    $local_time = $this->getSiteTime($row_10['Site'], date("Y-m-d", $startTimestamp), $row_11['time']);
			    $item_id .= $this->changeTemplateToItem($id, $local_time, date("Y-m-d", $startTimestamp) . " " .$row_11['time']) . ", ";
			}
			$startTimestamp += 24 * 60 * 60;
		    }
		}
	    }
	}else{
	    $id = $_POST['ids'];
	    $sql_10 = "select * from template where Id = '$id'";
	    $result_10 = mysql_query($sql_10, eBayListing::$database_connect);
	    $row_10 = mysql_fetch_assoc($result_10);
	    //$num_rows = mysql_num_rows($result_10);
	    if(empty($row_10['scheduleTemplateName'])){
		echo "[{success: false, msg: 'template ".$_POST['ids']." no set schedule template.'}]";
		return 1;
	    }
	    
	    $sql_11 = "select * from schedule_template where name = '".$row_10['scheduleTemplateName']."' and account_id = '".$this->account_id."'";
	    $result_11 = mysql_query($sql_11, eBayListing::$database_connect);
	    while($row_11 = mysql_fetch_assoc($result_11)){
		$startTimestamp = strtotime($row_10['ScheduleStartDate']);
		$endTimestamp = strtotime($row_10['ScheduleEndDate']);
		while($startTimestamp <= $endTimestamp){
		    if(date("D", $startTimestamp) == $row_11['day']){
			$local_time = $this->getSiteTime($row_10['Site'], date("Y-m-d", $startTimestamp), $row_11['time']);
			$item_id .= $this->changeTemplateToItem($id, $local_time, date("Y-m-d", $startTimestamp) . " " .$row_11['time']) . ", ";
		    }
		    $startTimestamp += 24 * 60 * 60;
		}
	    }
	    /*
	    $sql_10 = "select * from schedule where template_id = '".$_POST['ids']."'";
	    $result_10 = mysql_query($sql_10, eBayListing::$database_connect);
	    $num_rows = mysql_num_rows($result_10);
	    if($num_rows == 0){
		echo "[{success: false, msg: 'template ".$_POST['ids']." no set schedule date.'}]";;
		return 1;
	    }
	    $item_id = "";
	    while($row_10 = mysql_fetch_assoc($result_10)){
		$startTimestamp = strtotime($row_10['startDate']);
		$endTimestamp = strtotime($row_10['endDate']);
		while($startTimestamp <= $endTimestamp){
		    if(date("D", $startTimestamp) == $row_10['day']){
			if(date("D", $startTimestamp + 24 * 60 * 60) == $row_10['china_day']){
			    $localTimestamp = $startTimestamp + 24 * 60 * 60;
			}elseif(date("D", $startTimestamp - 24 * 60 * 60) == $row_10['china_day']){
			    $localTimestamp = $tempTimestamp - 24 * 60 * 60;
			}elseif(date("D", $startTimestamp) == $row_10['china_day']){
			    $localTimestamp = $startTimestamp;
			}
			
			//echo $startTimestamp."\n";
			//echo $localTimestamp."\n";
			
			//echo $localTimestamp - $startTimestamp."\n";
			//echo date("Y-m-d", $startTimestamp)."\n";
			//echo date("Y-m-d", $localTimestamp)."\n";
			//print_r(array($_POST['ids'], date("Y-m-d", $startTimestamp) . ' ' .$row_10['time'], date("Y-m-d", $localTimestamp) . ' ' .$row_10['china_time']));
			//echo date("Y-m-d", $startTimestamp) . ' ' .$row_10['time']."\n";
			$item_id .= $this->changeTemplateToItem($_POST['ids'], date("Y-m-d", $startTimestamp) . ' ' .$row_10['time'], date("Y-m-d", $localTimestamp) . ' ' .$row_10['china_time']) . ", ";
			//var_dump($result);
		    }
		    $startTimestamp += 24 * 60 * 60;
		}
	    }
	    */
	}
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo "[{success: true, msg: 'Schedule upload success, item id is ".$item_id.".'}]";;
	}
    }
    
    public function addTemplateToUpload(){
	$temp = "";
	$item_id = "";
	$ids = explode(',', $_POST['ids']);
	
	if(count($ids) > 1){
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		$item_id  .= $this->changeTemplateToItem($id, "", "") . ", ";
	    }
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $item_id  = $this->changeTemplateToItem($_POST['ids'], "", "") . ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Add To Upload Failure, Please Notice Admin."}]';
	}
    }
    
    public function immediatelyUploadTemplate(){
	$now = date("Y-m-d H:i:s");
	$temp = "";
	$item_id = "";
	$ids = explode(',', $_POST['ids']);
	
	if(count($ids) > 1){
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		    
		switch($row['Site']){
		    case "US":
			$localTime = date("Y-m-d H:i:s", strtotime("-12 hour ".$now));
		    break;
		
		    case "UK":
			$localTime = date("Y-m-d H:i:s", strtotime("-7 hour ".$now));
		    break;
		
		    case "Australia":
			$localTime = date("Y-m-d H:i:s", strtotime("+2 hour ".$now));
		    break;
		
		    case "France":
			$localTime = date("Y-m-d H:i:s", strtotime("-6 hour ".$now));
		    break;
		}
	    
		//$temp .= $id. " : ". $now . "<br>";	
		$item_id  .= $this->changeTemplateToItem($id, $now, $localTime, 1) . ", ";
	    }
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    //var_dump($row);
	    switch($row['Site']){
		case "US":
		    $localTime = date("Y-m-d H:i:s", strtotime("-12 hour ".$now));
		break;
	    
		case "UK":
		    $localTime = date("Y-m-d H:i:s", strtotime("-7 hour ".$now));
		break;
	    
		case "Australia":
		    $localTime = date("Y-m-d H:i:s", strtotime("+2 hour ".$now));
		break;
	    
		case "France":
		    $localTime = date("Y-m-d H:i:s", strtotime("-6 hour ".$now));
		break;
	    }
		
	    //$temp .= $_POST['ids']. " : ". $now . "<br>";
	    $item_id  = $this->changeTemplateToItem($_POST['ids'], $now, $localTime, 1). ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Immediately upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Immediately Upload Failure, Please Notice Admin."}]';
	}
    }
    
    public function intervalUploadTemplate(){
	/*
	ALTER TABLE `schedule` DROP PRIMARY KEY;
	ALTER TABLE `schedule` ADD INDEX ( `item_id` );
	ALTER TABLE `items` ADD `ScheduleLocalTime` DATETIME NOT NULL AFTER `ScheduleTime` ; 
	*/
	//echo date("Y-m-d H:i:s", strtotime("12:00:00") + 60);
	$now = date("Y-m-d H:i:s");
	$temp = "";
	$item_id = "";
	$_POST['date'] = substr($_POST['date'], 0, -18);
	//echo $_POST['date'];
	//exit;
	$ids = explode(',', $_POST['ids']);
	if(count($ids) > 1){
	    $i = 0;
	    foreach($ids as $id){
		$sql = "select Site from template where Id = '".$id."'";
		$result = mysql_query($sql, eBayListing::$database_connect);
		$row = mysql_fetch_assoc($result);
		
		$localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		$time = $this->getSiteTime($row['Site'], $_POST['date'], $_POST['time'], $i, $_POST['minute']);
		/*
		switch($row['Site']){
		    case "US":
			$time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "UK":
			$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Germany":
			$time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "Australia":
			$time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		    case "France":
			$time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']) + ($i * $_POST['minute'] * 60));
		    break;
		
		}
		*/
		
		if($time < $now){
		    echo '[{success: false, msg: "Time error: '.$time.'"}]';
		    return 0;
		}
		//$temp .= $id. " : ". $time . "<br>";
		$item_id  .= $this->changeTemplateToItem($id, $time, $localTime) . ", ";
		$i++;
	    }
	    //$temp = substr($temp, 0, -2);
	}else{
	    $sql = "select Site from template where Id = '".$_POST['ids']."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    
	    $localTime = date("Y-m-d H:i:s", strtotime($_POST['date'].' '.$_POST['time']));
	    
	    $time = $this->getSiteTime($row['Site'], $_POST['date'], $_POST['time']);
	    /*
	    switch($row['Site']){
		case "US":
		    $time = date("Y-m-d H:i:s", strtotime("+12 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "UK":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Germany":
		    $time = date("Y-m-d H:i:s", strtotime("+7 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "Australia":
		    $time = date("Y-m-d H:i:s", strtotime("-3 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
		case "France":
		    $time = date("Y-m-d H:i:s", strtotime("+6 hour ".$_POST['date'].' '.$_POST['time']));
		break;
	    
	    }
	    */
	    
	    if($time < $now){
		echo '[{success: false, msg: "Time error: '.$time.'"}]';
		return 0;
	    }
	    //$temp .= $_POST['ids'] . " : " . $time;
	    $item_id  = $this->changeTemplateToItem($_POST['ids'], $time, $localTime) . ", ";
	}
	
	$item_id = substr($item_id, 0, -2);
	if(!empty($item_id)){
	    echo '[{success: true, msg: "Interval upload item id is '.$item_id.'"}]';
	}else{
	    echo '[{success: false, msg: "Template Interval Upload Failure, Please Notice Admin."}]';
	}
    }
    
    private function getTemplateCategoryDeep($id){
	global $templateCategoryDeep;
	
	$sql = "select parent_id from template_category where id = '".$id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	if($row['parent_id'] == 0){
	    //$templateCategoryDeep .= "-";
	    return $templateCategoryDeep;
	}elseif(!empty($row['parent_id'])){
	    $templateCategoryDeep .= "+";
	    return $this->getTemplateCategoryDeep($row['parent_id']);
	}else{
	    return "";
	}
    }
    
    public function getTemplateCategory(){
	global $templateCategoryDeep;
        $sql = "select id,name from template_category where account_id = '".$this->account_id."' order by name";
        $result = mysql_query($sql, eBayListing::$database_connect);
        $array = array();
        $i = 0;
	while($row = mysql_fetch_assoc($result)){
            $array[$i]['id'] = $row['id'];
	    $templateCategoryDeep = "";
	    $t = $this->getTemplateCategoryDeep($row['id']);
	    $array[$i]['name'] = $t . $row['name'];
	    $i++;
        }
        
        echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function addToTemplate(){
	/*
	CREATE TABLE IF NOT EXISTS `ship_to_location` (
	  `SiteID` int(11) NOT NULL,
	  `ShippingLocation` varchar(25) NOT NULL,
	  `Description` varchar(50) NOT NULL,
	  KEY `SiteID` (`SiteID`)
	);
	
	ALTER TABLE `template` ADD `PrimaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `PrimaryCategoryCategoryID` ;
	ALTER TABLE `template` ADD `SecondaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `SecondaryCategoryCategoryID` ;
	ALTER TABLE `template` ADD `StoreCategoryName` VARCHAR( 200 ) NOT NULL AFTER `StoreCategoryID` ;
	ALTER TABLE `template` ADD `StoreCategory2Name` VARCHAR( 200 ) NOT NULL AFTER `StoreCategory2ID` ;
	
	ALTER TABLE `items` ADD `PrimaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `PrimaryCategoryCategoryID` ;
	ALTER TABLE `items` ADD `SecondaryCategoryCategoryName` VARCHAR( 200 ) NOT NULL AFTER `SecondaryCategoryCategoryID` ;
	ALTER TABLE `items` ADD `StoreCategoryName` VARCHAR( 200 ) NOT NULL AFTER `StoreCategoryID` ;
	ALTER TABLE `items` ADD `StoreCategory2Name` VARCHAR( 200 ) NOT NULL AFTER `StoreCategory2ID` ;
	
	ALTER TABLE `template` ADD `ShippingServiceOptionsType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `PhotoDisplay` ,
	ADD `InternationalShippingServiceOptionType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `ShippingServiceOptionsType` ;
	
	ALTER TABLE `items` ADD `ShippingServiceOptionsType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `PhotoDisplay` ,
	ADD `InternationalShippingServiceOptionType` ENUM( "Flat", "Calculated" ) NOT NULL AFTER `ShippingServiceOptionsType` ;
	1> 分类属性
	2> 生成导入sp的文件
	3> 模板
	4> 查询后显示的图片可自己输入  *
	5> 隐藏拍卖用户ID
	5> 运费政策     *
	6> 输入保险费用
	*/
	//ScheduleStartDate,ScheduleEndDate
	//ShippingType
	//ShipToLocations
	//print_r($_POST);
	//exit;
	session_start();
	
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$sql = "insert into template (BuyItNowPrice,Country,Currency,Description,
	ListingDuration,ListingType,PaymentMethods,PayPalEmailAddress,
	PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,Quantity,ReservePrice,
	Site,SKU,StartPrice,StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,
	BoldTitle,Border,Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypePlus,GalleryURL,accountId,UseStandardFooter,
	scheduleTemplateName,ScheduleStartDate,ScheduleEndDate,shippingTemplateName,StandardStyleTemplateId) values (
	'".$_POST['BuyItNowPrice']."','CN','".$_POST['Currency']."',
	'".htmlentities($_POST['Description'], ENT_QUOTES)."',
	'".$_POST['ListingDuration']."','".$_POST['ListingType']."','PayPal',
	'".$_POST['PayPalEmailAddress']."',
	'".$_POST['PrimaryCategoryCategoryID']."','".$_POST['PrimaryCategoryCategoryName']."','".$_POST['SecondaryCategoryCategoryID']."','".$_POST['SecondaryCategoryCategoryName']."',
	'".@$_POST['Quantity']."','".@$_POST['ReservePrice']."',
	'".$_POST['Site']."','".$_POST['SKU']."','".$_POST['StartPrice']."','".$_POST['StoreCategory2ID']."','".$_POST['StoreCategory2Name']."',
	'".$_POST['StoreCategoryID']."','".$_POST['StoreCategoryName']."','".$_POST['SubTitle']."',
	'".htmlentities($_POST['Title'], ENT_QUOTES)."','".(empty($_POST['BoldTitle'])?0:1)."',
	'".(empty($_POST['Border'])?0:1)."','".(empty($_POST['Featured'])?0:1)."','".(empty($_POST['Highlight'])?0:1)."',
	'".(empty($_POST['HomePageFeatured'])?0:1)."','".(empty($_POST['GalleryTypeFeatured'])?0:1)."','".(empty($_POST['GalleryTypePlus'])?0:1)."','".$_POST['GalleryURL']."',
	'".$this->account_id."','".(empty($_POST['UseStandardFooter'])?0:1)."',
	'".$_POST['scheduleTemplateName']."',".(empty($_POST['ScheduleStartDate'])?'NULL':"'".$_POST['ScheduleStartDate']."'").",".(empty($_POST['ScheduleEndDate'])?'NULL':"'".$_POST['ScheduleEndDate']."'").",'".$_POST['shippingTemplateName']."',".(empty($_POST['StandardStyleTemplateId'])?'NULL':"'".$_POST['StandardStyleTemplateId']."'").")";
	    
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $sql;
	//exit;
	//debugLog("add-template-tack.log", $sql);
	
	$id = mysql_insert_id(eBayListing::$database_connect);
	
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into template_picture_url (templateId,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	/*
	$i = 1;
	while(!empty($_POST['ShippingService-'.$i])){
	    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['FreeShipping-'.$i]."','".$_POST['ShippingService-'.$i]."','".$_POST['ShippingServiceCost-'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$i = 1;
	while(!empty($_POST['InternationalShippingService-'.$i])){
	    $ShipToLocation = '';
	    if($_POST['InternationalShippingToLocations-'.$i] == 'Custom Locations'){
		if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
		    $ShipToLocation .= ',Americas';
		}
		
		if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
		    $ShipToLocation .= ',US';
		}
		
		if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
		    $ShipToLocation .= ',Europe';
		}
		
		if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
		    $ShipToLocation .= ',Asia';
		}
		
		if(!empty($_POST['Canada_'.$i]) && $_POST['Canada_'.$i] == 1){
		    $ShipToLocation .= ',CA';
		}
		
		if(!empty($_POST['UK_'.$i]) && $_POST['UK_'.$i] == 1){
		    $ShipToLocation .= ',GB';
		}
		
		if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
		    $ShipToLocation .= ',AU';
		}
		
		if(!empty($_POST['Mexico_'.$i]) && $_POST['Mexico_'.$i] == 1){
		    $ShipToLocation .= ',MX';
		}
		
		if(!empty($_POST['Germany_'.$i]) && $_POST['Germany_'.$i] == 1){
		    $ShipToLocation .= ',DE';
		}
		
		if(!empty($_POST['Japan_'.$i]) && $_POST['Japan_'.$i] == 1){
		    $ShipToLocation .= ',JP';
		}
		
		$ShipToLocation = substr($ShipToLocation, 1);
	    }else{
		$ShipToLocation = 'Worldwide';
	    }
	    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService-'.$i]."','".$_POST['InternationalShippingServiceCost-'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	
	
	Array
	(
	    [LB00009-Mon-am-1] => Array
		(
		    [0] => 1:00 AM
		    [1] => 1:01 AM
		    [2] => 1:02 AM
		)
	)
	
	
	if(!empty($_SESSION['Schedule'])){
	    switch($_POST['Site']){
		case "US":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+12 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+12 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				//echo $sql_3;
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "UK":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+7 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+7 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "Germany":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+7 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+7 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "Australia":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("-3 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("-3 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    
		case "France":
		    foreach($_SESSION['Schedule'] as $key=>$value){
			$keyArray = explode("-", $key);
			//if(count($keyArray) == 4 && $keyArray[0] == $_POST['SKU']){
			    foreach($value as $name){
				//$sku = $keyArray[0];
				$day = date("D", strtotime("+6 hour ".$keyArray[1]." ".$name));
				$time = date("H:i:s", strtotime("+6 hour ".$name));
				$china_time = date("H:i:s", strtotime($name));
				$sql_3 = "insert into schedule (template_id,startDate,endDate,day,time,china_day,china_time,account_id) values 
				('".$id."','".$_POST['ScheduleStartDate']."','".$_POST['ScheduleEndDate']."','".$day."','".$time."','".$keyArray[1]."','".$china_time."','".$this->account_id."')";
				$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			    }
			//}
		    }
		break;
	    }
	}
	*/
	
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$_POST['SKU']])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    
	    foreach($_SESSION['AttributeSet'][$_POST['SKU']] as $attributeSetID=>$Attribute){
		$sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		$temp_array = array();
		foreach($Attribute as $attributeID=>$ValueID){
		    if(!empty($ValueID)){
			if(strpos($ValueID, "on") != false){
			    $tempAttributeID = $attributeID;
			    $attributeID = substr($ValueID, 0, -3);
			    $ValueID = substr($tempAttributeID, 0, -9);
			    //echo $attributeID.":".$ValueID;
			    //echo "\n";
			    $temp_array[$attributeID][] = $ValueID;
			}else{
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
				('".$attributeID."', '".$attribute_set_id."', '".$ValueID."')";
				$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
				
				//echo $sql_4;
				//echo "\n";
			}
		    }
		}
		
		//print_r($temp_array);
		if(count($temp_array) > 0){
		    foreach($temp_array as $key=>$value){
			$ValueID = "";
			foreach($value as $name){
			    $ValueID .= $name.',';
			}
			$ValueID = substr($ValueID, 0, -1);
			$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
			('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    }
		}
	    }
	}
	
	$sql_5 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$id."','".$_POST['template_category_id']."')";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	
	if($result){
	    unset($_SESSION['Schedule']);
	    unset($_SESSION['AttributeSet'][$_POST['SKU']]);
	    unset($_SESSION['ReturnPolicyReturns'][$_POST['SKU']]);
	    echo '{success: true, msg: "SKU '.$_POST['SKU'].' Add To Template Success, Template Id '.$id.'!"}';
	    $this->log("template", $_POST['SKU'] . " add to template.");
	}else{
	    echo '{success: false,
		    msg: "Can\'t add, please notice admin."}';
	    $this->log("template", $_POST['SKU'] . " add to template failure.", "error");
	}
    }
    
    public function copyTemplate(){
	$temp = "";
	if(strpos($_POST['ids'], ',')){
	    $array = explode(',', $_POST['ids']);
	    
	    foreach($array as $a){
		$sql_1 = "insert into template (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,ListingDuration,ListingType,PaymentMethods,PayPalEmailAddress,
		PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,UseStandardFooter,scheduleTemplateName,ScheduleStartDate,ScheduleEndDate,shippingTemplateName,StandardStyleTemplateId,status) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,ListingDuration,ListingType,PaymentMethods,PayPalEmailAddress,
		PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,UseStandardFooter,scheduleTemplateName,ScheduleStartDate,ScheduleEndDate,shippingTemplateName,StandardStyleTemplateId,'1' from template where Id = '".$a."'";
		
		//echo $sql_1."\n";
		
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$template_id = mysql_insert_id(eBayListing::$database_connect);
		$temp .= $template_id.", ";
		//var_dump($item_id);
		//exit;
		$sql_2 = "insert into template_picture_url (templateId,url)  select '".$template_id."',url from template_picture_url where templateId = '".$a."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		/*
		$sql_3 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$template_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from template_shipping_service_options where templateId = '".$a."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$template_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from template_international_shipping_service_option where templateId = '".$a."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		*/
		
		$sql_5 = "select * from template_attribute_set where templateId = '".$a."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $template_attribute_set_id = $row_5['attribute_set_id'];
		    $sql_6 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$row_5['attributeSetID']."')";
		    //echo $sql_6."\n";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    $sql_7 = "insert into template_attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
		    where attribute_set_id = '".$template_attribute_set_id."'";
		    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		}
		//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));
		
		$sql_8 = "insert into template_to_template_cateogry (template_id,template_category_id) select '".$template_id."',template_category_id from template_to_template_cateogry where template_id = '".$a."'";
		//echo $sql_8."\n";
		$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
	    
		if($result_1 && $result_2 && $result_5){
		    
		}else{
		    echo 0;
		    return 0;
		}
	    }
	    if(strlen($temp) > 3){
		echo "[{success: true, msg: 'Copy template success, template id is ".substr($temp, 0, -2).".'}]";
	    }
	    //echo 1;
	}else{
	    if(empty($_POST['copy_num'])){
		$_POST['copy_num'] = 1;
	    }
	    
	    for($i = 0; $i < $_POST['copy_num']; $i++){
		$sql_1 = "insert into template (AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,ListingDuration,ListingType,PaymentMethods,PayPalEmailAddress,
		PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,UseStandardFooter,scheduleTemplateName,ScheduleStartDate,ScheduleEndDate,shippingTemplateName,StandardStyleTemplateId,status) select AutoPay,BuyItNowPrice,CategoryMappingAllowed,Country,Currency,
		Description,ListingDuration,ListingType,PaymentMethods,PayPalEmailAddress,
		PrimaryCategoryCategoryID,PrimaryCategoryCategoryName,Quantity,
		ReservePrice,CurrentPrice,SecondaryCategoryCategoryID,SecondaryCategoryCategoryName,ShippingType,Site,SKU,StartPrice,
		StoreCategory2ID,StoreCategory2Name,StoreCategoryID,StoreCategoryName,SubTitle,Title,UserID,accountId,BoldTitle,Border,
		Featured,Highlight,HomePageFeatured,GalleryTypeFeatured,GalleryTypeGallery,GalleryTypePlus,
		GalleryURL,PhotoDisplay,UseStandardFooter,scheduleTemplateName,ScheduleStartDate,ScheduleEndDate,shippingTemplateName,StandardStyleTemplateId,'1' from template where Id = '".$_POST['ids']."'";
		
		//echo $sql_1."\n";
		
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		$template_id = mysql_insert_id(eBayListing::$database_connect);
		$temp .= $template_id.", ";
		//var_dump($item_id);
		//exit;
		$sql_2 = "insert into template_picture_url (templateId,url)  select '".$template_id."',url from template_picture_url where templateId = '".$_POST['ids']."'";
		$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		
		/*
		$sql_3 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) select '".$template_id."',FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority from template_shipping_service_options where templateId = '".$_POST['ids']."'";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		
		$sql_4 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) select '".$template_id."',ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation from template_international_shipping_service_option where templateId = '".$_POST['ids']."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		*/
		
		$sql_5 = "select * from template_attribute_set where templateId = '".$_POST['ids']."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		while($row_5 = mysql_fetch_assoc($result_5)){
		    $template_attribute_set_id = $row_5['attribute_set_id'];
		    $sql_6 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$row_5['attributeSetID']."')";
		    //echo $sql_6."\n";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    $sql_7 = "insert into template_attribute (attributeID,attribute_set_id,ValueID,ValueLiteral) 
		    select attributeID,'".$attribute_set_id."',ValueID,ValueLiteral from template_attribute 
		    where attribute_set_id = '".$template_attribute_set_id."'";
		    $result_7 = mysql_query($sql_7, eBayListing::$database_connect);
		}
		//var_dump(array($result_1, $result_2, $result_3, $result_4, $result_6, $result_7));
		
		$sql_8 = "insert into template_to_template_cateogry (template_id,template_category_id) select '".$template_id."',template_category_id from template_to_template_cateogry where template_id = '".$_POST['ids']."'";
		//echo $sql_8;
		$result_8 = mysql_query($sql_8, eBayListing::$database_connect);
		
		if($result_1 && $result_2 && $result_5 && $result_8){
		    //echo 1;
		}else{
		    echo 0;
		}
	    }
	    
	    if(strlen($template_id) > 3){
		echo "[{success: true, msg: 'Copy template success, template id is ".substr($temp, 0, -2).".'}]";
	    }
	}
    }
    
    public function getTemplate(){
	session_start();
	$sql = "select * from template where Id = '".$_GET['id']."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$row['SiteID'] = $row['Site'];
	$row['Description'] = html_entity_decode($row['Description'], ENT_QUOTES);
	$row['Title'] = html_entity_decode($row['Title'], ENT_QUOTES);
	
	if($row['ListingType'] == "FixedPriceItem" || $row['ListingType'] == "StoresFixedPrice"){
	    $row['BuyItNowPrice'] = $row['StartPrice'];
	    $row['StartPrice'] = 0;
	}
	
	unset($_SESSION['AttributeSet'][$row['Id']]);
	
	$sql_1 = "select url from template_picture_url where templateId = '".$row['Id']."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while($row_1 = mysql_fetch_assoc($result_1)){
	    $row['picture_'.$i] = $row_1['url'];
	    $i++;
	}
	$sql_2 = "select template_category_id from template_to_template_cateogry where template_id = '".$row['Id']."'";
	$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	$row_2 = mysql_fetch_assoc($result_2);
	$row['template_category_id'] = $row_2['template_category_id'];
	
	/*
	$sql_3 = "select * from template_shipping_service_options where templateId = '".$row['Id']."' order by ShippingServicePriority";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	$i = 1;
	while($row_3 = mysql_fetch_assoc($result_3)){
	    $row['ShippingService_'.$i] = $row_3['ShippingService'];
	    $row['ShippingServiceCost_'.$i] = $row_3['ShippingServiceCost'];
	    $row['ShippingServiceFree_'.$i] = $row_3['FreeShipping'];
	    $i++;
	}
	
	$sql_4 = "select * from template_international_shipping_service_option where templateId = '".$row['Id']."' order by ShippingServicePriority";
	$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	$i = 1;
	while($row_4 = mysql_fetch_assoc($result_4)){
	    $row['InternationalShippingService_'.$i] = $row_4['ShippingService'];
	    $row['InternationalShippingServiceCost_'.$i] = $row_4['ShippingServiceCost'];
	    $array = explode(",", $row_4['ShipToLocation']);
	    if(count($array) > 1){
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		foreach($array as $v){
		    $row[$v.'_'.$i] = 1;
		}
	    }elseif($row_4['ShipToLocation'] == "Worldwide"){
		$row['InternationalShippingToLocations_'.$i] = "Worldwide";
	    }else{
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		$row[$row_4['ShipToLocation'].'_'.$i] = 1;
	    }
	    $i++;
	}
	*/
	
	$sql_5 = "select * from template_attribute_set where templateId = '".$row['Id']."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	$row_5 = mysql_fetch_assoc($result_5);
	
	$sql_6 = "select * from template_attribute where attribute_set_id = '".$row_5['attribute_set_id']."'";
	$result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	while($row_6 = mysql_fetch_assoc($result_6)){
	    if(strpos($row_6['ValueID'], ',')){
		$array = explode(',', $row_6['ValueID']);
		foreach($array as $a){
		    $_SESSION['AttributeSet'][$row['Id']][$row_5['attributeSetID']][$a.'_checkbox'] = $row_6['attributeID'].'_on';
		}
	    }else{
		$_SESSION['AttributeSet'][$row['Id']][$row_5['attributeSetID']][$row_6['attributeID']] = $row_6['ValueID'];
	    }
	}
	/*
	unset($_SESSION['Schedule']);
	
	$sql_7 = "select * from schedule where template_id = '".$_GET['id']."'";
	$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	
	$temp = array();
	while($row_7 = mysql_fetch_assoc($result_7)){
	    $row['ScheduleStartDate'] = $row_7['startDate'];
	    $row['ScheduleEndDate'] = $row_7['endDate'];
	    if(array_key_exists($_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time'])), $_SESSION['Schedule'])){
		$_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))][count($_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))])] = date('g:i A', strtotime($row_7['china_time']));
	    }else{
		$_SESSION['Schedule'][$_GET['id'].'-'.$row_7['china_day'].'-'.date('a-g', strtotime($row_7['china_time']))][0] = date('g:i A', strtotime($row_7['china_time']));
	    }
	    $t = date("D-a-g", strtotime($row_7['china_day'] . " " . $row_7['china_time']))."-panel";
	    if(!in_array($t, $temp)){
		$temp[] = $t;
	    }
	}
	$row['Schedule'] = implode(",", $temp);
	*/
	echo '['.json_encode($row).']';
	mysql_free_result($result);
    }
    
    public function updateTemplate(){
	/*
	if(!empty($_POST['UseStandardFooter']) && $_POST['UseStandardFooter'] == 1){
	    $sql = "select footer from account_footer where accountId = '".$this->account_id."'";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $row = mysql_fetch_assoc($result);
	    $_POST['Description'] .= $row['footer'];
	}
	*/
	session_start();
	if($_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    $_POST['StartPrice'] = $_POST['BuyItNowPrice'];
	    $_POST['BuyItNowPrice'] = 0;
	}
	
	if($_POST['ListingType'] == "Chinese"){
	    $_POST['Quantity'] = 1;   
	}
	
	$id = $_GET['template_id'];
	//StartTime,EndTime
	//$PaymentMethods = ($_POST['PayPalPayment'] == 1)?'PayPal':'';

	$sql = "update template set 
	BuyItNowPrice='".$_POST['BuyItNowPrice']."',Country='CN',Currency='".$_POST['Currency']."',
	Description='".htmlentities($_POST['Description'], ENT_QUOTES)."',
	ListingDuration='".$_POST['ListingDuration']."',ListingType='".$_POST['ListingType']."',PaymentMethods='PayPal',
	PayPalEmailAddress='".$_POST['PayPalEmailAddress']."',
	PrimaryCategoryCategoryID='".$_POST['PrimaryCategoryCategoryID']."',PrimaryCategoryCategoryName='".$_POST['PrimaryCategoryCategoryName']."',
	SecondaryCategoryCategoryID='".$_POST['SecondaryCategoryCategoryID']."',SecondaryCategoryCategoryName='".$_POST['SecondaryCategoryCategoryName']."',
	Quantity='".@$_POST['Quantity']."',ReservePrice='".@$_POST['ReservePrice']."',
	Site='".$_POST['Site']."',SKU='".$_POST['SKU']."',StartPrice='".$_POST['StartPrice']."',StoreCategory2ID='".$_POST['StoreCategory2ID']."',StoreCategory2Name='".$_POST['StoreCategory2Name']."',
	StoreCategoryID='".$_POST['StoreCategoryID']."',StoreCategoryName='".$_POST['StoreCategoryName']."',SubTitle='".$_POST['SubTitle']."',
	Title='".htmlentities($_POST['Title'], ENT_QUOTES)."',BoldTitle='".(empty($_POST['BoldTitle'])?0:1)."',
	Border='".(empty($_POST['Border'])?0:1)."',Featured='".(empty($_POST['Featured'])?0:1)."',Highlight='".(empty($_POST['Highlight'])?0:1)."',
	HomePageFeatured='".(empty($_POST['HomePageFeatured'])?0:1)."',GalleryTypeFeatured='".(empty($_POST['GalleryTypeFeatured'])?0:1)."',GalleryTypePlus='".(empty($_POST['GalleryTypePlus'])?0:1)."',GalleryURL='".$_POST['GalleryURL']."',
	UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."',accountId='".$this->account_id."',UseStandardFooter='".(empty($_POST['UseStandardFooter'])?0:1)."',
	".((!empty($_POST['ScheduleStartDate']))?"ScheduleStartDate='".$_POST['ScheduleStartDate']."',":"").((!empty($_POST['ScheduleEndDate']))?"ScheduleEndDate='".$_POST['ScheduleEndDate']."',":"").
        "scheduleTemplateName='".$_POST['scheduleTemplateName']."',shippingTemplateName='".$_POST['shippingTemplateName']."',
        ShippingServiceCost1=".(($_POST['ShippingServiceCost1'] == "")?'NULL':$_POST['ShippingServiceCost1']).",ShippingServiceAdditionalCost1=".(($_POST['ShippingServiceAdditionalCost1']=="")?'NULL':$_POST['ShippingServiceAdditionalCost1']).",
        ShippingServiceCost2=".(($_POST['ShippingServiceCost2'] == "")?'NULL':$_POST['ShippingServiceCost2']).",ShippingServiceAdditionalCost2=".(($_POST['ShippingServiceAdditionalCost2']=="")?'NULL':$_POST['ShippingServiceAdditionalCost2']).",
        ShippingServiceCost3=".(($_POST['ShippingServiceCost3'] == "")?'NULL':$_POST['ShippingServiceCost3']).",ShippingServiceAdditionalCost3=".(($_POST['ShippingServiceAdditionalCost3']=="")?'NULL':$_POST['ShippingServiceAdditionalCost3']).",
        InternationalShippingServiceCost1=".(($_POST['InternationalShippingServiceCost1']=="")?'NULL':$_POST['InternationalShippingServiceCost1']).",InternationalShippingServiceAdditionalCost1=".(($_POST['InternationalShippingServiceAdditionalCost1']=="")?'NULL':$_POST['InternationalShippingServiceAdditionalCost1']).",
        InternationalShippingServiceCost2=".(($_POST['InternationalShippingServiceCost2']=="")?'NULL':$_POST['InternationalShippingServiceCost2']).",InternationalShippingServiceAdditionalCost2=".(($_POST['InternationalShippingServiceAdditionalCost2']=="")?'NULL':$_POST['InternationalShippingServiceAdditionalCost2']).",
        InternationalShippingServiceCost3=".(($_POST['InternationalShippingServiceCost3']=="")?'NULL':$_POST['InternationalShippingServiceCost3']).",InternationalShippingServiceAdditionalCost3=".(($_POST['InternationalShippingServiceAdditionalCost3']=="")?'NULL':$_POST['InternationalShippingServiceAdditionalCost3']).",
        StandardStyleTemplateId=".(empty($_POST['StandardStyleTemplateId'])?'NULL':"'".$_POST['StandardStyleTemplateId']."'").",status=0 where Id = '".$id."'";
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $sql;
	//exit;
	//$this->log("template", $sql);
	//debugLog("template-tack.log", $sql);
	
	$sql_1 = "delete from template_picture_url where templateId = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['picture_'.$i])){
	    $sql_1 = "insert into template_picture_url (templateId,url) values 
	    ('".$id."','".$_POST['picture_'.$i]."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	/*
	$sql_1 = "delete from template_shipping_service_options where templateId = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['ShippingService_'.$i])){
	    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	$sql_1 = "delete from template_international_shipping_service_option where templateId = '".$id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$i = 1;
	while(!empty($_POST['InternationalShippingService_'.$i])){
	    $ShipToLocation = '';
	    if($_POST['InternationalShippingToLocations_'.$i] == 'Custom Locations'){
		if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
		    $ShipToLocation .= ',Americas';
		}
		
		if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
		    $ShipToLocation .= ',US';
		}
		
		if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
		    $ShipToLocation .= ',Europe';
		}
		
		if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
		    $ShipToLocation .= ',Asia';
		}
		
		if(!empty($_POST['CA_'.$i]) && $_POST['CA_'.$i] == 1){
		    $ShipToLocation .= ',CA';
		}
		
		if(!empty($_POST['GB_'.$i]) && $_POST['GB_'.$i] == 1){
		    $ShipToLocation .= ',GB';
		}
		
		if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
		    $ShipToLocation .= ',AU';
		}
		
		if(!empty($_POST['MX_'.$i]) && $_POST['MX_'.$i] == 1){
		    $ShipToLocation .= ',MX';
		}
		
		if(!empty($_POST['DE_'.$i]) && $_POST['DE_'.$i] == 1){
		    $ShipToLocation .= ',DE';
		}
		
		if(!empty($_POST['JP_'.$i]) && $_POST['JP_'.$i] == 1){
		    $ShipToLocation .= ',JP';
		}
		
		$ShipToLocation = substr($ShipToLocation, 1);
	    }else{
		$ShipToLocation = 'Worldwide';
	    }
	    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	*/
	    
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$id])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    $sql_4 = "select attribute_set_id from template_attribute_set where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    $row_4 = mysql_fetch_assoc($result_4);
	    
	    $sql_4 = "delete from template_attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	    
	    $sql_4 = "delete from template_attribute_set where templateId = '".$id."'";
	    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	
	    foreach($_SESSION['AttributeSet'][$id] as $attributeSetID=>$Attribute){
		$sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		
		$attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		$temp_array = array();
		foreach($Attribute as $attributeID=>$ValueID){
		    if(!empty($ValueID)){
			if(strpos($ValueID, "on") != false){
			    $tempAttributeID = $attributeID;
			    $attributeID = substr($ValueID, 0, -3);
			    $ValueID = substr($tempAttributeID, 0, -9);
			    //echo $attributeID.":".$ValueID;
			    //echo "\n";
			    $temp_array[$attributeID][] = $ValueID;
			}else{
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
				('".$attributeID."', '".$attribute_set_id."', '".$ValueID."')";
				$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
				
				//echo $sql_4;
				//echo "\n";
			}
		    }
		}
		
		//print_r($temp_array);
		if(count($temp_array) > 0){
		    foreach($temp_array as $key=>$value){
			$ValueID = "";
			foreach($value as $name){
			    $ValueID .= $name.',';
			}
			$ValueID = substr($ValueID, 0, -1);
			$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
			('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    }
		}
	    }
	}
	
	$sql_5 = "select count(*) as num from template_to_template_cateogry where template_id = '".$id."'";
	$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
	$row_5 = mysql_fetch_assoc($result_5);
	
	if($row_5['num'] > 0){
	    $sql_6 = "update template_to_template_cateogry set template_category_id = '".$_POST['template_category_id']."' where template_id = '".$id."'";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	}else{
	    $sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$id."','".$_POST['template_category_id']."')";
	    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
	}
	
	
	if($result && $result_1 && $result_6){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "Update Template Success!"}';
	    $this->log("template", "update template ".$id." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update template."}
		}';
	    $this->log("template", "update template ".$id." failure.", "error");
	}
    }
    
    public function updateMultiTemplate(){
	//print_r($_POST);
	$ids = explode(',', $_GET['template_id']);
	
	$where = " where Id in (";
	foreach($ids as $id){
	    $where .= $id.",";
	}
	$where = substr($where, 0, -1);
	$where .= ")";
	
	$update = "update template set ";
	
	if(!empty($_POST['ListingType']) && $_POST['ListingType'] == "FixedPriceItem" || $_POST['ListingType'] == "StoresFixedPrice"){
	    if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
		$update .= "StartPrice = '".$_POST['BuyItNowPrice']."',";
		$update .= "BuyItNowPrice = '0',";
	    }
	}else{
	    if(!empty($_POST['BuyItNowPrice']) && $_POST['BuyItNowPrice'] != 'Multi Value'){
		$update .= "BuyItNowPrice = '".$_POST['BuyItNowPrice']."',";
	    }
	    if(!empty($_POST['StartPrice']) && $_POST['StartPrice'] != 'Multi Value'){
		$update .= "StartPrice = '".$_POST['StartPrice']."',";
	    }
	}
	
	if(!empty($_POST['Currency']) && $_POST['Currency'] != 'Multi Value'){
	    $update .= "Currency = '".$_POST['Currency']."',";
	}
	
	if(!empty($_POST['Description']) && $_POST['Description'] != 'Multi Value'){
	    $update .= "Description = '".htmlentities($_POST['Description'], ENT_QUOTES)."',";
	}
	/*
	if(!empty($_POST['DispatchTimeMax']) && $_POST['DispatchTimeMax'] != 'Multi Value'){
	    $update .= "DispatchTimeMax = '".$_POST['DispatchTimeMax']."',";
	}
	*/
	if(!empty($_POST['ListingDuration']) && $_POST['ListingDuration'] != 'Multi Value'){
	    $update .= "ListingDuration = '".$_POST['ListingDuration']."',";
	}
	
	if(!empty($_POST['ListingType']) && $_POST['ListingType'] != 'Multi Value'){
	    $update .= "ListingType = '".$_POST['ListingType']."',";
	}
	/*
	if(!empty($_POST['Location']) && $_POST['Location'] != 'Multi Value'){
	    $update .= "Location = '".$_POST['Location']."',";
	}
	*/
	if(!empty($_POST['PayPalEmailAddress']) && $_POST['PayPalEmailAddress'] != 'Multi Value'){
	    $update .= "PayPalEmailAddress = '".$_POST['PayPalEmailAddress']."',";
	}
	/*
	if(!empty($_POST['PostalCode']) && $_POST['PostalCode'] != 'Multi Value'){
	    $update .= "PostalCode = '".$_POST['PostalCode']."',";
	}
	*/
	if(!empty($_POST['PrimaryCategoryCategoryID']) && $_POST['PrimaryCategoryCategoryID'] != 'Multi Value'){
	    $update .= "PrimaryCategoryCategoryID = '".$_POST['PrimaryCategoryCategoryID']."',";
	}
	
	if(!empty($_POST['PrimaryCategoryCategoryName']) && $_POST['PrimaryCategoryCategoryName'] != 'Multi Value'){
	    $update .= "PrimaryCategoryCategoryName = '".$_POST['PrimaryCategoryCategoryName']."',";
	}
	
	if(!empty($_POST['SecondaryCategoryCategoryID']) && $_POST['SecondaryCategoryCategoryID'] != 'Multi Value'){
	    $update .= "SecondaryCategoryCategoryID = '".$_POST['SecondaryCategoryCategoryID']."',";
	}
	
	if(!empty($_POST['SecondaryCategoryCategoryName']) && $_POST['SecondaryCategoryCategoryName'] != 'Multi Value'){
	    $update .= "SecondaryCategoryCategoryName = '".$_POST['SecondaryCategoryCategoryName']."',";
	}
	
	if(!empty($_POST['Quantity']) && $_POST['Quantity'] != 'Multi Value'){
	    $update .= "Quantity = '".$_POST['Quantity']."',";
	}
	
	if(!empty($_POST['ReservePrice']) && $_POST['ReservePrice'] != 'Multi Value'){
	    $update .= "ReservePrice = '".$_POST['ReservePrice']."',";
	}
	
	if(!empty($_POST['Site']) && $_POST['Site'] != 'Multi Value'){
	    $update .= "Site = '".$_POST['Site']."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['StoreCategory2ID']) && $_POST['StoreCategory2ID'] != 'Multi Value'){
	    $update .= "StoreCategory2ID = '".$_POST['StoreCategory2ID']."',";
	}
	
	if(!empty($_POST['StoreCategory2Name']) && $_POST['StoreCategory2Name'] != 'Multi Value'){
	    $update .= "StoreCategory2Name = '".$_POST['StoreCategory2Name']."',";
	}
	
	if(!empty($_POST['StoreCategoryID']) && $_POST['StoreCategoryID'] != 'Multi Value'){
	    $update .= "StoreCategoryID = '".$_POST['StoreCategoryID']."',";
	}
	
	if(!empty($_POST['StoreCategoryName']) && $_POST['StoreCategoryName'] != 'Multi Value'){
	    $update .= "StoreCategoryName = '".$_POST['StoreCategoryName']."',";
	}
	
	
	if(!empty($_POST['SubTitle']) && $_POST['SubTitle'] != 'Multi Value'){
	    $update .= "SubTitle = '".mysql_real_escape_string($_POST['SubTitle'])."',";
	}
	
	if(!empty($_POST['Title']) && $_POST['Title'] != 'Multi Value'){
	    $update .= "Title = '".htmlentities($_POST['Title'], ENT_QUOTES)."',";
	}
	
	if(!empty($_POST['SKU']) && $_POST['SKU'] != 'Multi Value'){
	    $update .= "SKU = '".$_POST['SKU']."',";
	}
	
	if(!empty($_POST['Border'])){
	    $update .= "Border = '".$_POST['Border']."',";
	}
	//---------------------------------------------------------------------------------
	if(!empty($_POST['GalleryURL'])){
	    $update .= "GalleryURL = '".$_POST['GalleryURL']."',";
	}
	
	/*
	if(!empty($_POST['ShippingServiceOptionsType']) && $_POST['ShippingServiceOptionsType'] != 'Multi Value'){
	    $update .= "ShippingServiceOptionsType = '".$_POST['ShippingServiceOptionsType']."',";
	}
	
	if(!empty($_POST['InsuranceOption']) && $_POST['InsuranceOption'] != 'Multi Value'){
	    $update .= "InsuranceOption = '".$_POST['InsuranceOption']."',";
	}
	
	if(!empty($_POST['InsuranceFee']) && $_POST['InsuranceFee'] != 'Multi Value'){
	    $update .= "InsuranceFee = '".$_POST['InsuranceFee']."',";
	}
	
	if(!empty($_POST['InternationalShippingServiceOptionType']) && $_POST['InternationalShippingServiceOptionType'] != 'Multi Value'){
	    $update .= "InternationalShippingServiceOptionType = '".$_POST['InternationalShippingServiceOptionType']."',";
	}
	
	if(!empty($_POST['InternationalInsurance']) && $_POST['InternationalInsurance'] != 'Multi Value'){
	    $update .= "InternationalInsurance = '".$_POST['InternationalInsurance']."',";
	}
	
	if(!empty($_POST['InternationalInsuranceFee']) && $_POST['InternationalInsuranceFee'] != 'Multi Value'){
	    $update .= "InternationalInsuranceFee = '".$_POST['InternationalInsuranceFee']."',";
	}
	
	
	session_start();
	if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']])){
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsAcceptedOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsAcceptedOption'] != 'Multi Value'){
		$update .= "ReturnPolicyReturnsAcceptedOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsAcceptedOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsWithinOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsWithinOption'] != 'Multi Value'){
		$update .= "ReturnPolicyReturnsWithinOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyReturnsWithinOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyRefundOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyRefundOption'] != 'Multi Value'){
		$update .= "ReturnPolicyRefundOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyRefundOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyShippingCostPaidByOption']) && $_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyShippingCostPaidByOption'] != 'Multi Value'){
		$update .= "ReturnPolicyShippingCostPaidByOption = '".$_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyShippingCostPaidByOption']."',";
	    }
	    
	    if(!empty($_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyDescription']) && $_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyDescription'] != 'Multi Value'){
		$update .= "ReturnPolicyDescription = '".$_SESSION['ReturnPolicyReturns'][$_GET['template_id']]['ReturnPolicyDescription']."',";
	    }
	}
	*/
	if(!empty($_POST['UseStandardFooter'])){
	    $update .= "UseStandardFooter = '1',";
	}
	
	if(!empty($_POST['ScheduleStartDate'])){
	    $update .= "ScheduleStartDate = '".$_POST['ScheduleStartDate']."',";
	}
	
	if(!empty($_POST['ScheduleEndDate'])){
	    $update .= "ScheduleEndDate = '".$_POST['ScheduleEndDate']."',";
	}
	
	if(!empty($_POST['scheduleTemplateName'])){
	    $update .= "scheduleTemplateName = '".$_POST['scheduleTemplateName']."',";
	}
	
	if(!empty($_POST['shippingTemplateName'])){
	    $update .= "shippingTemplateName = '".$_POST['shippingTemplateName']."',";
	}
	
        $update .= "status = 0,";
        
	$update = substr($update, 0, -1);
	$sql = $update . $where;
	//$this->log("template", $sql);
	
	$result = mysql_query($sql, eBayListing::$database_connect);
	//echo $result;
	//print_r($_POST);
	$where = " where templateId in (";
	foreach($ids as $id){
	    $where .= $id.",";
	}
	$where = substr($where, 0, -1);
	$where .= ")";
	
	if(!empty($_POST['picture_1']) && $_POST['picture_1'] != 'Multi Value'){
	    $sql_1 = "delete from template_picture_url ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['picture_'.$i]) && $_POST['picture_'.$i] != 'Multi Value'){
		    $sql_1 = "insert into template_picture_url (templateId,url) values 
		    ('".$id."','".$_POST['picture_'.$i]."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	/*
	if(!empty($_POST['ShippingService_1'])){
	    $sql_1 = "delete from template_shipping_service_options ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    
	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['ShippingService_'.$i])){
		    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServicePriority) values
		    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$i."')";
		    //echo $sql_1."\n";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	
	if(!empty($_POST['InternationalShippingService_1'])){
	    $sql_1 = "delete from template_international_shipping_service_option ".$where;
	    //echo $sql_1."\n";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);

	    foreach($ids as $id){
		$i = 1;
		while(!empty($_POST['InternationalShippingService_'.$i])){
		    $ShipToLocation = '';
		    if($_POST['InternationalShippingToLocations_'.$i] == 'Custom Locations'){
			if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
			    $ShipToLocation .= ',Americas';
			}
			
			if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
			    $ShipToLocation .= ',US';
			}
			
			if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
			    $ShipToLocation .= ',Europe';
			}
			
			if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
			    $ShipToLocation .= ',Asia';
			}
			
			if(!empty($_POST['CA_'.$i]) && $_POST['CA_'.$i] == 1){
			    $ShipToLocation .= ',CA';
			}
			
			if(!empty($_POST['GB_'.$i]) && $_POST['GB_'.$i] == 1){
			    $ShipToLocation .= ',GB';
			}
			
			if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
			    $ShipToLocation .= ',AU';
			}
			
			if(!empty($_POST['MX_'.$i]) && $_POST['MX_'.$i] == 1){
			    $ShipToLocation .= ',MX';
			}
			
			if(!empty($_POST['DE_'.$i]) && $_POST['DE_'.$i] == 1){
			    $ShipToLocation .= ',DE';
			}
			
			if(!empty($_POST['JP_'.$i]) && $_POST['JP_'.$i] == 1){
			    $ShipToLocation .= ',JP';
			}
			
			$ShipToLocation = substr($ShipToLocation, 1);
		    }else{
			$ShipToLocation = 'Worldwide';
		    }
		    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServicePriority,ShipToLocation) values
		    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$i."','".$ShipToLocation."')";
		    //echo $sql_2."\n";
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		    $i++;
		}
	    }
	}
	*/
		    
	$temp_array = array();
	if(!empty($_SESSION['AttributeSet'][$_GET['template_id']])){
	    //print_r($_SESSION['AttributeSet']);
	    //exit;
	    foreach($ids as $id){
		//exit;
		$sql_4 = "select attribute_set_id from template_attribute_set where templateId = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		$row_4 = mysql_fetch_assoc($result_4);
		//$this->log("template", $sql_4);
		
		$sql_4 = "delete from template_attribute where attribute_set_id = '".$row_4['attribute_set_id']."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		//$this->log("template", $sql_4);
		
		$sql_4 = "delete from template_attribute_set where templateId = '".$id."'";
		$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		//$this->log("template", $sql_4);
		
		foreach($_SESSION['AttributeSet'][$_GET['template_id']] as $attributeSetID=>$Attribute){
		    $sql_4 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$id."', '".$attributeSetID."')";
		    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
		    //$this->log("template", $sql_4);
		    
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    $temp_array = array();
		    foreach($Attribute as $attributeID=>$ValueID){
			if(!empty($ValueID)){
			    if(strpos($ValueID, "on") != false){
				$tempAttributeID = $attributeID;
				$attributeID = substr($ValueID, 0, -3);
				$ValueID = substr($tempAttributeID, 0, -9);
				//echo $attributeID.":".$ValueID;
				//echo "\n";
				$temp_array[$attributeID][] = $ValueID;
			    }else{
				    $sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
				    ('".$attributeID."', '".$attribute_set_id."', '".$ValueID."')";
				    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
				    //$this->log("template", $sql_4);
			    }
			}
		    }
		    
		    //print_r($temp_array);
		    if(count($temp_array) > 0){
			foreach($temp_array as $key=>$value){
			    $ValueID = "";
			    foreach($value as $name){
				$ValueID .= $name.',';
			    }
			    $ValueID = substr($ValueID, 0, -1);
			    $sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) values 
			    ('".$key."', '".$attribute_set_id."', '".$ValueID."')";
			    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
			    //$this->log("template", $sql_4);
			}
		    }
		}
	    }
	}
	
	if(!empty($_POST['template_category_id'])){
	    foreach($ids as $id){
		$sql_5 = "select count(*) as num from template_to_template_cateogry where template_id = '".$id."'";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		$row_5 = mysql_fetch_assoc($result_5);
		
		if($row_5['num'] > 0){
		    $sql_6 = "update template_to_template_cateogry set template_category_id = '".$_POST['template_category_id']."' where template_id = '".$id."'";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}else{
		    $sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$id."','".$_POST['template_category_id']."')";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}
	    }
	}
	
	if($result || $result_6){
	    //unset($_SESSION['Schedule']);
	    //unset($_SESSION['AttributeSet']);
	    echo '{success: true, msg: "Update Template '.$_GET['template_id'].' Success!"}';
	    $this->log("template", "update multi template ".$_GET['template_id']." success.");
	}else{
	    echo '{success: false,
		    errors: {message: "can\'t update, please notice admin."}
		}';
	    $this->log("template", "update multi template ".$_GET['template_id']." failure.", "error");
	}
    }
    
    public function importTemplateFromSpoonFeeder(){
	//$handle = fopen("./a07052200ux0011.aie", "r");
	//print_r($_FILES);
	//exit;
	$success = false;
	$i = 1;
	$template_id_str = "";
	
	$file_array = array();
	
	while($_FILES['aie-'.$i]['error'] == 0){
	    $result = move_uploaded_file($_FILES['aie-'.$i]['tmp_name'], self::UPLOAD_TEMP_DIR.$_FILES['aie-'.$i]['name']);
	    if($result != false){
		$file_array[] = self::UPLOAD_TEMP_DIR.$_FILES['aie-'.$i]['name'];
	    }
	    $i++;
	}
	
	foreach($file_array as $file){
	    $handle = fopen($file, "r");
	    if ($handle) {
		$temp == 0;
		$data = array ();
		$data['accountId'] = $this->account_id;
		$data['PaymentMethods'] = 'PayPal';
		while (!feof($handle)) {
		    $buffer = trim(fgets($handle/*, 4096*/));
		    //echo $buffer;
		    if(!empty($buffer) && $buffer[0] == "[" && $buffer[strlen($buffer)-1] == "]"){
			$var_name = substr($buffer, 1, -1);
			//$count++;
			//echo $var_name;
			//echo "<br>";
			if($temp == 1 && $var_name == "DESCRIPTIONFONT"){
			    $temp = 2;
			}
		    }else{
			//@$data[$var_name] .= $buffer;
			switch($var_name){
			    //EBAY FIXED PRICE UK, EBAY AUCTION UK,
			    case "SELLING SITE":
				//$pos = strripos($buffer, " ");
				//$data['Site'] = substr($buffer, $pos+1);
				if(strpos($buffer,"FIXED PRICE")){
				    $data['ListingType'] = "FixedPriceItem";
				}elseif(strpos($buffer,"AUCTION")){
				    $data['ListingType'] = "Dutch";
				}elseif(strpos($buffer,"SHOPS")){
				    $data['ListingType'] = "StoresFixedPrice";
				}
				
				if(strpos($buffer, "USD")){
				    $data['Site'] = "US";
				}elseif(strpos($buffer, "UK")){
				    $data['Site'] = "UK";
				}elseif(strpos($buffer, "AU")){
				    $data['Site'] = "Australia";
				}elseif(strpos($buffer, "FRANCE")){
				    $data['Site'] = "France";
				}elseif(strpos($buffer, "GERMANY")){
				    $data['Site'] = "Germany";
				}
				//$data['ListingType'] = substr($buffer, $pos+1);
			    break;
			
			    case "MINIMUM BID PRICE":
				$data['StartPrice'] = $buffer;
			    break;
			
			    case "RESERVE PRICE":
				$data['ReservePrice'] = $buffer;
			    break;
			
			    case "BUY PRICE":
				$data['BuyItNowPrice'] = $buffer;
			    break;
			    
			    case "COUNTRY":
				$data['Country'] = $buffer;
			    break;
			
			    case "CURRENCY":
				//$pos = strpos($buffer, " ");
				//$data['Currency'] = substr($buffer, 0, $pos);
				$data['Currency'] = $buffer;
			    break;
			
			    case "DESCRIPTION":
				$temp = 1;
				$data['Description'] .= $buffer;
			    break;
			
			    case "DOMESTIC HANDLING TIME":
				$data['DispatchTimeMax'] = $buffer;
			    break;
			
			    case "AUCTION DURATION":
				$data['ListingDuration'] = 'Days_'.$buffer;
			    break;
			
			    case "LOCATION":
				$data['Location'] = $buffer;
			    break;
			
			    case "PAYPAL EMAIL":
				$data['PayPalEmailAddress'] = $buffer;
			    break;
			
			    case ""://
				$data['PostalCode'] = $buffer;
			    break;
			
			    case "CATEGORY 1":
				$data['PrimaryCategoryCategoryID'] = $buffer;
			    break;
			
			    case "CATEGORYDES 1":
				$data['PrimaryCategoryCategoryName'] = $buffer;
			    break;
			    
			    case "QUANTITY":
				$data['Quantity'] = $buffer;
			    break;
			
			    case "RETURN POLICY"://
				$data['ReturnPolicyDescription'] = $buffer;
			    break;
			
			    case "RETURN POLICY REFUND":
				$data['ReturnPolicyRefundOption'] = $buffer;
			    break;
			
			    case "RETURN POLICY ENABLED":
				if($buffer == "TRUE"){
				    $data['ReturnPolicyReturnsAcceptedOption'] = "ReturnsAccepted";
				}else{
				    $data['ReturnPolicyReturnsAcceptedOption'] = "ReturnsNotAccepted";
				}
			    break;
			
			    case "RETURN POLICY DAYS WITHIN":
				$r = explode(" ", $buffer);
				$data['ReturnPolicyReturnsWithinOption'] = $r[1]."_".$r[0];
			    break;
			
			    case "RETURN POLICY SHIPPING PAID BY":
				$data['ReturnPolicyShippingCostPaidByOption'] = $buffer;
			    break;
			
			    case "CATEGORY 2":
				$data['SecondaryCategoryCategoryID'] = $buffer;
			    break;
			
			    case "CATEGORYDES 2":
				$data['SecondaryCategoryCategoryName'] = $buffer;
			    break;
			
			    case "SKU CODE":
				$data['SKU'] = $buffer;
			    break;
			
			    case "STORE CATEGORY 2":
				$data['StoreCategory2ID'] = $buffer;
			    break;
			
			    case "STORECATEGORYDES 2":
				$data['StoreCategory2Name'] = $buffer;
			    break;
			
			    case "STORE CATEGORY":
				$data['StoreCategoryID'] = $buffer;
			    break;
			
			    case "STORECATEGORYDES":
				$data['StoreCategoryName'] = $buffer;
			    break;
			
			    case "SUBTITLE":
				$data['SubTitle'] = $buffer;
			    break;
			
			    case "TITLE":
				$data['Title'] = $buffer;
			    break;
			
			    case "GALLERY URL":
				$data['GalleryURL'] = $buffer;
			    break;
			
			    case "PICTURE URL":
				$picture = $buffer;
			    break;
			
			    case "INSURANCE OPTION":
				switch($buffer){
				    case "0":
					$data['InsuranceOption'] = "NotOffered";
				    break;
				
				    case "1":
					$data['InsuranceOption'] = "Optional";
				    break;
				
				    case "2":
					$data['InsuranceOption'] = "Required";
				    break;
				
				    case "3":
					$data['InsuranceOption'] = "IncludedInShippingHandling";
				    break;
				}
			    break;
			
			    case "INSURANCE":
				$data['InsuranceFee'] = $buffer;
			    break;
			
			    case "INTERNATIONAL INSURANCE OPTION":
				switch($buffer){
				    case "0":
					$data['InternationalInsurance'] = "NotOffered";
				    break;
				
				    case "1":
					$data['InternationalInsurance'] = "Optional";
				    break;
				
				    case "2":
					$data['InternationalInsurance'] = "Required";
				    break;
				
				    case "3":
					$data['InternationalInsurance'] = "IncludedInShippingHandling";
				    break;
				}
			    break;
			
			    case "INTERNATIONAL INSURANCE":
				$data['InternationalInsuranceFee'] = $buffer;
			    break;
			    
			//-------------------------- shipping service options  ----------------------------------------   
			    case "FREE SHIPPING":
				$data_1['shipping_service_options'][1]['FreeShipping'] = ($buffer=="TRUE")?1:0;
			    break;
			
			    case "SHIPPING SERVICE NAME":
				$data_1['shipping_service_options'][1]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][1]['ShippingServicePriority'] = 1;
			    break;
			
			    case "SHIPPING COST":
				$data_1['shipping_service_options'][1]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST":
				$data_1['shipping_service_options'][1]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "SHIPPING SERVICE NAME2":
				$data_1['shipping_service_options'][2]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][2]['ShippingServicePriority'] = 2;
			    break;
			
			    case "SHIPPING COST2":
				$data_1['shipping_service_options'][2]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST2":
				$data_1['shipping_service_options'][2]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "SHIPPING SERVICE NAME3":
				$data_1['shipping_service_options'][3]['ShippingService'] = $buffer;
				$data_1['shipping_service_options'][3]['ShippingServicePriority'] = 3;
			    break;
			
			    case "SHIPPING COST3":
				$data_1['shipping_service_options'][3]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "ADDITIONAL SHIPPING COST3":
				$data_1['shipping_service_options'][3]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			//--------------------------  international shipping service option --------------------------
			    case "INTERNATIONAL SHIPPING SERVICE NAME":
				$data_2['international_shipping_service_option'][1]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][1]['ShippingServicePriority'] = 1;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST":
				$data_2['international_shipping_service_option'][1]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST":
				$data_2['international_shipping_service_option'][1]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIP TO LOCATIONS":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][1]['ShipToLocation'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIPPING SERVICE NAME2":
				$data_2['international_shipping_service_option'][2]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][2]['ShippingServicePriority'] = 2;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST2":
				$data_2['international_shipping_service_option'][2]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST2":
				$data_2['international_shipping_service_option'][2]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			 
			    case "INTERNATIONAL SHIP TO LOCATIONS2":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][2]['ShipToLocation'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIPPING SERVICE NAME3":
				$data_2['international_shipping_service_option'][3]['ShippingService'] = $buffer;
				$data_2['international_shipping_service_option'][3]['ShippingServicePriority'] = 3;
			    break;
			
			    case "INTERNATIONAL SHIPPING COST3":
				$data_2['international_shipping_service_option'][3]['ShippingServiceCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL ADDITIONAL SHIPPING COST3":
				$data_2['international_shipping_service_option'][3]['ShippingServiceAdditionalCost'] = $buffer;
			    break;
			
			    case "INTERNATIONAL SHIP TO LOCATIONS3":
				$buffer = str_replace(array("1","2","3","4","5","6","7","8","9","10","11","12"),
						      array("None", "Worldwide", "Americas", "Asia", "AU", "CA",
							    "Europe", "DE", "JP", "MX", "GB", "Americas"), $buffer);
				$data_2['international_shipping_service_option'][3]['ShipToLocation'] = $buffer;
			    break;
			
			    //--------------------  attribute  ----------------------------------------------------
			    case "ATTRIBUTE FOR CATEGORY 1":
				//$xml = new SimpleXMLElement($buffer);
				//print_r($xml);
				//echo $buffer;
				$dom = new DOMDocument();
				$dom->loadXML($buffer);
				$AttributeSet = $dom->getElementsByTagName("AttributeSet");
				foreach($AttributeSet as $AttributeSetNode){
				    //echo $AttributeSetNode->getAttribute("id");
				    //echo "<br>";
				    $data_3['AttributeSet']['id'] = $AttributeSetNode->getAttribute("id");
				}
				
				$Attribute = $dom->getElementsByTagName("Attribute");
				$i = 0;
				foreach($Attribute as $AttributeNode){
				    $data_3['AttributeSet']['Attribute'][$i]['id'] = $AttributeNode->getAttribute("id");
				    //echo "Attribute id:" . $AttributeNode->getAttribute("id");
				    //echo "<br>";
				    $Value = $AttributeNode->getElementsByTagName("Value");
				    $j = 0;
				    foreach($Value as $ValueNode){
					$data_3['AttributeSet']['Attribute'][$i]['Value'][$j]['id'] = $ValueNode->getAttribute("id");
					//echo "Value id:" . $ValueNode->getAttribute("id");
					//echo "<br>";
					$Name = $ValueNode->getElementsByTagName("Name");
					foreach($Name as $NameNode){
					    $data_3['AttributeSet']['Attribute'][$i]['Value'][$j]['Name'] = $NameNode->textContent;
					    //echo "Name:" .$NameNode->textContent;
					    //echo "<br>";
					}
					$j++;
				    }
				    //echo "<br>";
				    $i++;
				}
				//print_r($data_3);
				//print_r($AttributeSet);
				//$data['attribute_set'][$xml->@attributes] = $buffer;
				//$data[''] = $buffer;
			    break;
			}
			if($temp == 1){
			    $var_name = "DESCRIPTION";
			}else{
			    $var_name = "";
			}
		    }
		}
		
		/*
		fclose($handle);
		print_r($data);
		print_r($data_1);
		print_r($data_2);
		print_r($data_3);
		exit;
		*/
		
		$fields = "";
		$values = "";
    
		foreach($data as $id=>$name){
		    $fields .= $id.",";
		    $values .= "'".mysql_real_escape_string($name)."',";
		}
		$fields = substr($fields, 0, -1);
		$values = substr($values, 0, -1);
		$sql = "insert into template ($fields) values ($values)";
		//echo $sql."\n";
		//$this->saveFetchData("test.sql", $sql);
		$result = mysql_query($sql, eBayListing::$database_connect);
		$template_id = mysql_insert_id(eBayListing::$database_connect);
		
		foreach($data_1['shipping_service_options'] as $t){
		    if(!empty($t['ShippingService'])){
			$sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) 
			values ('".$template_id."','".$t['FreeShipping']."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."')";
			//echo $sql_1."\n";
			$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		    }
		}
		
		foreach($data_2['international_shipping_service_option'] as $t){
		    if(!empty($t['ShippingService'])){
			$sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) 
			values ('".$template_id."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."','".$t['ShipToLocation']."')";
			//echo $sql_2."\n";
			$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		    }
		}
		
		if(empty($data_3['AttributeSet'])){
		    $sql_3 = "insert into template_attribute_set (templateId,attributeSetID) values ('".$template_id."','".$data_3['AttributeSet']['id']."')";
		    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		    $attribute_set_id = mysql_insert_id(eBayListing::$database_connect);
		    
		    foreach($data_3['AttributeSet']['Attribute'] as $a){
			foreach($a['Value'] as $v){
			    if(empty($v['Name'])){
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueID) 
				values('".$a['id']."','".$attribute_set_id."','".$v['id']."')";
			    }else{
				$sql_4 = "insert into template_attribute (attributeID,attribute_set_id,ValueLiteral) 
				values('".$a['id']."','".$attribute_set_id."','".$v['Name']."')";
			    }
			    $result_4 = mysql_query($sql_4, eBayListing::$database_connect);
			}
		    }
		}
		
		$sql_5 = "insert into template_picture_url (templateId,url) values ('".$template_id."','".$picture."')";
		$result_5 = mysql_query($sql_5, eBayListing::$database_connect);
		
		if(!empty($_POST['template_category_id'])){
		    $sql_6 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$template_id."','".$_POST['template_category_id']."')";
		    $result_6 = mysql_query($sql_6, eBayListing::$database_connect);
		}
		
		if($result){
		    $success = true;
		    $template_id_str .= $template_id . ", ";
		    //echo '{success: true, msg: "Import SpoonFeeder Template Success, template id is '.$template_id.'"}';
		}else{
		    $success = false;
		    //echo '{success: false, msg: ""}';
		}
	    }
	}
	
	if($success){
	    echo '{success: true, msg: "Import SpoonFeeder Template Success, template id is '.substr($template_id_str, 0, -2).'."}';
	}else{
	    echo '{success: false, msg: ""}';
	}
    }
    
    public function importTemplateFromTurboLister(){
	$handle = fopen($_FILES['turboLister']['tmp_name'], "r");
	//$handle = fopen('./burbo lister.csv', "r");
	$i = 0;
	while (($data = fgetcsv($handle/*, 4602, ","*/)) !== FALSE) {
	    if($i == 0){
		$i++;
		continue;
	    }
	    $array = array();
	    $array['accountId'] = $this->account_id;
	    $array['Site'] = $data[1];
	    $array['ListingType'] = $data[2];
	    $array['Title'] = mysql_real_escape_string($data[3]);
	    $array['SubTitle'] = $data[4];
	    $array['SKU'] = $data[5];
	    
	    $sql_0 = "select id from site where name = '".$data[1]."'";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	    $row_0 = mysql_fetch_assoc($result_0);
	    $array['PrimaryCategoryCategoryID'] = $data[6];
	    $array['PrimaryCategoryCategoryName'] = $this->getCategoryPathById($row_0['id'], $data[6]);
	    if(!empty($data[7])){
		$array['SecondaryCategoryCategoryID'] = $data[7];
		$array['SecondaryCategoryCategoryName'] = $this->getCategoryPathById($row_0['id'], $data[7]);
	    }
	    
	    if(!empty($data[8])){
		$array['StoreCategoryID'] = $data[8];
		$array['StoreCategoryName'] = $data[8];
	    }
	    
	    if(!empty($data[9])){
		$array['StoreCategory2ID'] = $data[9];
		$array['StoreCategory2Name'] = $data[9];
	    }
	    
	    $array['Quantity'] = $data[10];
	    $array['Currency'] = $data[12];
	    $array['StartPrice'] = $data[13];
	    $array['BuyItNowPrice'] = $data[14];
	    $array['ReservePrice'] = $data[15];
	    
	    $array['InternationalInsurance'] = $data[16];
	    $array['InternationalInsuranceFee'] = $data[17];
	    
	    $array['InsuranceOption'] = $data[18];
	    $array['InsuranceFee'] = $data[19];
	    
	    $array['ListingDuration'] = 'Days_'.$data[22];
	    $array['Country'] = $data[24];
	    
	    
	    $data[28] = str_replace("@@@@%", " ", $data[28]);
	    $data[28] = str_replace("%0D%0A", " ", $data[28]);
	    $array['Description'] = $data[28];
	    //$array['url'] = $data[30];//
	    $array['BoldTitle'] = $data[31];
	    $array['Featured'] = $data[32];
	    switch($data[33]){
		case "Featured":
		    $array['GalleryTypeFeatured'] = 1;
		break;
	    
		case "Gallery":
		    $array['GalleryTypeGallery'] = 1;
		break;
	    
		case "Plus":
		    $array['GalleryTypePlus'] = 1;
		break;
	    }
	    
	    $array['Highlight'] = $data[35];
	    $array['Border'] = $data[36];
	    $array['HomePageFeatured'] = $data[37];
	    $array['Location'] = $data[51];
	    $array['PayPalEmailAddress'] = $data[54];
	    $array['ShippingType'] = $data[73];
	    $array['DispatchTimeMax'] = $data[115];
	    
	    
	    if(!empty($data[196])){
		$array['ReturnPolicyRefundOption'] = $data[198];
		$array['ReturnPolicyReturnsAcceptedOption'] = $data[196];
		$array['ReturnPolicyReturnsWithinOption'] = $data[197];
		$array['ReturnPolicyShippingCostPaidByOption'] = $data[199];
		//$array['ReturnPolicyDescription'] = $data[115];
	    }
	    
	    $fields = "";
	    $values = "";
	    
	    foreach($array as $id=>$name){
		$fields .= $id.",";
		$values .= "'".mysql_real_escape_string($name)."',";
	    }
	    $fields = substr($fields, 0, -1);
	    $values = substr($values, 0, -1);
	    $sql = "insert into template ($fields) values ($values)";
	    //$this->saveFetchData("test.sql", $sql);
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    $template_id = mysql_insert_id(eBayListing::$database_connect);
	    //var_dump($result);
	    //exit;
	    //template picture url
	    $array['template_picture_url']['url'] = $data[30];
	    $sql_0 = "insert into template_picture_url (templateId,url) 
	    values ('".$template_id."','".$array['template_picture_url']['url']."')";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
		    
	    
	    //template shipping service options
	    $array['template_shipping_service_options'][1]['FreeShipping'] = $data[92];
	    $array['template_shipping_service_options'][1]['ShippingService'] = $data[88];
	    $array['template_shipping_service_options'][1]['ShippingServiceCost'] = $data[89];
	    $array['template_shipping_service_options'][1]['ShippingServiceAdditionalCost'] = $data[90];
	    $array['template_shipping_service_options'][1]['ShippingServicePriority'] = $data[91];
	    
	    $array['template_shipping_service_options'][2]['ShippingService'] = $data[94];
	    $array['template_shipping_service_options'][2]['ShippingServiceCost'] = $data[95];
	    $array['template_shipping_service_options'][2]['ShippingServiceAdditionalCost'] = $data[96];
	    $array['template_shipping_service_options'][2]['ShippingServicePriority'] = $data[97];
	    
	    $array['template_shipping_service_options'][3]['ShippingService'] = $data[99];
	    $array['template_shipping_service_options'][3]['ShippingServiceCost'] = $data[100];
	    $array['template_shipping_service_options'][3]['ShippingServiceAdditionalCost'] = $data[101];
	    $array['template_shipping_service_options'][3]['ShippingServicePriority'] = $data[102];
	    
	    foreach($array['template_shipping_service_options'] as $t){
		if(!empty($t['ShippingService'])){
		    $sql_1 = "insert into template_shipping_service_options (templateId,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) 
		    values ('".$template_id."','".$t['FreeShipping']."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."')";
		    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		}
	    }
	    //template international shipping service option
	    $array['template_international_shipping_service_option'][1]['ShippingService'] = $data[116];
	    $array['template_international_shipping_service_option'][1]['ShippingServiceCost'] = $data[117];
	    $array['template_international_shipping_service_option'][1]['ShippingServiceAdditionalCost'] = $data[118];
	    $array['template_international_shipping_service_option'][1]['ShipToLocation'] = str_replace('|', ',' ,$data[119]);
	    $array['template_international_shipping_service_option'][1]['ShippingServicePriority'] = $data[120];
	    
	    $array['template_international_shipping_service_option'][2]['ShippingService'] = $data[121];
	    $array['template_international_shipping_service_option'][2]['ShippingServiceCost'] = $data[122];
	    $array['template_international_shipping_service_option'][2]['ShippingServiceAdditionalCost'] = $data[123];
	    $array['template_international_shipping_service_option'][2]['ShipToLocation'] = str_replace('|', ',' ,$data[124]);
	    $array['template_international_shipping_service_option'][2]['ShippingServicePriority'] = $data[125];
	    
	    $array['template_international_shipping_service_option'][3]['ShippingService'] = $data[126];
	    $array['template_international_shipping_service_option'][3]['ShippingServiceCost'] = $data[127];
	    $array['template_international_shipping_service_option'][3]['ShippingServiceAdditionalCost'] = $data[128];
	    $array['template_international_shipping_service_option'][3]['ShipToLocation'] = str_replace('|', ',' ,$data[129]);
	    $array['template_international_shipping_service_option'][3]['ShippingServicePriority'] = $data[130];
	   
	    foreach($array['template_international_shipping_service_option'] as $t){
		if(!empty($t['ShippingService'])){
		    $sql_2 = "insert into template_international_shipping_service_option (templateId,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) 
		    values ('".$template_id."','".$t['ShippingService']."','".$t['ShippingServiceCost']."','".$t['ShippingServiceAdditionalCost']."','".$t['ShippingServicePriority']."','".$t['ShipToLocation']."')";
		    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
		}
	    }
	    
	    if(!empty($_POST['template_category_id'])){
		$sql_3 = "insert into template_to_template_cateogry (template_id,template_category_id) values ('".$template_id."','".$_POST['template_category_id']."')";
		$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    }
	    //$array['SKU1'] = $data[194];
	    
	    //print_r($array);
	    //$this->saveFetchData("test.html", print_r($array));
	    $i++;
	}
	
	if($result){
	    echo '{success: true, msg: "Import Turbo Lister Template Success, template id is '.$template_id.'"}';
	}else{
	    echo '{success: false, msg: ""}';
	}
    }
    
    private function getAllSubTemplateCategory($parent_id){
	$allTemplateSubCategoryId = array();
	$allTemplateSubCategoryId[] = $parent_id;
	
	$sql = "select id from template_category where parent_id = '".$parent_id."'";
	//echo $sql."<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
	while($row = mysql_fetch_assoc($result)){
	    if(!empty($row['id'])){
		$allTemplateSubCategoryId[] = $row['id'];
		
		$sql_1 = "select id from template_category where parent_id = '".$row['id']."'";
		//echo $sql_1."<br>";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		while($row_1 = mysql_fetch_assoc($result_1)){
		    if(!empty($row_1['id'])){
			$allTemplateSubCategoryId[] = $row_1['id'];
			
			$sql_2 = "select id from template_category where parent_id = '".$row_1['id']."'";
			//echo $sql_2."<br>";
			$result_2 = mysql_query($sql_2, eBayListing::$database_connect);
			while($row_2 = mysql_fetch_assoc($result_1)){
			    $allTemplateSubCategoryId[] = $row_2['id'];
			}
		    }
		}
	    }
	}
	
	return $allTemplateSubCategoryId;
    }
    
    public function exportTemplateToExcel(){
	set_time_limit(600);
	require_once './Classes/PHPExcel.php';
	require_once './Classes/PHPExcel/IOFactory.php';

	$objExcel = new PHPExcel();
	
	$objExcel->setActiveSheetIndex(0);
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'SKU');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Title');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Description');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Price');
	$objExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Image');
	
	$where = "";
	if(!empty($_GET['SKU'])){
	    $where .= " and SKU like '%".$_GET['SKU']."%'";
	}
	
	if(!empty($_GET['Title'])){
	    $where .= " and Title like '%".$_GET['Title']."%'";
	}
	
	if(!empty($_GET['ListingType'])){
	    $where .= " and ListingType ='".$_GET['ListingType']."'";
	}
	
	if(!empty($_GET['ListingDuration'])){
	    $where .= " and ListingDuration ='".$_GET['ListingDuration']."'";
	}
	
	if(!empty($_GET['TemplateCategory'])){
	    $category_id = $this->getAllSubTemplateCategory($_GET['TemplateCategory']);
	    //print_r($category_id);
	    //exit;
	    
	    $template_id = "";
	    $sql = "select template_id from template_to_template_cateogry where template_category_id in (".implode(",", $category_id).")";
	    //echo $sql."<br>";
	    $result = mysql_query($sql, eBayListing::$database_connect);
	    while($row = mysql_fetch_assoc($result)){
		$template_id .= $row['template_id'].",";
	    }
	    $where .= "and Id in (".substr($template_id, 0, -1).")";
	}
	
	$sql = "select SKU,Title,Description,ListingType,BuyItNowPrice,StartPrice from template where accountId = '".$this->account_id."'" . $where;
	//echo $sql."<br>";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$i = 2;
	while($row = mysql_fetch_assoc($result)){
	    $j = 0;
	    if($row['ListingType'] != "FixedPriceItem" && $row['ListingType'] != "StoresFixedPrice"){
		$row['StartPrice'] = $row['BuyItNowPrice'];
	    }
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['SKU']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['Title']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, html_entity_decode($row['Description'], ENT_QUOTES));
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $row['StartPrice']);
	    $objExcel->getActiveSheet()->setCellValueByColumnAndRow($j++, $i, $this->getSkuPicture($row['SKU']));
	    //$data .= '"'.$row['SKU'].'","'.$row['Title'].'","'.$row['StartPrice'].'"'."\n";
	    $i++;
	}
	
	$outputFileName = "output.xls";
	header("Content-Type: application/force-download");     
	header("Content-Type: application/octet-stream");     
	header("Content-Type: application/download");     
	header('Content-Disposition:inline;filename="'.$outputFileName.'"');     
	header("Content-Transfer-Encoding: binary");     
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");     
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");     
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");     
	header("Pragma: no-cache");     
    
	$writer = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
	$writer->save('php://output');	//echo $data;
	/*
	header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=template.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $data;*/
    }
    
    //--------  Standard Style Template ----------------------------------------------------
    public function getStandardStyleTemplate(){
	$sql = "select id,name from standard_style_template where accountId = '".$this->account_id."' group by name";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['id'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function saveStandardStyleTemplate(){
	if($_POST['standard_style_template_id'] == 0){
            $sql = "insert into standard_style_template (name,content,accountId) values ('".$_POST['standard_style_template_name']."','".htmlentities($_POST['content'], ENT_QUOTES)."','".$this->account_id."')";
            $result = mysql_query($sql, eBayListing::$database_connect);
        }else{
            $sql = "update standard_style_template set content='".htmlentities($_POST['content'], ENT_QUOTES)."' where id='".$_POST['standard_style_template_id']."' and accountId='".$this->account_id."'";
            $result = mysql_query($sql, eBayListing::$database_connect);
        }
    }
    
    //--------  Schedule Template ----------------------------------------------------------
    public function getScheduleTemplate(){
	$sql = "select name from schedule_template where account_id = '".$this->account_id."' group by name";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['name'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function deleteScheduleTemplate(){
	$sql_0 = "select Id from template where scheduleTemplateName = '".$_POST['name']."' and accountId = '".$this->account_id."'";
	$result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	while($row_0 = mysql_fetch_assoc($result_0)){
	    $template_id .= $row_0['Id'].", ";
	}
	$template_id = substr($template_id, 0, -2);
	    
	$sql_1 = "delete from schedule_template where name = '".$_POST['name']."' and account_id = '".$this->account_id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	if($result_1){
	    echo "[{success: true, msg: 'delete schedule template success, but ".$template_id." those templates use this schedule template.'}]";
	}else{
	    echo "[{success: false, msg: 'delete failure!'}]";
	}
    }
    
    public function loadScheduleTemplate(){
	session_start();
	
	$sql_7 = "select * from schedule_template where name = '".$_GET['name']."' and account_id = '".$this->account_id."'";
	$result_7 = mysql_query($sql_7, eBayListing::$database_connect);
	
	unset($_SESSION['Schedule']);
	$temp = array();
	while($row_7 = mysql_fetch_assoc($result_7)){
	    if(array_key_exists($_GET['name'].'-'.$row_7['day'].'-'.date('a-g', strtotime($row_7['time'])), $_SESSION['Schedule'])){
		$_SESSION['Schedule'][$_GET['name'].'-'.$row_7['day'].'-'.date('a-g', strtotime($row_7['time']))][count($_SESSION['Schedule'][$_GET['name'].'-'.$row_7['day'].'-'.date('a-g', strtotime($row_7['time']))])] = date('g:i A', strtotime($row_7['time']));
	    }else{
		$_SESSION['Schedule'][$_GET['name'].'-'.$row_7['day'].'-'.date('a-g', strtotime($row_7['time']))][0] = date('g:i A', strtotime($row_7['time']));
	    }
	    $t = date("D-a-g", strtotime($row_7['day'] . " " . $row_7['time']))."-panel";
	    if(!in_array($t, $temp)){
		$temp[] = $t;
	    }
	}
	//$row['Schedule'] = implode(",", $temp);
	echo '['.json_encode(array('Schedule'=>implode(",", $temp))).']';
	mysql_free_result($result);
    }
    
    public function saveScheduleTemplate(){
	session_start();
	print_r($_SESSION['Schedule']);
	$sql_1 = "select count(*) as num from schedule_template where name = '".$_POST['name']."' and account_id = '".$this->account_id."'";
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$row_1 = mysql_fetch_assoc($result_1);
	if($row_1['num'] > 0){
	    if(!empty($_SESSION['Schedule'])){
		$sql_1 = "delete from schedule_template where name = '".$_POST['name']."' and account_id = '".$this->account_id."'";
		$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
		
		foreach($_SESSION['Schedule'] as $key=>$value){
		    $keyArray = explode("-", $key);
		    foreach($value as $name){
			$day = date("D", strtotime($keyArray[1]." ".$name));
			$time = date("H:i:s", strtotime($name));
			/*
			$sql_1 = "select id from schedule_template where 
			name = '".$_POST['name']."' and day = '".$day."' and time = '".$time."' and account_id = '".$this->account_id."'";
			$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
			$row_1 = mysql_fetch_assoc($result_1);
			if(empty($row_1['id'])){
			*/
			    $sql_3 = "insert into schedule_template (name,day,time,account_id) values 
			    ('".$_POST['name']."','".$day."','".$time."','".$this->account_id."')";
			    //echo $sql_3;
			    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
			//}
		    }
		}
	    }
	}else{
	    if(!empty($_SESSION['Schedule'])){
		
		foreach($_SESSION['Schedule'] as $key=>$value){
		    $keyArray = explode("-", $key);
		    foreach($value as $name){
			$day = date("D", strtotime($keyArray[1]." ".$name));
			$time = date("H:i:s", strtotime($name));
			$sql_3 = "insert into schedule_template (name,day,time,account_id) values 
			('".$_POST['name']."','".$day."','".$time."','".$this->account_id."')";
			//echo $sql_3;
			$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
		    }
		}
	    }
	}
    }
    
    public function addTemplateScheduleTime(){
	$id = (!empty($_POST['template_id']))?$_POST['template_id']:$_POST['sku'];
	
	if(!empty($_POST['time'])){
	    session_start();
	    if(@!is_array($_SESSION['Schedule'][$id.'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$id.'-'.$_POST['dayTime']] = array();
	    }
	    if(@!in_array($_POST['time'], $_SESSION['Schedule'][$id.'-'.$_POST['dayTime']])){
		$_SESSION['Schedule'][$id.'-'.$_POST['dayTime']][] = $_POST['time'];
	    }
	}
	print_r($_SESSION['Schedule'][$id.'-'.$_POST['dayTime']]);
    }
    
    public function deleteTemplateScheduleTime(){
	session_start();
	$id_array = explode(",", $_POST['id']);
	print_r($id_array);
	foreach($id_array as $id){
	    unset($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']][$id]);
	}
	/*
	$i = 0;
	foreach($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']] as $s){
	    $_SESSION[$_POST['sku'].'-'.$_POST['dayTime']][$i] = $s;
	    $i++;
	}
	*/
	//sort($_SESSION[$_POST['sku'].'-'.$_POST['dayTime']]);
	print_r($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']]);
    }
    
    public function deleteAllTemplateScheduleTime(){
	session_start();
	unset($_SESSION['Schedule'][$_POST['template_id'].'-'.$_POST['dayTime']]);
    }
    
    public function getTemplateScheduleTime(){
	session_start();
	//print_r($_SESSION[$_GET['sku'].'-'.$_GET['dayTime']]);
	//$array = array(array("time"=>"13:21"), array("time"=>"13:30"));
	if(@is_array($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']])){
	    sort($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']]);
	    $data = array();
	    $i = 0;
	    foreach($_SESSION['Schedule'][$_GET['template_id'].'-'.$_GET['dayTime']] as $s){
		$data[$i]['time'] = $s;
		$i++;
	    }
	    echo json_encode($data);
	}else{
	    echo json_encode(array());
	}
	//print_r($_SESSION['Schedule']);
    }
    
    public function updateTemplateScheduleTime(){
	
    }
    
    //---------  Shipping Template -------------------------------------------------------
    public function getShippingTemplate(){
	$sql = "select name from shipping_template where account_id = '".$this->account_id."' group by name";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$array = array();
	$i = 0;
	while($row = mysql_fetch_assoc($result)){
	    $array[$i]['id'] = $row['name'];
	    $array[$i]['name'] = $row['name'];
	    $i++;
	}
	echo json_encode($array);
	mysql_free_result($result);
    }
    
    public function loadShippingTemplate(){
	session_start();
	
	$sql = "select * from shipping_template where name = '".$_GET['name']."' and Site = '".$_GET['Site']."' and account_id = '".$this->account_id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	
	$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyReturnsAcceptedOption'] = $row['ReturnPolicyReturnsAcceptedOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyReturnsWithinOption'] = $row['ReturnPolicyReturnsWithinOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyRefundOption'] = $row['ReturnPolicyRefundOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyShippingCostPaidByOption'] = $row['ReturnPolicyShippingCostPaidByOption'];
	$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyDescription'] = $row['ReturnPolicyDescription'];
	
	$sql_3 = "select * from s_template where template_id = '".$row['id']."' order by ShippingServicePriority";
	$result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	$i = 1;
	while($row_3 = mysql_fetch_assoc($result_3)){
	    $row['ShippingService_'.$i] = $row_3['ShippingService'];
	    $row['ShippingServiceCost_'.$i] = $row_3['ShippingServiceCost'];
	    $row['ShippingServiceAdditionalCost_'.$i] = $row_3['ShippingServiceAdditionalCost'];
	    $row['ShippingServiceFree_'.$i] = $row_3['FreeShipping'];
	    $i++;
	}
	
	$sql_4 = "select * from i_s_template where template_id = '".$row['id']."' order by ShippingServicePriority";
	$result_4 = mysql_query($sql_4, eBayListing::$database_connect);
	$i = 1;
	while($row_4 = mysql_fetch_assoc($result_4)){
	    $row['InternationalShippingService_'.$i] = $row_4['ShippingService'];
	    $row['InternationalShippingServiceCost_'.$i] = $row_4['ShippingServiceCost'];
	    $row['InternationalShippingServiceAdditionalCost_'.$i] = $row_4['ShippingServiceAdditionalCost'];
	    $array = explode(",", $row_4['ShipToLocation']);
	    if(count($array) > 1){
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		foreach($array as $v){
		    $row[$v.'_'.$i] = 1;
		}
	    }elseif($row_4['ShipToLocation'] == "Worldwide"){
		$row['InternationalShippingToLocations_'.$i] = "Worldwide";
	    }else{
		$row['InternationalShippingToLocations_'.$i] = "Custom Locations";
		$row[$row_4['ShipToLocation'].'_'.$i] = 1;
	    }
	    $i++;
	}
	
	echo '['.json_encode($row).']';
	mysql_free_result($result);
    }
    
    public function saveShippingTemplate(){
	session_start();
	$sql_0 = "delete from shipping_template where name = '".$_GET['name']."' and account_id = '".$this->account_id."'";
	$result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	
	$sql_1 = "insert into shipping_template (name,Site,Location,PostalCode,
	ReturnPolicyDescription,ReturnPolicyRefundOption,ReturnPolicyReturnsAcceptedOption,ReturnPolicyReturnsWithinOption,ReturnPolicyShippingCostPaidByOption,
	DispatchTimeMax,ShippingServiceOptionsType,InsuranceOption,InsuranceFee,InternationalShippingServiceOptionType,InternationalInsurance,InternationalInsuranceFee,account_id) values 
	('".$_GET['name']."','".$_GET['Site']."','".$_POST['Location']."','".$_POST['PostalCode']."',
	'".$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyDescription']."','".$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyRefundOption']."',
	'".$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyReturnsAcceptedOption']."','".$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyReturnsWithinOption']."',
	'".$_SESSION['ReturnPolicyReturns'][$_GET['name']]['ReturnPolicyShippingCostPaidByOption']."','".$_POST['DispatchTimeMax']."','".$_POST['ShippingServiceOptionsType']."','".$_POST['InsuranceOption']."','".$_POST['InsuranceFee']."','".$_POST['InternationalShippingServiceOptionType']."','".$_POST['InternationalInsurance']."','".$_POST['InternationalInsuranceFee']."','".$this->account_id."')";
	//echo $sql_1;
	$result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	$id = mysql_insert_id(eBayListing::$database_connect);
	
	
	$i = 1;
	while(!empty($_POST['ShippingService_'.$i])){
	    $sql_1 = "insert into s_template (template_id,FreeShipping,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority) values
	    ('".$id."','".@$_POST['ShippingServiceFree_'.$i]."','".$_POST['ShippingService_'.$i]."','".$_POST['ShippingServiceCost_'.$i]."','".$_POST['ShippingServiceAdditionalCost_'.$i]."','".$i."')";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    $i++;
	}
	
	
	$i = 1;
	while(!empty($_POST['InternationalShippingService_'.$i])){
	    $ShipToLocation = '';
	    if($_POST['InternationalShippingToLocations_'.$i] == 'Custom Locations'){
		if(!empty($_POST['Americas_'.$i]) && $_POST['Americas_'.$i] == 1){
		    $ShipToLocation .= ',Americas';
		}
		
		if(!empty($_POST['US_'.$i]) && $_POST['US_'.$i] == 1){
		    $ShipToLocation .= ',US';
		}
		
		if(!empty($_POST['Europe_'.$i]) && $_POST['Europe_'.$i] == 1){
		    $ShipToLocation .= ',Europe';
		}
		
		if(!empty($_POST['Asia_'.$i]) && $_POST['Asia_'.$i] == 1){
		    $ShipToLocation .= ',Asia';
		}
		
		if(!empty($_POST['CA_'.$i]) && $_POST['CA_'.$i] == 1){
		    $ShipToLocation .= ',CA';
		}
		
		if(!empty($_POST['GB_'.$i]) && $_POST['GB_'.$i] == 1){
		    $ShipToLocation .= ',GB';
		}
		
		if(!empty($_POST['AU_'.$i]) && $_POST['AU_'.$i] == 1){
		    $ShipToLocation .= ',AU';
		}
		
		if(!empty($_POST['MX_'.$i]) && $_POST['MX_'.$i] == 1){
		    $ShipToLocation .= ',MX';
		}
		
		if(!empty($_POST['DE_'.$i]) && $_POST['DE_'.$i] == 1){
		    $ShipToLocation .= ',DE';
		}
		
		if(!empty($_POST['JP_'.$i]) && $_POST['JP_'.$i] == 1){
		    $ShipToLocation .= ',JP';
		}
		
		$ShipToLocation = substr($ShipToLocation, 1);
	    }else{
		$ShipToLocation = 'Worldwide';
	    }
	    $sql_2 = "insert into i_s_template (template_id,ShippingService,ShippingServiceCost,ShippingServiceAdditionalCost,ShippingServicePriority,ShipToLocation) values
	    ('".$id."','".$_POST['InternationalShippingService_'.$i]."','".$_POST['InternationalShippingServiceCost_'.$i]."','".$_POST['InternationalShippingServiceAdditionalCost_'.$i]."','".$i."','".$ShipToLocation."')";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    $i++;
	}
	echo '{success: true, msg: "Save Shipping Template Success!"}';
    }
    
    public function deleteShippingTemplate(){
	$sql = "select id from shipping_template where name = '".$_POST['name']."' and Site = '".$_POST['site']."' and account_id = '".$this->account_id."'";
	$result = mysql_query($sql, eBayListing::$database_connect);
	$row = mysql_fetch_assoc($result);
	$id = $row['id'];
	
	if(!empty($id)){
	    $template_id = "";
	    
	    $sql_0 = "select Id from template where Site = '".$_POST['site']."' and shippingTemplateName = '".$_POST['name']."' and accountId = '".$this->account_id."'";
	    $result_0 = mysql_query($sql_0, eBayListing::$database_connect);
	    while($row_0 = mysql_fetch_assoc($result_0)){
		$template_id .= $row_0['Id'].", ";
	    }
	    $template_id = substr($template_id, 0, -2);
	    
	    $sql_1 = "delete from shipping_template where id = '".$id."'";
	    $result_1 = mysql_query($sql_1, eBayListing::$database_connect);
	    //echo $sql_1."\n";
	    
	    $sql_2 = "delete from s_template where template_id = '".$id."'";
	    $result_2 = mysql_query($sql_2, eBayListing::$database_connect);
	    //echo $sql_2."\n";
	    
	    $sql_3 = "delete from i_s_template where template_id = '".$id."'";
	    $result_3 = mysql_query($sql_3, eBayListing::$database_connect);
	    //echo $sql_3."\n";
	    
	    if($result_1 && $result_2 && $result_3){
		echo "[{success: true, msg: 'delete shipping template success, but ".$template_id." those templates use this shipping template.'}]";
	    }else{
		echo "[{success: false, msg: 'delete failure!'}]";
	    }
	}else{
	    echo "[{success: false, msg: 'delete failure!'}]";
	}
    }
}
?>