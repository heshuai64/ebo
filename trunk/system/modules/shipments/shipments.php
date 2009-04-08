<?php
class QoShipments {
	
	private $os;

	public function __construct($os){
		$this->os = $os;
	}
        
        public function searchShipment(){
            $where = " 1 = 1 ";
            $shipment_detail_where = "";    
            
            if(!empty($_POST['id'])){
                    $where .= " and s.id like '%".$_POST['id']."%'";
            }
            
            if(!empty($_POST['ordersId'])){
                    $where .= " and o.ordersId like '%".$_POST['ordersId']."%'";
            }
            
            if(!empty($_POST['shippingMethod'])){
                    $where .= " and s.shippingMethod = '".$_POST['shippingMethod']."'";
            }
            
            if(!empty($_POST['sellerId'])){
                    $where .= " and o.sellerId = '".$_POST['sellerId']."'";
            }
            
            if(!empty($_POST['shipToName'])){
                    $where .= " and s.shipToName like '%".$_POST['shipToName']."%'";
            }
            
            if(!empty($_POST['shipToAddressLine'])){
                    $where .= " and (s.shipToAddressLine1 like '%".$_POST['shipToAddressLine']."%' or s.shipToAddressLine2 like '%".$_POST['shipToAddressLine']."%')";
            }
                        
            if(!empty($_POST['postalReferenceNo'])){
                    $where .= " and s.postalReferenceNo like '%".$_POST['postalReferenceNo']."%'";
            }
            
            if(!empty($_POST['status'])){
                    $where .= " and s.status = '".$_POST['status']."'";
            }
            
            if(!empty($_POST['itemId'])){
                    $shipment_detail_where .= " and sd.itemId like '%".$_POST['itemId']."%'";
            }
            
            if(!empty($_POST['itemTitle'])){
                    $shipment_detail_where .= " and sd.itemTitle like '%".$_POST['itemTitle']."%'";
            }
            
            if(!empty($_POST['skuId'])){
                    $shipment_detail_where .= " and sd.skuId like '%".$_POST['skuId']."%'";
            }
            
            if(!empty($_POST['skuTitle'])){
                    $shipment_detail_where .= " and sd.skuTitle like '%".$_POST['skuTitle']."%'";
            }
            
            if(!empty($_POST['createdOnFrom'])){
                    $where .= " and s.createdOn > '".$_POST['createdOnFrom']."'";
            }
            
            if(!empty($_POST['createdOnTo'])){
                    $where .= " and s.createdOn < '".$_POST['createdOnTo']."'";
            }
            
            if(!empty($_POST['packedOnFrom'])){
                    $where .= " and s.packedOn > '".$_POST['packedOnFrom']."'";
            }
            
            if(!empty($_POST['packedOnTo'])){
                    $where .= " and s.packedOn < '".$_POST['packedOnTo']."'";
            }
            
            if(!empty($_POST['shippedOnFrom'])){
                    $where .= " and s.shippedOn < '".$_POST['shippedOnFrom']."'";
            }
            
            if(!empty($_POST['shippedOnTo'])){
                    $where .= " and s.shippedOn < '".$_POST['shippedOnTo']."'";
            }
            
            if(empty($shipment_detail_where)){
                $sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,
                s.packedOn,s.shippedOn,s.status from qo_shipments as s left join qo_orders as o on s.ordersId = o.id where ".$where;
            }else{
                $sql = "select s.id,o.id as ordersId,s.shipToName,s.shipToEmail,o.sellerId,s.createdOn,
                s.packedOn,s.shippedOn,s.status from (qo_shipments as s left join qo_shipments_detail as sd on s.id=sd.shipmentsId)
                left join qo_orders as o on s.ordersId = o.id where ".$where.$shipment_detail_where;
            }
            //echo $sql;
            $result = mysql_query($sql);
            $i = 0;
            $transaction_array = array();
            while($row = mysql_fetch_assoc($result)){
                $transaction_array[] = $row;
                $i++;
            }
            //var_dump($order_array);
            echo json_encode(array('totalCount'=>$i, 'records'=>$transaction_array));
            mysql_free_result($result);
        }
	
	function getShipmentInfo(){
		$sql = "select * from qo_shipments where id = '".$_GET['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		//var_dump($row);
		echo '['.json_encode($row).']';
		mysql_free_result($result);
	}
	
	public function getShipmentDetail(){
		$sql = "select * from qo_shipments_detail where shipmentsId ='".$_GET['id']."'";
		$result = mysql_query($sql);
		$shipment_detail_array = array();
		while($row = mysql_fetch_assoc($result)){
			$shipment_detail_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$shipment_detail_array));
		mysql_free_result($result);
	}
	
	public function addShipmentDetail(){
		$sql = "insert into qo_shipments_detail (shipmentsId,skuId,skuTitle,itemId,itemTitle,quantity) values (
		'".$_POST['shipmentsId']."','".$_POST['skuId']."','".$_POST['skuTitle']."','".$_POST['itemId']."',
		'".$_POST['itemTitle']."','".$_POST['quantity']."')";
		$result = mysql_query($sql);
		echo $result;
	}
	
	public function saveShipmentInfo(){
		
	}
	
	public function verifyShipment(){
		$sql = "select * from qo_shipments_detail where shipmentsId ='".$_POST['id']."'";
		$result = mysql_query($sql);
		$shipment_detail_array = array();
		while($row = mysql_fetch_assoc($result)){
			$shipment_detail_array[] = $row;
			$i++;
		}
		echo json_encode(array('totalCount'=>$i, 'records'=>$shipment_detail_array));
		mysql_free_result($result);
	}
	
	public function packShipment(){
		$sql = "select status from qo_shipments where id = '".$_POST['id']."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		if($row['status'] == "N"){
			$sql = "update qo_shipments set status='K',packedBy='',packedOn='".date("Y-m-d H:i:s")."' where id='".$_POST['id']."'";
			$result = mysql_query($sql);
			if($result){
				echo "{success: true,info:'\'<font color=\'green\'>Operation Successfully</font>'}"; 
			}else{
				echo "{success: false, errors: { reason: 'Saving failed. Try again.' }}";
			}
		}else{
			echo "{success: false, errors: { reason: '\'<font color=\'red\'>Can\'t Pack This Shipment.</font>'}}";
		}
	}
}

?>