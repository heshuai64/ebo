<?php
class Attributes  {
    const DATABASE_HOST = 'localhost';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = '5333533';
    const DATABASE_NAME = 'ebaylisting';
    
    private static $database_connect;
    private $parser;
    private $CharacteristicsSetId;
    private $AttributeId;
    private $AttributeValueId;
    private $AttributeValueName;
    private $step = "";
    public $array = array();
	
    public function __construct() 
    {
        Attributes::$database_connect = mysql_connect(self::DATABASE_HOST, self::DATABASE_USER, self::DATABASE_PASSWORD);

        if (!Attributes::$database_connect) {
            echo "Unable to connect to DB: " . mysql_error(Attributes::$database_connect);
            exit;
        }
	
        mysql_query("SET NAMES 'UTF8'", Attributes::$database_connect);
	
        if (!mysql_select_db(self::DATABASE_NAME, Attributes::$database_connect)) {
            echo "Unable to select mydbname: " . mysql_error(Attributes::$database_connect);
            exit;
        }
        
        $this->parser = xml_parser_create();

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
    }

    public function parse($data) 
    {
        xml_parse($this->parser, $data);
    }

    private function tag_open($parser, $tag, $attributes) 
    {
    	
    	switch($tag){
            case strtoupper("CharacteristicsSet"):
                $this->CharacteristicsSetId = $attributes['ID'];
                //var_dump($tag, $attributes);
            break;
            
            case strtoupper("PresentationInstruction"):
                //var_dump($tag, $attributes);
                $this->step = "PresentationInstruction";
            break;
            
            case strtoupper("Attribute"):
                switch ($this->step){
                    case "PresentationInstruction":
                        $this->AttributeId = $attributes['ID'];
                        //var_dump($tag, $attributes);
                        $this->step = "PresentationInstruction-Attribute";
                    break;
                    
                    case "CharacteristicsList":
                        $this->AttributeId = $attributes['ID'];
                        //var_dump($tag, $attributes);
                        $this->step = "CharacteristicsList-Attribute";
                    break;
                }
            break;
            
            case strtoupper("Input"):
                //var_dump($tag, $attributes);
                switch ($this->step){
                    case "PresentationInstruction-Attribute-Label":
                        $this->array[$this->CharacteristicsSetId][$this->AttributeId]['Type'] = $attributes['TYPE'];
                        $this->step = "PresentationInstruction";
                    break;
                }
            break;
            
            case strtoupper("CharacteristicsList"):
                //var_dump($tag, $attributes);
                $this->step = "CharacteristicsList";
            break;
            
            case strtoupper("Label"):
                switch ($this->step){
                    case "PresentationInstruction-Attribute":
                        $this->step = "PresentationInstruction-Attribute-Label";
                    break;
                    
                    case "CharacteristicsList-Attribute":
                        $this->step = "CharacteristicsList-Attribute-Label";
                    break;
                }
                //var_dump($parser, $tag, $attributes);
            break;
            
            case strtoupper("ValueList"):
                $this->step = "CharacteristicsList-Attribute-ValueList";
            break;
            
            case strtoupper("Value"):
                if($this->step == "CharacteristicsList-Attribute-ValueList"){
                    $this->AttributeValueId = $attributes['ID'];
                    //var_dump($tag, $attributes);
                    $this->step = "CharacteristicsList-Attribute-ValueList-Value";
                }
            break;
            
            case strtoupper("Name"):
                if($this->step == "CharacteristicsList-Attribute-ValueList-Value"){
                    //var_dump($tag, $attributes);
                    $this->step = "CharacteristicsList-Attribute-ValueList-Name";
                }
            break;
            
            case strtoupper("Dependency"):
                $this->step = "CharacteristicsList";
            break;
    	}
    }

    private function cdata($parser, $cdata) 
    {
    	switch ($this->step){
            case "CharacteristicsList-Attribute-Label":
                //var_dump($cdata);
                $this->array[$this->CharacteristicsSetId][$this->AttributeId]['Label'] = $cdata;
                $this->step = "CharacteristicsList-Attribute-ValueList";
            break;
            
            case "CharacteristicsList-Attribute-ValueList-Name":
                //var_dump($cdata);
                $this->array[$this->CharacteristicsSetId][$this->AttributeId]['ValueList'][$this->AttributeValueId] = $cdata;
                $this->step = "CharacteristicsList-Attribute-ValueList";
            break;
    	}
    }

    private function tag_close($parser, $tag) 
    {
    	//$this->step = "";

        //var_dump($parser, $tag);
        switch($tag){
        	case strtoupper("CharacteristicsSet"):
        		//print_r($this->array);
    			//exit();
    		break;
    		
    		case strtoupper("PresentationInstruction"):

    		break;
    		
    		case strtoupper("CharacteristicsList"):

    		break;
    		
    		case strtoupper("Attribute"):
                    switch ($this->step){
                        case "CharacteristicsList-Attribute-ValueList":
                                $this->step = "CharacteristicsList";
                        break;
                    }
    		break;
        }

    }
    
    public function dealData(){
    	//print_r($this->array);
    	//exit;
    	foreach ($this->array as $key1=>$value1){
            foreach ($value1 as $key2=>$value2){
                $sql_1 = "insert into CharacteristicsLists (CharacteristicsSetId,AttributeId,Label,Type) values ('".$key1."','".$key2."','".$value2['Label']."','".$value2['Type']."')";
                $result_1 = mysql_query($sql_1, Attributes::$database_connect);
                //echo $sql_1;
                //echo "\n";
                
                foreach ($value2['ValueList'] as $key3=>$value3){
                    $sql_2 = "insert into CharacteristicsAttributeValueLists (CharacteristicsSetId,AttributeId,id,name) values 
                    ('".$key1."','".$key2."','".$key3."','".$value3."')";
                    $result_2 = mysql_query($sql_2, Attributes::$database_connect);
                    //echo $sql_2;
                    //echo "\n";
                }
            }
        }
    }

}

$conn = mysql_connect("localhost", "root", "5333533");

if (!$conn) {
    echo "Unable to connect to DB: " . mysql_error();
    exit;
}
  
if (!mysql_select_db("ebaylisting")) {
    echo "Unable to select mydbname: " . mysql_error();
    exit;
}


if(!empty($argv[2])){
    $a = new Attributes();
    $f = fopen('GetAttributesCS-'.$argv[2].'.xml', 'r' );
    while( $data = fread($f, 4096)){
        $a->parse($data);
    }
    $a->dealData();
}else{
    $sql = "select id from site where status = 1";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)){
        echo date("Y-m-d H:i:s")." ". $row['id']. " start\n";
        $a = new Attributes();
        $f = fopen('GetAttributesCS-'.$row['id'].'.xml', 'r' );
        while( $data = fread($f, 4096)){
            $a->parse($data);
        }
        $a->dealData();
        echo date("Y-m-d H:i:s")." ". $row['id']. " end\n";
    }
}
?>