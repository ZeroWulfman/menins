<?php

//Grab Table Data
$ratetable = TablePress::$controller->model_table->load(1);
$ratetable = $ratetable["data"];
$deddisctable = TablePress::$controller->model_table->load(2);
$deddisctable = $deddisctable["data"];
$newhometable = TablePress::$controller->model_table->load(3);
$newhometable = $newhometable["data"];
$yesnotable = TablePress::$controller->model_table->load(4);
$yesnotable = $yesnotable["data"];
$zipcodetable = TablePress::$controller->model_table->load(5);
$zipcodetable = $zipcodetable["data"];
$terrtable = TablePress::$controller->model_table->load(6);
$terrtable = $terrtable["data"];
$proctable = TablePress::$controller->model_table->load(7);
$proctable = $proctable["data"];

echo "<pre>";
print_r($_POST);
echo "</pre>";
//Create Quote Object
class quote {
    //Set up Props
    public $yearbuilt=2012;
    public $coverage = 251000;
    public $deductible = "$5,000";
    public $dualhomeauto = "Yes";
    public $transfer = "Yes";
    public $zip = 90001;
    public $propclass = "Rural";
    public $expcoverage = "Yes";
    public $homeplus = "Yes";
    public $lawnorder = "Yes";
    public $claimfree = "Yes";
    public $manualdisc = 10.0;
    public $manualsur = 100.0;
    public $liacov = 300000;
    //Capture Props
    public $rate = "";
    public $deddisc = "";
    public $grosspremium = "";
    public $newhomedisc = "";
    public $transferdisc = "";
    public $dualhomeautodisc = "";
    public $terramount = "";
    public $procclass = "";
    public $expcovamount = "";
    public $homeplusamount = "";
    public $lawamount = "";
    public $claimfreedisc = "";
    public $mandiscamount = "";
    public $mansuramount = "";
    public $mappremium = "";
};
//State Quote
$quote = new quote;

//Static Variables
$year = date("Y");

//Rate
function rate($quote, $ratetable, $year) {
    foreach(array_slice($ratetable,1) as $line) {
        if ($year - $quote->yearbuilt <= $line[0]) {
            return $quote->rate = $line[1];
        }
    }
}

//Deductible Discount Rate
function deddisc($quote, $deddisctable) {
    $ded = [];
    $rate = [];
    //Collect "Headings" from table
    for($i=0; $i < count($deddisctable[0]); $i++) {
        array_push($ded, $deddisctable[0][$i]);
    }
    for($i=0; $i < count($deddisctable); $i++) {
        array_push($rate, $deddisctable[$i][0]);
    }
    //Find the keys associated with headings in order to find specifc cell matching quote info
    $dedkey = array_search($quote->deductible, $ded);
    $ratekey = array_search($quote->rate, $rate);

    $quote->deddisc = $deddisctable[$ratekey][$dedkey];
}

//Super/Gross Premium (Combined functions)
function grosspre($quote) {
    $grosspre = ($quote->coverage/100)*$quote->rate;
    $quote->grosspremium = $grosspre - ($grosspre*$quote->deddisc);
}

//New Home Discount
function newhome($quote, $newhometable, $year) {
    foreach($newhometable[0] as $years) {
        if ($year - $quote->yearbuilt <= $years) {
            $newhomeratekey = array_search($years, $newhometable[0]);
            break;
        }
    }
    $newhomerate = $newhometable[1][$newhomeratekey];
    $quote->newhomedisc = ($quote->grosspremium * $newhomerate);
}

