<?php

namespace App\Http\Controllers\Temp;

use App\Http\Controllers\Controller;
use App\Models\Kudos\CashSales;
use App\Models\Kudos\Customer;
use App\Models\Kudos\SalesPeriod;
use App\Models\Kudos\StockColorSize;
use App\Models\Kudos\StockDetail;
use App\Models\Kudos\StockStyle;
use App\Models\Kudos\StockUDF;
use App\Models\Kudos\Supplier;
use App\Models\Kudos\Voucher;
use App\Models\Kudos\VoucherMovement;
use Illuminate\Http\Request;

class A21Controller extends Controller
{
    //
    protected $supplier;
    protected $customer;
    protected $stockdetails;
    protected $voucher;
    protected $voucherMovement;
    protected $stockcolorsize;
    protected $stockcolor;
    protected $stocksize;
    protected $stockstyle;
    protected $udf;
    protected $cashSales;
    protected $salesPeriod;


    public function __construct(CashSales $cashsales,SalesPeriod $salesperiod,Supplier $supplier, Customer $customer, StockDetail $stockdetail, Voucher $v, StockColorSize $scs, StockStyle $stockstyle, StockUDF $udf, VoucherMovement $vm)
    {
        $this->supplier = $supplier;
        $this->customer = $customer;
        $this->stockdetails = $stockdetail;
        $this->voucher = $v;
        $this->stockcolorsize = $scs;
        $this->stockstyle = $stockstyle;
        $this->udf = $udf;
        $this->voucherMovement = $vm;
        $this->cashSales = $cashsales;
        $this->salesPeriod = $salesperiod;
    }

