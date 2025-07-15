<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;

class TestController extends Controller
{

    protected $checkout;

    public function __construct(Checkout $checkout)
    {
        $this->checkout = $checkout;
    }

    public function ecpay(Request $request)
    {


        $formData = [
            'UserId' => 2, // 用戶ID , Optional
            'ItemDescription' => '產品簡介',
            'ItemName' => '測試商品',
            'TotalAmount' => '10',
            'PaymentMethod' => 'Credit', // ALL, Credit, ATM, WebATM
            "CustomField1"=>"", //自填入ID
        ];

        $callback = url("test/ecpay/callback");
        return $this->checkout->setReturnUrl($callback)->setPostData($formData)->send();
    }

    public function ecpayCallback(Request $request){
       /**
        * array:17 [▼ /
          "CustomField1" => null
          "CustomField2" => null
          "CustomField3" => null
          "CustomField4" => null
          "MerchantID" => "3002607"
          "MerchantTradeNo" => "O175254249240136158"
          "PaymentDate" => "2025/07/15 09:26:29"
          "PaymentType" => "Credit_CreditCard"
          "PaymentTypeChargeFee" => "2"
          "RtnCode" => "1"
          "RtnMsg" => "Succeeded"
          "SimulatePaid" => "0"
          "StoreID" => null
          "TradeAmt" => "10"
          "TradeDate" => "2025/07/15 09:21:32"
          "TradeNo" => "2507150921330121"
          "CheckMacValue" => "205AC832F1F59CCC56523F98EFED50FBC100A84A1CBE6D8BC062C9CDA7F039B3"
        ]
        */
        $post = $request->post();
        if ($post["RtnCode"] == "1") { //成功
            $id = $post["CustomField1"];
        } else { //失敗

        }
        //dd($request , $post);
    }

}