//Transfer & Dual Home/Auto Discounts, Extended Coverage, Home Plus Endorsement, CLaim Free Discount
function yesnos($quote, $yesnotable) {
    //Transfer
    if ($quote->transfer === "Yes") {
        $quote->transferdisc = ($quote->grosspremium * $yesnotable[1][1]);
    } else {
        $quote->transferdisc = ($quote->grosspremium * $yesnotable[1][2]);
    }
    //Dual Home/Auto
    if ($quote->dualhomeauto === "Yes") {
        $quote->dualhomeautodisc = ($quote->grosspremium * $yesnotable[2][1]);
    } else {
        $quote->dualhomeautodisc = ($quote->grosspremium * $yesnotable[2][2]);
    }
    //Extended Coverage
    if ($quote->expcoverage === "Yes") {
        $quote->expcovamount = $yesnotable[3][1];
    } else {
        $quote->expcovamount = $yesnotable[3][2];
    }
    //Home Plus Endorsement
    if ($quote->homeplus === "Yes") {
        $quote->homeplusamount = $yesnotable[4][1];
    } else {
        $quote->homeplusamount = $yesnotable[4][2];
    }
    //Law & Ordinance
    if ($quote->lawnorder === "Yes") {
        $quote->lawamount = ($quote->grosspremium * $yesnotable[5][1]);
    } else {
        $quote->lawamount = ($quote->grosspremium * $yesnotable[5][2]);
    }
    //Claim Free Discount
    if ($quote->claimfree === "Yes") {
        $quote->claimfreedisc = ($quote->grosspremium * $yesnotable[6][1]);
    } else {
        $quote->claimfreedisc = ($quote->grosspremium * $yesnotable[6][2]);
    }
}
//Territory
function territory($quote, $zipcodetable, $terrtable) {
    for ($i=0; $i <= count($zipcodetable);$i++) {
        if ($quote->zip === (int)$zipcodetable[$i][0]) {
            $terr = $zipcodetable[$i][1];
            break;
        }
    }
    $terrkey = array_search($terr, $terrtable[0]);
    $quote->terramount = ($quote->grosspremium * $terrtable[1][$terrkey]);
}

//Class Protection
function classproc($quote, $proctable) {
    $prockey = array_search($quote->propclass, $proctable[0]);
    $quote->procclass = ($quote->grosspremium * $proctable[1][$prockey]);
}

//Manual Discount & Surcharge
function manual($quote) {
    $quote->mandiscamount = ($quote->grosspremium * $quote->manualdisc)/100;
    $quote->mansuramount = ($quote->grosspremium * $quote->manualsur)/100;
}
function mappre($quote) {
    $quote->mappremium = $quote->grosspremium - $quote->newhomedisc - $quote->transferdisc - $quote->dualhomeautodisc + $quote->terramount + $quote->procclass + $quote->expcovamount + $quote->homeplusamount + $quote->lawamount - $quote->claimfreedisc - $quote->mandiscamount + $quote->mansuramount;
}
function liapremium($quote) {
    if ($quote->zip >= 98000 && $quote->zip <= 99403) {

    }
}

rate($quote, $ratetable, $year);
deddisc($quote, $deddisctable);
grosspre($quote);
newhome($quote, $newhometable, $year);
yesnos($quote, $yesnotable);
territory($quote, $zipcodetable, $terrtable);
classproc($quote, $proctable);
manual($quote);
mappre($quote);
 echo "<div><b>Rate:</b> ".$quote->rate."</div></br>
     <div><b>DedDisc:</b> ".$quote->deddisc."</div></br>
     <div><b>GrossPre:</b> ".$quote->grosspremium."</div></br>
     <div><b>NewHomeDisc:</b> ".$quote->newhomedisc."</div></br>
     <div><b>TransferDisc:</b> ".$quote->transferdisc."</div></br>
     <div><b>DualDisc:</b> ".$quote->dualhomeautodisc."</div></br>
     <div><b>Terr:</b> ".$quote->terramount."</div></br>
     <div><b>ProcClass:</b> ".$quote->procclass."</div></br>
     <div><b>ExpCov:</b> ".$quote->expcovamount."</div></br>
     <div><b>HomePlus:</b> ".$quote->homeplusamount."</div></br>
     <div><b>Law:</b> ".$quote->lawamount."</div></br>
     <div><b>ClaimFree:</b> ".$quote->claimfreedisc."</div></br>
     <div><b>ManDisc:</b> ".$quote->mandiscamount."</div></br>
     <div><b>ManSur:</b> ".$quote->mansuramount."</div></br>
     <div><b>MAPPre:</b> ".$quote->mappremium."</div></br>";