    public function supplierExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=supplier.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('CODE', 'NAME', 'A.C.N', 'A.B.N', 'REF1', 'REF2', 'REF3', 'REF4', 'REF5', 'REF6', 'REF7', 'REF8', 'REF9', 'REF10', 'BCODE','BCITY', 'BSB','BACCOUNTN', 'BACCOUNT', 'PAYTYPE', 'PMT_PRIORITY', 'AGEPERIOD', 'TRADENAME', 'CONTACT', 'ADDRESS1', 'ADDRESS2', 'CITY', 'STATE', 'COUNTRY', 'POSTCODE', 'PHONENUM', 'EMAIL', 'CURRENCY', 'MOBILE', 'FAX', 'CREDIT LIMIT', 'SETT DISC PERCENTAGE','CREDIT LIMIT TERMS','SETT DISC TERMS','SETT TERMS','LODGREF','LEADTIME','ACCOUNT PAY STATUS','ACCOUNT ORD STATUS','On forwarder Code','Partial Discount Code','Print Type','BPAY BILLER CODE','Template Title');
        $columnsTable = array('Code', 'Description', '', '', '', '', '', '', '', '', '', '', '', '', 'Bank Name','Bank Branch', '',
        'Bank Account Number', '', '', '', '', 'Description', 'Contact', 'Address 1', 'Address 2',
        '', '', '', '', 'Phone', 'Email', 'Default', '', 'Fax', 'Credit Limit', 'Default Discount %','','','Term Days',
        '','Lead Time','','','','','','', 'DB Column Name');
        $data = $this->supplier->limit(1000)->get();
        // dd($data);
        $callback = function() use($data, $columns, $columnsTable) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $columnsTable);

            foreach ($data as $task) {
                $row['CODE']  = $task->Code;
                $row['NAME']    = $task->Description;
                $row['A.C.N']    = '';
                $row['A.B.N']  = '';
                $row['REF1']  = '';
                $row['REF2']  = '';
                $row['REF3']  = '';
                $row['REF4']  = '';
                $row['REF5']  = '';
                $row['REF6']  = '';
                $row['REF7']  = '';
                $row['REF8']  = '';
                $row['REF9']  = '';
                $row['REF10']  = '';
                $row['BCODE']  = $task['Bank Name'];
                $row['BCITY']  = $task['Bank Branch'];
                $row['BSB']  = '';
                $row['BACCOUNTN'] = $task['Bank Account Number'];
                $row['BACCOUNT'] = '';
                $row['PAYTYPE']  = 'Cheque';
                $row['PMT_PRIORITY']  = '';
                $row['AGEPERIOD']  = '';
                $row['TRADENAME']  = $task['Description'];
                $row['CONTACT']  = $task['Contact'];
                $row['ADDRESS1']  = $task['Address 1'];
                $row['ADDRESS2']  = $task['Address 2'];
                $row['CITY']  = '';
                $row['STATE']  = '';
                $row['COUNTRY']  = '';
                $row['POSTCODE']  = '';
                $row['PHONENUM']  = $task['Phone'];
                $row['EMAIL']  = $task['Email'];
                $row['CURRENCY']  = 'AUD';
                $row['MOBILE']  = '';
                $row['FAX']  = $task['Fax'];
                $row['CREDIT LIMIT']  = $task['Credit Limit'];
                $row['SETT DISC PERCENTAGE ']  = $task['Default Discount %'];
                $row['CREDIT LIMIT TERMS']  = '';
                $row['SETT DISC TERMS']  = '';
                $row['SETT TERMS']  = $task['Term Days'];
                $row['LODGREF']  = '';
                $row['LEADTIME']  = $task['Lead Time'];
                $row['ACCOUNT PAY STATUS']  = 'Normal';
                $row['ACCOUNT ORD STATUS']  = 'Normal';
                $row['On forwarder Code']  = '';
                $row['Partial Discount Code']  = '';
                $row['Print Type']  = '';
                $row['BPAY BILLER CODE']  = '';

                fputcsv($file, array(
                    $row['CODE'],
                    $row['NAME'],
                    $row['A.C.N'],
                    $row['A.B.N'],
                    $row['REF1'],
                    $row['REF2'],
                    $row['REF3'],
                    $row['REF4'],
                    $row['REF5'],
                    $row['REF6'],
                    $row['REF7'],
                    $row['REF8'],
                    $row['REF9'],
                    $row['REF10'],
                    $row['BCODE'] ,
                    $row['BCITY'] ,
                    $row['BSB'] ,
                    $row['BACCOUNTN'],
                    $row['BACCOUNT'],
                    $row['PAYTYPE']  ,
                    $row['PMT_PRIORITY'] ,
                    $row['AGEPERIOD']  ,
                    $row['TRADENAME']  ,
                    $row['CONTACT']  ,
                    $row['ADDRESS1']  ,
                    $row['ADDRESS2']  ,
                    $row['CITY']  ,
                    $row['STATE']  ,
                    $row['COUNTRY']  ,
                    $row['POSTCODE']  ,
                    $row['PHONENUM']  ,
                    $row['EMAIL']  ,
                    $row['CURRENCY']  ,
                    $row['MOBILE']  ,
                    $row['FAX']  ,
                    $row['CREDIT LIMIT']  ,
                    $row['SETT DISC PERCENTAGE ']  ,
                    $row['CREDIT LIMIT TERMS']  ,
                    $row['SETT DISC TERMS']  ,
                    $row['SETT TERMS']  ,
                    $row['LODGREF']  ,
                    $row['LEADTIME']  ,
                    $row['ACCOUNT PAY STATUS']  ,
                    $row['ACCOUNT ORD STATUS']  ,
                    $row['On forwarder Code']  ,
                    $row['Partial Discount Code']  ,
                    $row['Print Type']  ,
                    $row['BPAY BILLER CODE']  ,
                ));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function customerExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Customer.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('Customer Code', 'Customer Name', 'Bill To', 'DC', 'Store Number', 'ACN', 'ABN', 'Currency', 'Inv Trade Name', 'Inv Contact', 'Inv Address 1', 'Inv Address 2',
         'Inv City', 'Inv State', 'Inv Country','Inv Postcode', 'Inv Phone Number','Inv Email', 'Inv Fax', 'Inv Mobile', 'Warehouse', 'Price Schema', 'Ref 1 - Code', 'Ref 2 - Code', 'Ref 3 - Code', 'Ref 4 - Code', 'Ref 5 - Code', 'Ref 6 - Code', 'Ref 7 - Code', 'Ref 8 - Code', 'Ref 9 - Code', 'Ref 10 - Code',
         'Carrier', 'On-forwarder', 'Service Type', 'Delv Trade Name', 'Delv Contact', 'Delv Adrs1', 'Delv Adrs2', 'Delv City','Delv State','Delv Country','Delv Post Code','Bank Code','Bank Branch','Bank BSB','Bank Account Name','Credit Limit Amount','Credit Limit Days','Settlement Discount %','Settlement Discount Days','Settlement Days','Credit Rating','Despatch Priority','Age Statements By','Statement Type','Print Type','Discount %','Rebate 1','Rebate 2','Template Title');
        $columnsTable = array('Code', 'Contact FirstName LastName', 'Code', '', '', '', '', '', 'Contact FirstName LastName',
        'Contact Title FirstName LastName', 'Address Street', '',
         'Address City', '', 'Address Country','Address Postcode', 'Phone Home','Email', 'Fax', 'Phone Mobile', '', 'Price', 'Ref 1 - Code', 'Ref 2 - Code', 'Ref 3 - Code', 'Ref 4 - Code', 'Ref 5 - Code', 'Ref 6 - Code', 'Ref 7 - Code', 'Ref 8 - Code', 'Ref 9 - Code', 'Ref 10 - Code',
         '', '', 'Type', 'Delivery Trade Name', 'Delivery Title FirstName LastName', 'Delivery Address Street', '',
         'Delivery Address City','','Delivery Address Country','Delivery Address Post Code',
         '','Bank Branch','','','Credit Limit','Term Days','','','','','','','','','','','','DB Column Name');
         $data = $this->customer
                ->join("Customer Delivery Details", "Customer Delivery Details.Customer ID","Customer.ID")
                ->limit(1000)
                ->get();

        // dd($data);
        $callback = function() use($data, $columns,$columnsTable) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $columnsTable);

            foreach ($data as $task) {
                $row['Customer Code']  = $task->Code;
                $row['Customer Name']    = $task['Contact FirstName'].' '.$task['Contact LastName'];//$task['Trading Name'];
                $row['Bill To']    = $task->Code;
                $row['DC']  = '';
                $row['Store Number']  = '';
                $row['ACN']  = '';
                $row['ABN']  = '';
                $row['Currency']  = '';
                $row['Inv Trade Name']  = $task['Trading Name'];
                $row['Inv Contact']  = $task['Contact Title'].' '.$task['Contact FirstName'].' '.$task['Contact LastName'];
                $row['Inv Address 1']  = $task['Address Street'];
                $row['Inv Address 2']  = '';
                $row['Inv City']  = $task['Address City'];
                $row['Inv State']  = '';
                $row['Inv Country']  = $task['Address County'];
                $row['Inv Postcode']  = $task['Address Post Code'];
                $row['Inv Phone Number']  = $task['Phone Home'];
                $row['Inv Email'] = $task['Email'];
                $row['Inv Fax'] = $task['Fax'];
                $row['Inv Mobile']  = $task['Phone Mobile'];
                $row['Warehouse']  = '';
                $row['Price Schema']  = $task['Price'];
                $row['Ref 1 - Code']  = '';
                $row['Ref 2 - Code']  = '';
                $row['Ref 3 - Code']  = '';
                $row['Ref 4 - Code']  = '';
                $row['Ref 5 - Code']  = '';
                $row['Ref 6 - Code']  = '';
                $row['Ref 7 - Code']  = '';
                $row['Ref 8 - Code']  = '';
                $row['Ref 9 - Code']  = '';
                $row['Ref 10 - Code']  = '';
                $row['Carrier']  = '';
                $row['On-forwarder']  = '';
                $row['Service Type']  = '';
                $row['Delv Trade Name']  = $task["Delivery Trading Name"];
                $row['Delv Contact']  =  $task['Delivery Title'].' '.$task['Delivery FirstName'].' '.$task['Delivery LastName'];;
                $row['Delv Adrs1']  = $task['Delivery Address Street'];
                $row['Delv Adrs2']  = '';
                $row['Delv City']       = $task["Delivery Address City"];
                $row['Delv State']       = '';
                $row['Delv Country']       = $task['Delivery Address Country'];
                $row['Delv Post Code']       = $task['Delivery Address Post Code'];
                $row['Bank Code']      = '';
                $row['Bank Branch']       = $task['Bank Branch'];
                $row['Bank BSB']       = '';
                $row['Bank Account Name']       = '';
                $row['Credit Limit Amount']       = $task['Credit Limit'];
                $row['Credit Limit Days']       = $task['Term Days'];
                $row['Settlement Discount %']       = '';
                $row['Settlement Discount Days']       = '';
                $row['Settlement Days']      = '';
                $row['Credit Rating']       = '';
                $row['Despatch Priority']       = '';
                $row['Age Statements By']       = '';
                $row['Statement Type']     = '';
                $row['Print Type']     = '';
                $row['Discount %']     = '';
                $row['Rebate 1']     = '';
                $row['Rebate 2']     = '';

                fputcsv($file, array(
                    $row['Customer Code'],
                    $row['Customer Name'],
                    $row['Bill To'],
                    $row['DC'],
                    $row['Store Number'],
                    $row['ACN'],
                    $row['ABN'],
                    $row['Currency'],
                    $row['Inv Trade Name'],
                    $row['Inv Contact'],
                    $row['Inv Address 1'],
                    $row['Inv Address 2'],
                    $row['Inv City'],
                    $row['Inv State'],
                    $row['Inv Country'] ,
                    $row['Inv Postcode'] ,
                    $row['Inv Phone Number'] ,
                    $row['Inv Email'],
                    $row['Inv Fax'],
                    $row['Inv Mobile']  ,
                    $row['Warehouse'] ,
                    $row['Price Schema']  ,
                    $row['Ref 1 - Code']  ,
                    $row['Ref 2 - Code']  ,
                    $row['Ref 3 - Code']  ,
                    $row['Ref 4 - Code']  ,
                    $row['Ref 5 - Code']  ,
                    $row['Ref 6 - Code']  ,
                    $row['Ref 7 - Code']  ,
                    $row['Ref 8 - Code']  ,
                    $row['Ref 9 - Code']  ,
                    $row['Ref 10 - Code']  ,
                    $row['Carrier']  ,
                    $row['On-forwarder']  ,
                    $row['Service Type']  ,
                    $row['Delv Trade Name']  ,
                    $row['Delv Contact']  ,
                    $row['Delv Adrs1']  ,
                    $row['Delv Adrs2']  ,
                    $row['Delv City']  ,
                    $row['Delv State']  ,
                    $row['Delv Country']  ,
                    $row['Delv Post Code']  ,
                    $row['Bank Code']  ,
                    $row['Bank Branch']  ,
                    $row['Bank BSB']  ,
                    $row['Bank Account Name']  ,
                    $row['Credit Limit Amount']  ,
                    $row['Credit Limit Days']  ,
                    $row['Settlement Discount %']  ,
                    $row['Settlement Discount Days']  ,
                    $row['Settlement Days']  ,
                    $row['Credit Rating']  ,
                    $row['Despatch Priority']  ,
                    $row['Age Statements By']  ,
                    $row['Statement Type'],
                    $row['Print Type'],
                    $row['Discount %'],
                    $row['Rebate 1'],
                    $row['Rebate 2'],
                ));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function productExport(Request $req){
        $limit = $req->limit == '' ? 200 : $req->limit;


        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=PRODUCTS_.CSV",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $ap21Header = array('Product Code','Product Description','Colour Code','Colour Description','Size Range','Size Code','EAN Code','Sell Price','Purchased','Produced','Sold',
        'Stocked',
        'Used In Production',
        'Include in MRP',
        'Sold at Retail',
        'Ref1',
        'Ref2',
        'Ref3','Ref4','Ref5','Ref6','Ref7','Ref8','Ref9','Ref10','Ref11','Ref12','Ref13','Ref14','Ref15',
        'Ref16','Ref17','Ref18','Ref19','Ref20'
        ,'ColourRef1','ColourRef2','ColourRef3','ColourRef4','ColourRef5','ColourRef6','ColourRef7','ColourRef8','ColourRef9','ColourRef10','Cost','Dimension Range','Dimension Code'
        ,'Style Level','UOM');

        $tableColumns = array('','','','','','','','Selling Price 1','','','',
        '',
        '',
        '',
        '',
        'UDF 1',
        'UDF 2',
        'UDF 3','UDF 4','Look Up Number','Internet Active / e-retailer inactive','Average Cost','Standard Cost','Selling Price 2','Selling Price 3','Selling Price 4','Selling Price 5','Ref13','Ref14','Ref15',
        'Ref16','Ref17','Ref18','Ref19','Ref20'
        ,'ColourRef1','ColourRef2','ColourRef3','ColourRef4','ColourRef5','ColourRef6','ColourRef7','ColourRef8','ColourRef9','ColourRef10','Last Purchase Price','',''
        ,'Style Level','UOM');

        $data = //$this->stockstyle->limit(10)->get();
        $this->stockcolorsize
                        ->join("Stock Style", "Style Colour Size.Style ID","Stock Style.ID") //stock style
                        // ->join("Stock Size", "Stock Size.ID","Style Colour Size.Size ID")
                        // ->join("Stock Colour", "Stock Colour.ID","Style Colour Size.Colour ID")
                        ->join("Stock Detail", "Stock Detail.Style/Colour/Size ID","Style Colour Size.ID")
                        // ->leftJoin("Barcode", "Barcode.Style/Colour/Size ID","Style Colour Size.ID")
                        ->select(["Stock Style.*","Style Colour Size.*","Stock Style.Code as productCode","Stock Style.ID as productID",

                        ])
                        // ->select(["Stock Style.*", "Style Colour Size.*","Stock Size.Code as sizeCode","Stock Size.Order as sizeOrder"])
                        // ->where("Stock Style.Code", "<>","")
                        // ->where("Stock Style.ID", $matrix["ID"])
                        ->where("Stock Detail.Branch ID", 1)
                        ->where("Style Colour Size.Size ID", 0)
                        ->where("Style Colour Size.Colour ID", 0)
                        // ->where("Style Colour Size.Style ID", 21)
                        // ->orderBy("Stock Size.Order", 'asc')
                        // ->toSql();
                        // ->limit(2000)
                        ->get();
                        // ->random($limit);

        // dd($data);
        $callback = function() use($data, $ap21Header,$tableColumns ) {

            $file = fopen('php://output', 'w');
            fputcsv($file, $ap21Header);
            fputcsv($file, $tableColumns);


            foreach ($data as $matrix) {

                $child = $this->stockcolorsize
                ->join("Stock Style", "Style Colour Size.Style ID","Stock Style.ID") //stock style
                ->join("Stock Size", "Stock Size.ID","Style Colour Size.Size ID")
                ->join("Stock Colour", "Stock Colour.ID","Style Colour Size.Colour ID")
                ->join("Stock Detail", "Stock Detail.Style/Colour/Size ID","Style Colour Size.ID")
                ->leftJoin("Barcode", "Barcode.Style/Colour/Size ID","Style Colour Size.ID")
                ->select(["Stock Style.*","Style Colour Size.*","Stock Style.Code as productCode","Stock Style.ID as productID",
                "Stock Colour.Code as colourCode","Stock Colour.Code as colourDes","Stock Size.Code as sizeCode","Stock Size.Code as sizeDes",
                "Barcode.As Number as barCode", "Stock Detail.*"
                ])
                // ->select(["Stock Style.*", "Style Colour Size.*","Stock Size.Code as sizeCode","Stock Size.Order as sizeOrder"])
                // ->where("Stock Style.Code", "<>","")
                ->where("Stock Style.ID", $matrix["Style ID"])
                ->where("Stock Detail.Branch ID", 1)
                ->orderBy("Stock Size.Order", 'asc')
                // ->toSql();
                ->get();
                // dd($child);
                $row['Product Code']  = $matrix->Code;
                $row['Product Description']    = $matrix['Description'];
                $row['Colour Code']    = '';
                $row['Colour Description']  = '';
                $row['Size Range']  =  count($child) > 0 ? "'".$child[0]["sizeCode"]."-".$child[count($child)-1]["sizeCode"] : '';
                $row['Size Code']  = '';
                $row['EAN Code']  = '';
                $row['Sell Price']  = '';
                $row['Purchased']  = "Y";
                $row['Produced']  = "Y";
                $row['Sold']  = "Y";
                $row['Stocked']  = 'Y';
                $row['Used In Production']  = "Y";
                $row['Include in MRP']  = 'Y';
                $row['Sold at Retail']  = "Y";
                $row['Ref1']  = $matrix['UDF1 ID'] == 0 ? '' : $this->udf->where('ID', $matrix['UDF1 ID'])->first()->{'Description'};
                $row['Ref2']  = $matrix['UDF2 ID'] == 0 ? '' : $this->udf->where('ID', $matrix['UDF2 ID'])->first()->{'Description'};
                $row['Ref3']  = $matrix['UDF3 ID'] == 0 ? '' : $this->udf->where('ID', $matrix['UDF3 ID'])->first()->{'Description'};
                $row['Ref4']  = $matrix['UDF4 ID'] == 0 ? '' : $this->udf->where('ID', $matrix['UDF4 ID'])->first()->{'Description'};
                $row['Ref5']  = $matrix["Look Up Number"];
                $row['Ref6']  = $matrix["Internet Active"];//Internet Active
                $row['Ref7']  = '';
                $row['Ref8']  = '';
                $row['Ref9']  ='';
                $row['Ref10']  = '';
                $row['Ref11']  = '';
                $row['Ref12']  = '';
                $row['Ref13']  ='';
                $row['Ref14']  ='';
                $row['Ref15']  ='';
                $row['Ref16']  ='';
                $row['Ref17']  ='';
                $row['Ref18']  ='';
                $row['Ref19']  ='';
                $row['Ref20']  ='';
                $row['ColourRef1']  = '';
                $row['ColourRef2']  = '';
                $row['ColourRef3']  = '';
                $row['ColourRef4']  = '';
                $row['ColourRef5']  = '';
                $row['ColourRef6']  = '';
                $row['ColourRef7']  = '';
                $row['ColourRef8']  = '';
                $row['ColourRef9']  = '';
                $row['ColourRef10']  = '';
                $row['Cost'] = '';
                $row['Dimension Range'] = '';
                $row['Dimension Code']  = '';
                $row['Style Level']  = '2';
                $row['UOM']  = '';


                fputcsv($file,
                    array(
                        $row['Product Code'] ,
                        $row['Product Description'] ,
                        $row['Colour Code'],
                        $row['Colour Description'] ,
                        $row['Size Range']  ,
                        $row['Size Code']  ,
                        $row['EAN Code']  ,
                        $row['Sell Price'] ,
                        $row['Purchased'] ,
                        $row['Produced'] ,
                        $row['Sold']  ,
                        $row['Stocked']  ,
                        $row['Used In Production'] ,
                        $row['Include in MRP'],
                        $row['Sold at Retail'] ,
                        $row['Ref1']  ,
                        $row['Ref2']  ,
                        $row['Ref3']  ,
                        $row['Ref4']  ,
                        $row['Ref5']  ,
                        $row['Ref6']  ,
                        $row['Ref7']  ,
                        $row['Ref8']  ,
                        $row['Ref9']  ,
                        $row['Ref10']  ,
                        $row['Ref11']  ,
                        $row['Ref12']  ,
                        $row['Ref13']  ,
                        $row['Ref14']  ,
                        $row['Ref15']  ,
                        $row['Ref16']  ,
                        $row['Ref17'] ,
                        $row['Ref18']  ,
                        $row['Ref19']  ,
                        $row['Ref20']  ,
                        $row['ColourRef1'] ,
                        $row['ColourRef2'] ,
                        $row['ColourRef3'] ,
                        $row['ColourRef4'] ,
                        $row['ColourRef5'] ,
                        $row['ColourRef6'] ,
                        $row['ColourRef7'] ,
                        $row['ColourRef8'],
                        $row['ColourRef9'] ,
                        $row['ColourRef10'] ,
                        $row['Cost'] ,
                        $row['Dimension Range'],
                        $row['Dimension Code'] ,
                        $row['Style Level'] ,
                        $row['UOM']
                    )
                );


                foreach($child as $task){
                    $row['Product Code']  = $task->Code;
                    $row['Product Description']    = $task['Description'];
                    $row['Colour Code']    = $task['colourCode'];
                    $row['Colour Description']  = $task['colourDes'] == '' ? $task['colourCode'] : $task['colourDes'];
                    $row['Size Range']  = "'".$child[0]["sizeCode"]."-".$child[count($child)-1]["sizeCode"];
                    $row['Size Code']  = $task['sizeCode'];
                    $row['EAN Code']  = $task['barCode'];
                    $row['Sell Price']  = $task["Selling Price 1"];
                    $row['Purchased']  = "Y";
                    $row['Produced']  = "Y";
                    $row['Sold']  = "Y";
                    $row['Stocked']  = 'Y';
                    $row['Used In Production']  = "Y";
                    $row['Include in MRP']  = 'Y';
                    $row['Sold at Retail']  = "Y";
                    $row['Ref1']  = $task['UDF1 ID'] == 0 ? '' : $this->udf->where('ID', $task['UDF1 ID'])->first()->{'Description'};
                    $row['Ref2']  = $task['UDF2 ID'] == 0 ? '' : $this->udf->where('ID', $task['UDF2 ID'])->first()->{'Description'};
                    $row['Ref3']  = $task['UDF3 ID'] == 0 ? '' : $this->udf->where('ID', $task['UDF3 ID'])->first()->{'Description'};
                    $row['Ref4']  = $task['UDF4 ID'] == 0 ? '' : $this->udf->where('ID', $task['UDF4 ID'])->first()->{'Description'};
                    $row['Ref5']  = $task["Look Up Number"];
                    $row['Ref6']  = $task["e-retailer inactive"] == 0 ? 1 : 0;//Internet Active
                    $row['Ref7']  = $task['Average Cost'];
                    $row['Ref8']  = $task['Standard Cost'];
                    $row['Ref9']  = $task['Selling Price 2'];
                    $row['Ref10']  = $task['Selling Price 3'];
                    $row['Ref11']  = $task['Selling Price 4'];
                    $row['Ref12']  = $task['Selling Price 5'];
                    $row['Ref13']  ='';
                    $row['Ref14']  ='';
                    $row['Ref15']  ='';
                    $row['Ref16']  ='';
                    $row['Ref17']  ='';
                    $row['Ref18']  ='';
                    $row['Ref19']  ='';
                    $row['Ref20']  ='';
                    $row['ColourRef1']  = '';
                    $row['ColourRef2']  = '';
                    $row['ColourRef3']  = '';
                    $row['ColourRef4']  = '';
                    $row['ColourRef5']  = '';
                    $row['ColourRef6']  = '';
                    $row['ColourRef7']  = '';
                    $row['ColourRef8']  = '';
                    $row['ColourRef9']  = '';
                    $row['ColourRef10']  = '';
                    $row['Cost'] = $task['Last Purchase Price'];
                    $row['Dimension Range'] = '';
                    $row['Dimension Code']  = '';
                    $row['Style Level']  = '2';
                    $row['UOM']  = '';


                    fputcsv($file,
                        array(
                            $row['Product Code'] ,
                            $row['Product Description'] ,
                            $row['Colour Code'],
                            $row['Colour Description'] ,
                            $row['Size Range']  ,
                            $row['Size Code']  ,
                            $row['EAN Code']  ,
                            $row['Sell Price'] ,
                            $row['Purchased'] ,
                            $row['Produced'] ,
                            $row['Sold']  ,
                            $row['Stocked']  ,
                            $row['Used In Production'] ,
                            $row['Include in MRP'],
                            $row['Sold at Retail'] ,
                            $row['Ref1']  ,
                            $row['Ref2']  ,
                            $row['Ref3']  ,
                            $row['Ref4']  ,
                            $row['Ref5']  ,
                            $row['Ref6']  ,
                            $row['Ref7']  ,
                            $row['Ref8']  ,
                            $row['Ref9']  ,
                            $row['Ref10']  ,
                            $row['Ref11']  ,
                            $row['Ref12']  ,
                            $row['Ref13']  ,
                            $row['Ref14']  ,
                            $row['Ref15']  ,
                            $row['Ref16']  ,
                            $row['Ref17'] ,
                            $row['Ref18']  ,
                            $row['Ref19']  ,
                            $row['Ref20']  ,
                            $row['ColourRef1'] ,
                            $row['ColourRef2'] ,
                            $row['ColourRef3'] ,
                            $row['ColourRef4'] ,
                            $row['ColourRef5'] ,
                            $row['ColourRef6'] ,
                            $row['ColourRef7'] ,
                            $row['ColourRef8'],
                            $row['ColourRef9'] ,
                            $row['ColourRef10'] ,
                            $row['Cost'] ,
                            $row['Dimension Range'],
                            $row['Dimension Code'] ,
                            $row['Style Level'] ,
                            $row['UOM']
                        )
                    );
                }

            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function voucherExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Voucher_.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('Store Number','Voucher Number','Is Alphanumeric','PIN','Product Code','Issue/GL Date','Expiry Date','Remaining Value','Tender Type','Person Code'
        );
        $tableColumns = array('Store Number','Voucher Number','Is Alphanumeric','PIN','Product Code','Issue/GL Date','Expiry Date','Remaining Value','Tender Type','Person Code'
        );
        $data = $this->voucher
                     ->join("Voucher Movement", "Voucher Movement.Voucher ID", "Voucher.ID")
                     ->join("Branch", "Branch.ID", "Voucher Movement.Branch ID")
                     ->where("Voucher.Deleted", 0)
                     ->where("Voucher Movement.Movement Type", 1)
                     ->select(["Voucher.ID as vID","Voucher.Serial Number as SerialNumber","Voucher.Expiry","Voucher Movement.ID as mID","Voucher Movement.Voucher ID as mvID","Voucher Movement.Value","Voucher Movement.Date", "Branch.Code as branchCode"])
                     ->limit(2000)
                    //  ->where("Voucher.ID","5DF9CD66-0945-4059-BC1C-C8A36924A494")
                     ->get();
                    //  ->random(1);

        // dd($data);
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $key => $v) {
                info($key);
                //first get data from movement table and
                //check whether voucher is used or not
                $movements =  $this->voucherMovement->where("Voucher Id", $v->vID)->where("Movement Type",'<>', 1)->orderBy('Date', 'asc')->get();

                //if more than 1 rows check value
                $flag = true;
                $remaingValue = $v["Value"];
                foreach($movements as $vm){
                    $remaingValue = $remaingValue + $vm["Value"];
                }
                if($remaingValue <= 0){
                    $flag = false;
                }
                info("Remaining Amount ".$remaingValue);
                if(true == $flag){

                    $row['Store Number']  = $v['branchCode'];
                    $row['Voucher Number']    = $v['SerialNumber'];
                    $row['Is Alphanumeric']    = is_numeric($v["SerialNumber"]) == 1 ? 'N' : 'Y';
                    $row['PIN']  = '';
                    $row['Product Code']  = '';
                    $row['Issue/GL Date']  = $v['Date'];
                    $row['Expiry Date']  =  $v['Expiry'];
                    $row['Remaining Value']  = $remaingValue;
                    $row['Tender Type']  = '';
                    $row['Person Code']  = '';



                    fputcsv($file, array(
                    $row['Store Number'] ,
                    $row['Voucher Number'] ,
                    $row['Is Alphanumeric'],
                    $row['PIN'] ,
                    $row['Product Code']  ,
                    $row['Issue/GL Date']  ,
                    $row['Expiry Date']  ,
                    $row['Remaining Value'] ,
                    $row['Tender Type'] ,
                    $row['Person Code'] ,
                    ));
                }



            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function personExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Voucher_.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('Person Code','Title','First Name','Surname','Address Line 1','Address Line 2','City','State','Postcode','Country'
        ,'Telephone 1','Telephone 2','Telephone 3','Job Title','DOB','Date 1','Date 2','Reference 1','Reference 2','Reference 3','Reference 4','Reference 5','Reference 6','Reference 7','Reference 8','Reference 9','Reference 10'
        ,'Loyalty Type','Loyalty Number','Loyalty Expiry','Created Store Code','Customer Code','Retail Customer','Retail Sales Rep'
        ,'Privacy Flag','Username','Gender','SalesNet Flag','Supplier Code'
        );
        $data = $this->voucher->limit(10)->get();

        dd($data);
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $task) {
                $row['Person Code']  = '';
                $row['Title']  = '';
                $row['First Name ']  = '';
                $row['Surname']  = '';
                $row['Address Line 1']  = '';
                $row['Address Line 2']  = '';
                $row['City']  = '';
                $row['State']  = '';
                $row['Postcode']  = '';
                $row['Country']  = '';
                $row['Telephone 1']  = '';
                $row['Telephone 2']  = '';
                $row['Telephone 3']  = '';
                $row['Job Title']  = '';
                $row['DOB']  = '';
                $row['Date 1']  = '';
                $row['Date 2']  = '';
                $row['Reference 1']  = '';
                $row['Reference 2']  = '';
                $row['Reference 3']  = '';
                $row['Reference 4']  = '';
                $row['Reference 5']  = '';
                $row['Reference 6']  = '';
                $row['Reference 7']  = '';
                $row['Reference 8']  = '';
                $row['Reference 9']  = '';
                $row['Reference 10']  = '';
                $row['Loyalty Type']  = '';
                $row['Loyalty Number']  = '';
                $row['Loyalty Expiry']  = '';
                $row['Created Store Code']  = '';
                $row['Customer Code']  = '';
                $row['Retail Customer']  = '';
                $row['Retail Sales Rep']  = '';
                $row['Privacy Flag']  = '';
                $row['Username']  = '';
                $row['Gender']  = '';
                $row['SalesNet Flag']  = '';
                $row['Supplier Code']  = '';



                fputcsv($file, array(
                    $row['Person Code '] ,
                    $row['Title'] ,
                    $row['First Name'] ,
                    $row['Surname'] ,
                    $row['Address Line 1'] ,
                    $row['Address Line 2'] ,
                    $row['City'] ,
                    $row['State'] ,
                    $row['Postcode'] ,
                    $row['Country'] ,
                    $row['Telephone 1'] ,
                    $row['Telephone 2'] ,
                    $row['Telephone 3'] ,
                    $row['Job Title'] ,
                    $row['DOB'] ,
                    $row['Date 1'] ,
                    $row['Date 2'] ,
                    $row['Reference 1'],
                    $row['Reference 2'],
                    $row['Reference 3'],
                    $row['Reference 4'],
                    $row['Reference 5'],
                    $row['Reference 6'],
                    $row['Reference 7'],
                    $row['Reference 8'],
                    $row['Reference 9'],
                    $row['Reference 10'],
                    $row['Loyalty Type'] ,
                    $row['Loyalty Number'] ,
                    $row['Loyalty Expiry'] ,
                    $row['Created Store Code'] ,
                    $row['Customer Code'] ,
                    $row['Retail Customer'] ,
                    $row['Retail Sales Rep'] ,
                    $row['Privacy Flag'] ,
                    $row['Username'] ,
                    $row['Gender'] ,
                    $row['SalesNet Flag'] ,
                    $row['Supplier Code'] ,
                ));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function barcodeExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=BARCODES.CSV",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('STYLE','COLOUR','SIZEVAL','EANTYPE-1','BARCODE-1','EANTYPE -2','BARCODE-2','EANTYPE-3','BARCODE-3','EANTYPE-4','BARCODE-4'
        ,'EANTYPE-5','BARCODE-5','EANTYPE-6','BARCODE-6','EANTYPE-7','BARCODE-7','EANTYPE-8','BARCODE-8','EANTYPE-9','BARCODE-9',
        'EANTYPE-10','BARCODE-10'
        );

        $columns2 = array('','','','EANTYPE-1','Look Up Number','EANTYPE -2','As Number','EANTYPE-3','BARCODE-3','EANTYPE-4','BARCODE-4'
        ,'EANTYPE-5','BARCODE-5','EANTYPE-6','BARCODE-6','EANTYPE-7','BARCODE-7','EANTYPE-8','BARCODE-8','EANTYPE-9','BARCODE-9',
        'EANTYPE-10','BARCODE-10'
        );

        $data = $this->stockcolorsize
        ->join("Stock Style", "Style Colour Size.Style ID","Stock Style.ID") //stock style
        ->join("Stock Size", "Stock Size.ID","Style Colour Size.Size ID")
        ->join("Stock Colour", "Stock Colour.ID","Style Colour Size.Colour ID")
        ->join("Stock Detail", "Stock Detail.Style/Colour/Size ID","Style Colour Size.ID")
        ->join("Barcode", "Barcode.Style/Colour/Size ID","Style Colour Size.ID")
        ->select(["Stock Style.Code as productCode","Stock Style.ID as productID",
        "Stock Colour.Code as colourCode","Stock Size.Code as sizeCode",
        "Barcode.As Number as barCode", "Style Colour Size.Look Up Number"
        ])
        // ->select(["Stock Style.*", "Style Colour Size.*","Stock Size.Code as sizeCode","Stock Size.Order as sizeOrder"])
        // ->where("Stock Style.Code", "<>","")
        // ->where("Stock Style.ID", $matrix["Style ID"])
        ->where("Stock Detail.Branch ID", 1)
        // ->orderBy("Stock Size.Order", 'asc')
        // ->toSql();
        ->limit(2000)
        ->get();

        // dd($data);
        $callback = function() use($data, $columns, $columns2) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $columns2);

            foreach ($data as $task) {
                $row['STYLE']  = $task['productCode'];
                $row['COLOUR']  = $task['colourCode'];
                $row['SIZEVAL']  = $task['sizeCode'];
                $row['EANTYPE-1']  = '0';
                $row['BARCODE-1']  = $task['Look Up Number'];
                $row['EANTYPE-2']  = '1';
                $row['BARCODE-2']  = $task['barCode'];
                $row['EANTYPE-3']  = '';
                $row['BARCODE-3']  = '';
                $row['EANTYPE-4']  = '';
                $row['BARCODE-4']  = '';
                $row['EANTYPE-5']  = '';
                $row['BARCODE-5']  = '';
                $row['EANTYPE-6']  = '';
                $row['BARCODE-6']  = '';
                $row['EANTYPE-7']  = '';
                $row['BARCODE-7']  = '';
                $row['EANTYPE-8']  = '';
                $row['BARCODE-8']  = '';
                $row['EANTYPE-9']  = '';
                $row['BARCODE-9']  = '';
                $row['EANTYPE-10']  = '';
                $row['BARCODE-10']  = '';



                fputcsv($file, array(
                $row['STYLE'] ,
                $row['COLOUR'] ,
                $row['SIZEVAL'],
                $row['EANTYPE-1'],
                $row['BARCODE-1'],
                $row['EANTYPE-2'],
                $row['BARCODE-2'],
                $row['EANTYPE-3'],
                $row['BARCODE-3'],
                $row['EANTYPE-4'],
                $row['BARCODE-4'],
                $row['EANTYPE-5'],
                $row['BARCODE-5'],
                $row['EANTYPE-6'],
                $row['BARCODE-6'],
                $row['EANTYPE-7'],
                $row['BARCODE-7'],
                $row['EANTYPE-8'],
                $row['BARCODE-8'],
                $row['EANTYPE-9'],
                $row['BARCODE-9'],
                $row['EANTYPE-10'],
                $row['BARCODE-10'],
                ));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function salesTransactionExport(){
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=SALES.CSV",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
        $columns = array('External Reference Number','Customer Order Number','Customer Code','Bill To','Distribution Centre','Order Date','Due Date',
        'Cancel Date','SO Type','Barcode','Style Code','Colour Code',
        'Size Code','Price','Quantity','SOrder Reference 1','SOrder Reference 2','SOrder Reference 3','SOrder Reference 4','SOrder Reference 5','SOrder Reference 6','SOrder Reference 7','SOrder Reference 8','SOrder Reference 9','SOrder Reference 10',
        'Special Instructions','Delivery Instructions','Order Notes','Discount Percentage','Warehouse Code'
        );

        $columns2 = array('','receipt','Customer Code','Customer Code','','Cash Sale Date','','','','Barcode','Style Code'
        ,'Colour Code','Size Code','','Quantity','','','','','','',
        '',''
        );

        $currendDate = date("Y/m/d",strtotime("-6 months"));
         

        $data = $this->cashSales
                ->join("Stock Detail", "Stock Detail.ID", "cash sale.detail id")
                ->join("Sales by Period", "Sales by Period.Style/Colour/Size ID", "Stock Detail.Style/Colour/Size ID")
                ->join("Style Colour Size", "Style Colour Size.ID", "Sales by Period.Style/Colour/Size ID")
                ->join("Stock Style", "Stock Style.ID", "Style Colour Size.Style ID")
                ->join("Stock Colour", "Style Colour Size.Colour ID", "Stock Colour.ID")
                ->join("Stock Size", "Style Colour Size.Size ID", "Stock Size.ID")
                ->join("Barcode", "Style Colour Size.ID", "Barcode.Style/Colour/Size ID")
                ->join("Customer", "cash sale.customer id", "Customer.ID")
                ->select(["cash sale.Date as cashSaleDate","cash sale.*","Sales by Period.*","Stock Style.Code as productCode","Stock Colour.Code as colourCode","Stock Size.Code as sizeCode","Barcode.As Number as barcode","Customer.Code as customerCode"])
                ->whereDate('cash sale.date','>=', $currendDate)
                ->whereDate('Sales by Period.date','>=', $currendDate)
                ->orderBy("cash sale.date",'desc')
                ->limit(20000)
                ->get();
        // dd($data);
        // ->select(["Stock Style.*","Style Colour Size.*","Stock Style.Code as productCode","Stock Style.ID as productID",
        // "Stock Colour.Code as colourCode","Stock Colour.Code as colourDes","Stock Size.Code as sizeCode","Stock Size.Code as sizeDes",
        // "Barcode.As Number as barCode", "Stock Detail.*"
        // ])
        // ->select(["Stock Style.*", "Style Colour Size.*","Stock Size.Code as sizeCode","Stock Size.Order as sizeOrder"])
        // ->where("Stock Style.Code", "<>","")
        // ->where("Stock Style.ID", $matrix["Style ID"])
        // ->where("Stock Detail.Branch ID", 1)
        // ->orderBy("Stock Size.Order", 'asc')
        // ->toSql();
        // ->limit(20)
        // ->get();

        // dd($data);
        $callback = function() use($data, $columns, $columns2) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $columns2);

            foreach ($data as $task) {
                $row['External Reference Number']  = '';
                $row['Customer Order Number']  = $task['receipt'];
                $row['Customer Code']  = $task['customerCode'];
                $row['Bill To']  = $task['customerCode'];
                $row['Distribution Centre']  = '';
                $row['Order Date']  = $task['cashSaleDate'];
                $row['Due Date']  = '';
                $row['Cancel Date']  = '';
                $row['SO Type']  = '';
                $row['Barcode']  = $task['barcode'];
                $row['Style Code']  = $task['productCode'];
                $row['Colour Code']  = $task['colourCode'];
                $row['Size Code']  = $task['sizeCode'];
                $row['Price']  = '';
                $row['Quantity']  = $task['quantity'];
                $row['SOrder Reference 1']  = '';
                $row['SOrder Reference 2']  = '';
                $row['SOrder Reference 3']  = '';
                $row['SOrder Reference 4']  = '';
                $row['SOrder Reference 5']  = '';
                $row['SOrder Reference 6']  = '';
                $row['SOrder Reference 7']  = '';
                $row['SOrder Reference 8']  = '';
                $row['SOrder Reference 9']  = '';
                $row['SOrder Reference 10']  = '';
                $row['Special Instructions']  = '';
                $row['Delivery Instructions']  = '';
                $row['Order Notes']  = '';
                $row['Discount Percentage']  = '';
                $row['Warehouse Code']  = '';




                fputcsv($file, array(
                    $row['External Reference Number'] ,
                    $row['Customer Order Number']  ,
                    $row['Customer Code']  ,
                    $row['Bill To']  ,
                    $row['Distribution Centre']  ,
                    $row['Order Date']  ,
                    $row['Due Date']  ,
                    $row['Cancel Date']  ,
                    $row['SO Type']  ,
                    $row['Barcode']  ,
                    $row['Style Code']  ,
                    $row['Colour Code']  ,
                    $row['Size Code']  ,
                    $row['Price']  ,
                    $row['Quantity']  ,
                    $row['SOrder Reference 1']  ,
                    $row['SOrder Reference 2']  ,
                    $row['SOrder Reference 3']  ,
                    $row['SOrder Reference 4']  ,
                    $row['SOrder Reference 5']  ,
                    $row['SOrder Reference 6']  ,
                    $row['SOrder Reference 7']  ,
                    $row['SOrder Reference 8']  ,
                    $row['SOrder Reference 9']  ,
                    $row['SOrder Reference 10']  ,
                    $row['Special Instructions']  ,
                    $row['Delivery Instructions']  ,
                    $row['Order Notes']  ,
                    $row['Discount Percentage']  ,
                    $row['Warehouse Code']  ,
                ));
            }

            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
