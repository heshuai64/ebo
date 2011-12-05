<?php
ini_set("memory_limit", "256M");

class ItemSpecifics{
    private $parser;
    private $Recommendations = array();
    private $step;
    private $stage;
    private $categoryId;
    private $NameRecommendationNum;
    private $ValueRecommendationNum;
    private $i = 0;
    
    public function __construct(){
		$conn = mysql_connect("localhost", "root", "5333533");
			
		if (!$conn) {
		    echo "Unable to connect to DB: " . mysql_error();
		    exit;
		}
		  
		if (!mysql_select_db("ebaylisting")) {
		    echo "Unable to select mydbname: " . mysql_error();
		    exit;
		}

    	$sql = 'TRUNCATE TABLE name_recommendation';
		mysql_query($sql);
			
		$sql = 'TRUNCATE TABLE value_recommendation';
		mysql_query($sql);
		
        $this->parser = xml_parser_create();

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "data_handler");
    }

    public function parse($data){
        xml_parse($this->parser, $data);
    }

    private function tag_open($parser, $tag, $attributes){
    	$this->stage = $tag;
    	$step_group = (int) ($this->step/10);
    	//echo "<font color='red'>".$tag."|".$step_group."</font><br>";
    	switch($tag){
	    case strtoupper("Recommendations"):
		    $this->step = 10;
	    break;
	    
	    case strtoupper("CategoryID"):
		    $this->step = 20;
		    $this->NameRecommendationNum = 0;
	    break;
	    
	    //NameRecommendation
	    case strtoupper("NameRecommendation"):
		    $this->step = 30;
		    $this->ValueRecommendationNum = 0;
	    break;
	    
	    case strtoupper("Name"):
		    $this->step = 31;
	    break;
	    
	    //NameRecommendation::ValidationRules
	    case strtoupper("ValidationRules"):
		    $this->step = 50;
	    break;
	    
	    case strtoupper("MaxValues"):
		    if($step_group == 5)
				$this->step = 51;
	    break;
	    
	    case strtoupper("SelectionMode"):
		    if($step_group == 5)
				$this->step = 52;
	    break;
	    
	    case strtoupper("VariationSpecifics"):
		    if($step_group == 5)
				$this->step = 53;
	    break;
	    
	    case strtoupper("Relationship"):
		    if($step_group == 5)
				$this->step = 54;
	    break;
	    
	    case strtoupper("ParentName"):
		    if($step_group == 5)
				$this->step = 55;
	    break;
	    
	    case strtoupper("ParentValue"):
		    if($step_group == 5)
				$this->step = 56;
	    break;
	    
	    //ValueRecommendation
	    case strtoupper("ValueRecommendation"):
		    $this->step = 60;
	    break;
	    
	    case strtoupper("Value"):
		    if($step_group == 6)
			    $this->step = 61;
	    break;
	    
	    //ValueRecommendation::ValidationRules
	    case strtoupper("ValidationRules"):
		    if($step_group == 6)
			    $this->step = 63;
	    break;
	    
	    case strtoupper("Relationship"):
		    if($step_group == 6)
			    $this->step = 64;
	    break;
	    
	    case strtoupper("ParentName"):
		    if($step_group == 6)
			    $this->step = 65;
	    break;
	    
	    case strtoupper("ParentValue"):
		    if($step_group == 6)
			    $this->step = 66;
	    break;
    	}
    }

    private function data_handler($parser, $cdata) {
    	//var_dump($cdata);
    	$cdata = trim($cdata);
    	if($cdata == ""){
    	    return 0;		
    	}
	
		if($this->step == 61){
		    //echo $cdata."\n";
		    $this->i++;
		}
		
		if($this->i > 500){
		    exit;
		}
		
    	echo $this->step.":".$cdata."<br>";
    	//var_dump($cdata);
    	/*if($cdata == "63869"){
    		var_dump($this->step);
    	}*/
    	
    	switch($this->step){
		    case 20:
			    $this->categoryId = $cdata;
			    $this->Recommendations[$this->categoryId] = array();
		    break;
		    
		    case 31:
			    $this->Recommendations[$this->categoryId][$this->NameRecommendationNum]['Name'] = $cdata;
		    break;
		    
		    case 51:
			    $this->Recommendations[$this->categoryId][$this->NameRecommendationNum]['MaxValues'] = $cdata;
		    break;
		    
		    case 52:
			    $this->Recommendations[$this->categoryId][$this->NameRecommendationNum]['SelectionMode'] = $cdata;
		    break;
		    
		    case 53:
			    $this->Recommendations[$this->categoryId][$this->NameRecommendationNum]['VariationSpecifics'] = $cdata;
		    break;
		    
		    case 61:
			    $this->Recommendations[$this->categoryId][$this->NameRecommendationNum]['ValueRecommendation'][$this->ValueRecommendationNum] = $cdata;
		    break;
    	}
    }

    private function tag_close($parser, $tag){
        switch($tag){
		    case strtoupper("NameRecommendation"):
				$this->NameRecommendationNum++;
		    break;
		    
		    case strtoupper("ValueRecommendation"):
				$this->ValueRecommendationNum++;
		    break;
		    
		    default:
			    //$this->step = null;
		    break;
        }
    }
    
    public function data_save(){
    	//print_r($this->Recommendations);
    	//exit;
    	foreach ($this->Recommendations as $key1=>$value1){
		    foreach ($value1 as $key2=>$value2){
				$sql_1 = "insert into name_recommendation (CategoryID,Name,ValidationRulesMaxValues,ValidationRulesSelectionMode,ValidationRulesVariationSpecifics) values  
				('".$key1."','".mysql_real_escape_string($value2['Name'])."','".$value2['MaxValues']."','".$value2['SelectionMode']."','".$value2['VariationSpecifics']."')";
				echo $sql_1."\n";
				$result_1 = mysql_query($sql_1);
				
				$NameRecommendationId = mysql_insert_id();
				if(!empty($value2['ValueRecommendation'])){
				    foreach ($value2['ValueRecommendation'] as $key3=>$value3){
					$sql_2 = "insert into value_recommendation (NameRecommendationId,ValueRecommendationValue) values 
					($NameRecommendationId,'".mysql_real_escape_string($value3)."')";
					echo $sql_2."\n";
					$result_2 = mysql_query($sql_2);
				    }
				}
		    }
		}
    }

}

$xml_parser = new ItemSpecifics();
$f = fopen('5025363447_report.xml', 'r' );
while( $data = fread( $f, 4096 ) )
{
    $data = str_replace("&quot;", "\"", $data);
    $data = str_replace("&amp;", "", $data);
    
    $xml_parser->parse($data);
}
$xml_parser->data_save();

/*
CREATE TABLE `name_recommendation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SiteID` int(11) NULL,
  `CategoryID` bigint(20) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `ValidationRulesMaxValues` int(11) NOT NULL,
  `ValidationRulesSelectionMode` varchar(25) NOT NULL,
  `ValidationRulesVariationSpecifics` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `CategoryID` (`CategoryID`)
);

CREATE TABLE `value_recommendation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `NameRecommendationId` int(11) NOT NULL,
  `ValueRecommendationValue` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `NameRecommendationId` (`NameRecommendationId`)
);
*/
?>
