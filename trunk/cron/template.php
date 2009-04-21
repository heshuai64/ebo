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
        <div style="width:800px;text-align:center;margin:auto;">
                <table align="center" cellpadding="1" cellspacing="1" border="1" width="100%">
                        <tr align="center" class="header">
                                <th>No</th><th>Address</th><th>Barcode</th><th>Sku</th><th>Images</th>
                        </tr>
                        <?php
                        $i = 1;
                        foreach($this->shipment as $shipment){
                                echo '<tr>';
                                        echo '<td>';
                                                echo $i;
                                        echo '</td>';
                                        echo '<td>';
                                                echo $shipment['shipToName'].'<br>'.
                                                $shipment['shipToAddressLine1'].'<br>'.
                                                $shipment['shipToAddressLine2'].'<br>'.
                                                $shipment['shipToCity'].'<br>'.
                                                $shipment['shipToStateOrProvince'].'<br>'.
                                                $shipment['shipToPostalCode'].'<br>'.
                                                $shipment['shipToCountry'].'<br>';
                                        echo '</td>';
                                        echo '<td>';
                                                echo '<img src="'.PackingList::BAR_CODE_URL.'?code=code39&o=1&t=30&r=1&text='.$shipment['id'].'&f1=Arial.ttf&f2=8&a1=&a2=&a3=">';
                                        echo '</td>';
                                        echo '<td>';
                                                foreach($shipment['shipmentDetail'] as $shipmentDetail){
                                                        echo $shipmentDetail['skuId'].' X '.$shipmentDetail['quantity'].'<br>';
                                                }
                                        echo '</td>';
                                         echo '<td>';
                                                //var_dump($shipment['shipmentDetail']);
                                                if(count($shipment['shipmentDetail']) == 1){
                                                        echo '<img width="300" height="200" src="'.$shipmentDetail['image'].'"/><br>';
                                                }else{
                                                        $height = round(200 / count($shipment['shipmentDetail']));
                                                        $width = round(300 / count($shipment['shipmentDetail']));
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