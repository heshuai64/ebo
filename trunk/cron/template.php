<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="PRAGMA" content="NO-CACHE">
        <meta http-equiv="CACHE-CONTROL" content="NO-CACHE">
        <meta http-equiv="EXPIRES" content="-1">
        
        <title>Packing List</title>
        
</head>
<body>
        <div style="width:900px;margin:auto;">
                <table align="center" cellpadding="0" cellspacing="0" border="1" width="100%">
                        <tr align="center" class="header">
                                <th>No</th><th>Shipment Id</th><th>Address</th><th>Sku</th><th>Images</th>
                        </tr>
                        <?php
                        $i = 1;
                        foreach($this->shipment as $shipment){
                                echo '<tr>';
                                        echo '<td>';
                                                echo $i;
                                        echo '</td>';
                                        echo '<td>';
                                                //image.php?code=code128&o=1&t=30&r=2&text='.$shipment['id'].'&f1=Arial.ttf&f2=8&a1=&a2=B&a3=
                                                echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code128&o=1&t=30&r=1&text='.$shipment['id'].'&f1=Arial.ttf&f2=8&a1=&a2=B&a3=">';
                                        echo '</td>';
                                        echo '<td><font size="4">';
                                                echo $shipment['shipToName'].'<br>'.
                                                $shipment['shipToAddressLine1'].'<br>'.
                                                (!empty($shipment['shipToAddressLine2'])?$shipment['shipToAddressLine2'].'<br>':'').
                                                $shipment['shipToCity'].'<br>'.
                                                $shipment['shipToStateOrProvince'].'<br>'.
                                                $shipment['shipToPostalCode'].'<br>'.
                                                $shipment['shipToCountry'].'<br>';
                                        echo '</font></td>';
                                        echo '<td>';
                                                foreach($shipment['shipmentDetail'] as $shipmentDetail){
                                                        echo $shipmentDetail['skuId'].' X '.$shipmentDetail['quantity'].'<br>';
                                                }
                                        echo '</td>';
                                         echo '<td>';
                                                //var_dump($shipment['shipmentDetail']);
                                                if(count($shipment['shipmentDetail']) == 1){
                                                        echo '<img width="150" height="100" src="'.$shipmentDetail['image'].'"/><br>';
                                                }else{
                                                        $height = round(100 / count($shipment['shipmentDetail']));
                                                        $width = round(150 / count($shipment['shipmentDetail']));
                                                        foreach($shipment['shipmentDetail'] as $shipmentDetail){
                                                                echo '<img width="'.$width.'" height="'.$height.'" src="'.$shipmentDetail['image'].'"/>';
                                                        }
                                                }
                                        echo '</td>';
                                echo '</tr>';
                                $i++;
                        }
                        ?>
                </table>
        </div>
</body>
</html>