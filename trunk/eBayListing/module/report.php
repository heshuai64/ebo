<?php
class Report{
    private $account_id;
    
    public function __construct($account_id){
        $this->account_id = $account_id;
    }
    
    public function saleReport(){
        $thirty_days_ago = "";
        $twenty_days_ago = "";
        $ten_days_ago = "";
        $today = "";
        
        $sql_1 = "select sum(Quantity),SKU,TemplateID,Title,ListingType,CurrentPrice,ListingDuration from items where StartTime > '".$thirty_days_ago."' and (Status = 5 or Status = 6) and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_2 = "select sum(QuantitySold) from items where StartTime > '".$thirty_days_ago."' and Status = 6 and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_3 = "select sum(Quantity) from items where Status = 2 and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_4 = "select sum(Quantity) from items where StartTime > '".$ten_days_ago."' and Status = 2 and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_5 = "select sum(Quantity) from items where StartTime > '".$twenty_days_ago."' and Status = 2 and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_6 = "select sum(QuantitySold) from items where StartTime like '".$today."' and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_7 = "select sum(Quantity) from items where StartTime like '".$today."' and Status = 2 and accountId = '".$this->account_id."' group by TemplateID";
        
        $sql_8 = "select sum(Quantity) from items where EndTime like '".$today."' and (Status = 5 or Status = 6) and accountId = '".$this->account_id."' group by TemplateID";
    }
}
?>