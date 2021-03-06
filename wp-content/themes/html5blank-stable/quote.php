<?php
if (isset($ninja_forms_processing)) {
    //Grab Table Data
    $ratetable = TablePress::$controller->model_table->load(1);
    $ratetable = $ratetable["data"];
    $deddisctable = TablePress::$controller->model_table->load(2);
    $deddisctable = $deddisctable["data"];
    $newhometable = TablePress::$controller->model_table->load(3);
    $newhometable = $newhometable["data"];
    $zipcodetable = TablePress::$controller->model_table->load(5);
    $zipcodetable = $zipcodetable["data"];
    $terrtable = TablePress::$controller->model_table->load(6);
    $terrtable = $terrtable["data"];
    $proctable = TablePress::$controller->model_table->load(7);
    $proctable = $proctable["data"];
    $mercovtable = Tablepress::$controller->model_table->load(9);
    $mercovtable = $mercovtable["data"];


    $quotes = $ninja_forms_processing->get_all_fields();



    //Create Quote Object
    class quote {
        function __construct($quotes) {
            //The Field numbers in brackets are defined by Ninja Forms as the field id number.
            $this->yearbuilt = $quotes['503'];
            $this->coverage = (int)$quotes['504'] = preg_replace('/[\$,]/', '', $quotes['504']);
            $this->deductible = $quotes['505'];
            $this->zip = (int)$quotes['509'];
            $this->propclass = $quotes['510'];
            $this->liacov = $quotes['519'];
        }
        //Capture Props
        public $rate = "";
        public $deddisc = "";
        public $grosspremium = "";
        public $newhomedisc = "";
        public $terramount = "";
        public $procclass = "";
        public $mappremium = "";
        public $liamount = "";
        public $total = "";
    };
    //State Quote
    $quote = new quote($quotes);

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

    //MAP Premium
    function mappre($quote) {
        $quote->mappremium = $quote->grosspremium - $quote->newhomedisc + $quote->terramount + $quote->procclass;
    }
    //Liability Premium
    function liapremium($quote, $mercovtable) {
        if ($quote->zip >= 98000 && $quote->zip <= 99403) {
            if ($quote->liacov === '$300,000') {
                $quote->liamount = 171;
            } else if ($quote->liacov === '$500,000') {
                $quote->liamount = 202;
            } else if ($quote->liacov === '$1,000,000'){
                $quote->liamount = 228;
            }
        } else if ($quote->zip >=97000 && $quote->zip <= 97999) {
            if ($quote->liacov === '$300,000') {
                $quote->liamount = 203;
            } else if ($quote->liacov === '$500,000') {
                $quote->liamount = 233;
            } else if ($quote->liacov === '$1,000,000'){
                $quote->liamount = 260;
            }
        } else {
            foreach($mercovtable as $county) {
                if ($quote->zip === (int)$county[0]) {
                    if ($quote->liacov === '$300,000') {
                        $quote->liamount = 88;
                        break;
                    } else if ($quote->liacov === '$500,000') {
                        $quote->liamount = 118;
                        break;
                    } else if ($quote->liacov === '$1,000,000'){
                        $quote->liamount = 382;
                        break;
                    }
                } else {
                    $quote->liamount = 79;
                }
            }
        }
    }
    //Total Premium
    function finalize($quote) {
        $quote->total = round($quote->liamount + $quote->mappremium,2);
    }

    rate($quote, $ratetable, $year);
    deddisc($quote, $deddisctable);
    grosspre($quote);
    newhome($quote, $newhometable, $year);
    territory($quote, $zipcodetable, $terrtable);
    classproc($quote, $proctable);
    mappre($quote);
    liapremium($quote, $mercovtable);
    finalize($quote);

     echo "<div><b>Rate:</b> ".$quote->rate."</div></br>
         <div><b>DedDisc:</b> ".$quote->deddisc."</div></br>
         <div><b>GrossPre:</b> ".$quote->grosspremium."</div></br>
         <div><b>NewHomeDisc:</b> ".$quote->newhomedisc."</div></br>
         <div><b>Terr:</b> ".$quote->terramount."</div></br>
         <div><b>ProcClass:</b> ".$quote->procclass."</div></br>
         <div><b>MAPPre:</b> ".$quote->mappremium."</div></br>
         <div><b>Lia:</b> ".$quote->liamount."</div></br>
         <div><b>Total:</b> ".$quote->total."</div></br>";
}
