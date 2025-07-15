<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use TsaiYiHua\ECPay\Checkout;
use TsaiYiHua\ECPay\Invoice;
use TsaiYiHua\ECPay\Constants\ECPayDonation;
use TsaiYiHua\ECPay\Services\StringService;
use App\Models\Member;

class EcpayController extends Controller
{

    protected $checkout;
    protected $invoice;
    public function __construct(Checkout $checkout, Invoice $invoice)
    {
        $this->checkout = $checkout;
        $this->invoice = $invoice;
    }

    /**
     * user_id 用戶ID
     * plan 方案一 需帶入point點數
     * memo 備註
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|array
     */
    public function ecpay(Request $request)
    {

        $member = Member::find($request->user_id);

        if ($member == null) {
            abort(500 , "Error member not found!");
        }

        $order = new Order();
        $order->user_id = $request->user_id;
        $order->status = Order::STATUS_NOT_PAY;
        $order->name =  $member->name;
        $order->phone = $member->phone;
        $order->email = $member->email;
        $order->addr = $member->addr;
        $order->pay_type = Order::PAY_TYPE_GREEN;
        $order->plan  = $request->plan;
        $order->memo = $request->memo;

        $orderFirstCount = Order::where("user_id" ,$request->user_id)
                         ->whereIn("plan", [Order::PLAN_NORMAL , Order::PLAN_HIGHT])
                         ->where("status" , Order::STATUS_PAY)->count();

        $callback = url("ecpay/callback");
        $itemDescription ="";
        $itemName ="";

        switch ($order->plan) {
            case Order::PLAN_POINT :
                $itemName ="單次方案";
                $itemDescription ="Pay-per-report (單次方案)";
                $order->point =  $request->point;
                $order->price = $request->point * 10;
                break;
            case Order::PLAN_NORMAL :
                $itemName ="基礎方案";
                $itemDescription ="基礎方案";
                $order->point =  0;
                $order->price = $orderFirstCount ? Order::PLAN_NORMAL_PRICE_2 : Order::PLAN_NORMAL_PRICE_1;
                break;
            case Order::PLAN_HIGHT:
                $itemName ="高用量方案";
                $itemDescription ="高用量方案";
                $order->point =  0;
                $order->price = $orderFirstCount ? Order::PLAN_NORMAL_HIGHT_2 : Order::PLAN_NORMAL_HIGHT_1;
                break;
            default:
                abort(500 ,"Error plan type {$request->plan}.");
        }

        $order->save();

        $formData = [
            'UserId' => $request->user_id, // 用戶ID , Optional
            'ItemDescription' =>$itemDescription,
            'ItemName' => $itemName,
            'TotalAmount' =>  $order->price,
            'PaymentMethod' => 'Credit', // ALL, Credit, ATM, WebATM
            "CustomField1"=>"", $order->id
        ];


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

    /**
    * 開立發票
    */
    public function invoice(Request $request)
    {
        $itemData[] = [
            'name' => 'product name',
            'qty' => 1,
            'unit' => 'piece',
            'price' => 5000
        ];
        $invData = [
            'UserId' => 1,
            'Items' => $itemData,
            'CustomerName' => 'User Name',
            'CustomerEmail' => 'email@address.com',
            'CustomerPhone' => '0912345678',
            'OrderId' => StringService::identifyNumberGenerator('O'),
            'Donation' => ECPayDonation::Yes,
            'LoveCode' => 168001,
            'Print' => 0,
            'CarruerType' => 1
        ];
        return $this->invoice->setPostData($invData)->send();
    }



}
