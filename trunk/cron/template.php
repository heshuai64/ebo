<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title>Packing List</title>
        <style>
                .address{
                        font-size:20px;
                        line-height:120%;
                        padding-left:6px;
                        padding-right:0px;
                        position:relative;
                        width:500px;
                }
                
                .sku{
                        padding:0px;
                }
                
                .barcode{
                        position:absolute;
                        padding:0px;
                        right:10px;
                        bottom:0px;
                }
                
                .shipment-id{
                        font-size:14px;
                        position:absolute;
                        padding:0px;
                        right:10px;
                        bottom:32px;
                }
        </style>
</head>
<body>
        <!--style="width:900px;margin:auto;"-->
        <div style="width:1000px;margin:auto;"> 
                <table align="center" cellpadding="0" cellspacing="0" border="1" width="100%">
                        <!--
                        <tr align="center" class="header">
                                <th>No</th><th>Address</th><th>Sku</th><th>Images</th><th>Shipping</th>
                        </tr>
                        -->
                        <?php
                        $i = 1;
                        for($i=0; $i< count($this->shipment); $i++){
                        //foreach($this->shipment as $shipment){
                                echo '<tr>';
                                        /*
                                        echo '<td>';
                                                echo $i;
                                        echo '</td>';
                                        
                                        echo '<td>';
                                                //image.php?code=code128&o=1&t=30&r=2&text='.$shipment['id'].'&f1=Arial.ttf&f2=8&a1=&a2=B&a3=
                                                //image.php?code=code39&o=1&t=30&r=2&text=SHI20090500221&f1=Arial.ttf&f2=8&a1=&a2=&a3=
                                                echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code39&o=1&t=30&r=1&text='.$this->shipment[$i]['id'].'&f1=Arial.ttf&f2=8&a1=&a2=&a3=">';
                                        echo '</td>';
                                        */
                                        echo '<td><div class="address"><div class="shipment-id">'.$this->shipment[$i]['id'].'(<font color="red">'.$this->shipment[$i]['envelope'].'</font>)</div>';
                                                echo "Attn: ".$this->shipment[$i]['shipToName']."(".$this->shipment[$i]['buyerId'].")<br>".
                                                $this->shipment[$i]['shipToAddressLine1']." ".(!empty($this->shipment[$i]['shipToAddressLine2'])?$this->shipment[$i]['shipToAddressLine2'].'<br>':'<br>').
                                                $this->shipment[$i]['shipToCity']. '<br>'.
                                                $this->shipment[$i]['shipToStateOrProvince']. ", ". $this->shipment[$i]['shipToPostalCode'].'<br>'.
                                                $this->shipment[$i]['shipToCountry'].'<br>'.
                                                ((!empty($this->shipment[$i]['shipToPhoneNo']) && $this->shipment[$i]['shipToPhoneNo'] != "Invalid Request")?"Tel:".$this->shipment[$i]['shipToPhoneNo'].'<br>':'<br>');
                                                echo '<div class="barcode">';
                                                        echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code39&o=1&t=30&r=1&text='.$this->shipment[$i]['id'].'&f1=-1&f2=8&a1=&a2=&a3=">';
                                                echo '</div>
                                                </div></td>';
                                        $i++;
                                        
                                        if($i < count($this->shipment)){
                                                echo '<td><div class="address"><div class="shipment-id">'.$this->shipment[$i]['id'].'(<font color="red">'.$this->shipment[$i]['envelope'].'</font>)</div>';
                                                echo "Attn: ".$this->shipment[$i]['shipToName']."(".$this->shipment[$i]['buyerId'].")<br>".
                                                $this->shipment[$i]['shipToAddressLine1']." ".(!empty($this->shipment[$i]['shipToAddressLine2'])?$this->shipment[$i]['shipToAddressLine2'].'<br>':'<br>').
                                                $this->shipment[$i]['shipToCity']. '<br>'.
                                                $this->shipment[$i]['shipToStateOrProvince']. ", ". $this->shipment[$i]['shipToPostalCode'].'<br>'.
                                                $this->shipment[$i]['shipToCountry'].'<br>'.
                                                ((!empty($this->shipment[$i]['shipToPhoneNo']) && $this->shipment[$i]['shipToPhoneNo'] != "Invalid Request")?"Tel:".$this->shipment[$i]['shipToPhoneNo'].'<br>':'<br>');
                                                echo '<div class="barcode">';                       //image.php?code=code39&o=1&t=30&r=1&text=SHM200906A0013&f1=Arial.ttf&f2=8&a1=&a2=&a3=
                                                        echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code39&o=1&t=30&r=1&text='.$this->shipment[$i]['id'].'&f1=-1&f2=8&a1=&a2=&a3=">';
                                                echo '</div>
                                                </div></td>';    
                                        }
                                        /*
                                        echo '<td><div class="sku">';
                                                foreach($this->shipment[$i]['shipmentDetail'] as $shipmentDetail){
                                                        echo $shipmentDetail['skuId'].' X '.$shipmentDetail['quantity'].'<br>';
                                                }
                                        echo '</div></td>';
                                        
                                        
                                        
                                        if($i < count($this->shipment)){
                                                //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                                                echo '<td>';
                                                        echo $i;
                                                echo '</td>';
                                                echo '<td>';
                                                        //image.php?code=code128&o=1&t=30&r=2&text='.$shipment['id'].'&f1=Arial.ttf&f2=8&a1=&a2=B&a3=
                                                        echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code128&o=1&t=30&r=1&text='.$this->shipment[$i]['id'].'&f1=Arial.ttf&f2=8&a1=&a2=B&a3=">';
                                                echo '</td>';
                                                echo '<td style="padding-left:10px"><font size="4">';
                                                        echo "Attn: ".$this->shipment[$i]['shipToName'].'<br>'.
                                                        $this->shipment[$i]['shipToAddressLine1']." ".(!empty($this->shipment[$i]['shipToAddressLine2'])?$this->shipment[$i]['shipToAddressLine2'].'<br>':'').
                                                        $this->shipment[$i]['shipToCity']. '<br>'.
                                                        $this->shipment[$i]['shipToStateOrProvince']. ", ". $this->shipment[$i]['shipToPostalCode'].'<br>'.
                                                        $this->shipment[$i]['shipToCountry'].'<br>';
                                                echo '</font></td>';
                                                echo '<td width="10">';
                                                        foreach($this->shipment[$i]['shipmentDetail'] as $shipmentDetail){
                                                                echo $shipmentDetail['skuId'].' X '.$shipmentDetail['quantity'].'<br>';
                                                        }
                                                echo '</td>';
                                                
                                                $i++;
                                        }
                                        */
                                        
                                        /*
                                        echo '<td>';
                                                //var_dump($shipment['shipmentDetail']);
                                                if(count($this->shipment[$i]['shipmentDetail']) == 1){
                                                        echo '<img width="150" height="100" src="'.$shipmentDetail['image'].'"/><br>';
                                                }else{
                                                        $height = round(100 / count($this->shipment[$i]['shipmentDetail']));
                                                        $width = round(150 / count($this->shipment[$i]['shipmentDetail']));
                                                        foreach($this->shipment[$i]['shipmentDetail'] as $shipmentDetail){
                                                                echo '<img width="'.$width.'" height="'.$height.'" src="'.$shipmentDetail['image'].'"/>';
                                                        }
                                                }
                                        echo '</td>';
                                        
                                        echo '<td>';
                                                echo $this->shipment[$i]['shippingMethod'];
                                        echo '</td>';
                                        */
                                echo '</tr>';
                                
                                //$i++;
                        }
                        ?>
                </table>
        </div>
</body>
</html>