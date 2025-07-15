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
     * plan   方案 1|2|3
     * point  方案一 需帶入point點數
     * period 方案二、三 帶入訂閱 月或年 參數 M/Y
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
        $order->name =  $member->name;
        $order->phone = $member->phone;
        $order->email = $member->email;
        $order->addr = $member->addr;
        $order->pay_type = Order::PAY_TYPE_GREEN;
        $order->plan  = $request->plan;
        $order->memo = $request->memo;
        $order->period = $request ->period;
        $order->point =  0;



        $callback = url("ecpay/callback");
        $itemDescription ="";
        $itemName ="";

        //找尋是否有首購
        $orderFirstFn = function() use($request) {
           return  Order::where("user_id" ,$request->user_id)
            ->whereIn("plan", [Order::PLAN_NORMAL , Order::PLAN_HIGHT])
            ->where("status" , Order::STATUS_AUTHORIZE)->count();
        };

        switch ($order->plan) {
            case Order::PLAN_POINT :
                $itemName ="單次方案";
                $itemDescription ="Pay-per-report (單次方案)";
                $order->status = Order::STATUS_NOT_PAY;
                $order->point =  $request->point;
                $order->price = $request->point * 10;
                break;
            case Order::PLAN_NORMAL :
                $itemName ="基礎方案";
                $itemDescription ="基礎方案 訂閱費";
                $order->status = Order::STATUS_NOT_AUTHORIZE;

                if ($order->period  =="M") {//月訂閱 首購優惠
                    //找尋是否有訂閱 無訂購首月折購
                    $order->price = $orderFirstFn()? Order::PLAN_NORMAL_PRICE_2 : Order::PLAN_NORMAL_PRICE_1;
                } else { //年訂閱
                    $order->price =  Order::PLAN_NORMAL_PRICE_3;
                }
                break;
            case Order::PLAN_HIGHT:
                $itemName ="高用量方案";
                $itemDescription ="高用量方案 訂閱費";
                $order->status = Order::STATUS_NOT_AUTHORIZE;

                if ($order->period  =="M") {
                    $order->price = $orderFirstFn() ? Order::PLAN_HIGHT_PRICE_2 : Order::PLAN_HIGHT_PRICE_1;
                } else {
                    $order->price =  Order::PLAN_HIGHT_PRICE_3;
                }
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

    /*
     * FirstAmount ：首期收款金額（如首購優惠價）

    PeriodAmount ：後續每期金額

    PeriodType ：週期型態（D=天、M=月、Y=年）

    Frequency ：幾個單位週期進行一次扣款（如每1月）

    ExecTimes ：期數，設大一點即可視為長期訂
     */
    public function subscribe()
    {
        $formData = [
            'UserId' => 1,
            'ItemDescription' => '訂閱會員首購優惠',
            'ItemName' => '會員訂閱費',
            'TotalAmount' => 599,    // 後續每期金額
            'PaymentMethod' => 'Credit',
        ];
        $periodAmt = [
            'PeriodAmount'  => 599,
            'PeriodType'    => 'M',   // M=月
            'Frequency'     => 1,     // 每1個月
            'ExecTimes'     => 12,    // 期數
            'PeriodReturnURL' => route('ecpay.period_return'),
            'FirstAmount'   => 499,   // 首期金額
        ];
        return $this->checkout->setPostData($formData)->withPeriodAmount($periodAmt)->send();
    }

    public function sendOrderWithInvoice()
    {
        $items = [[
            'name' => '產品333',
            'qty'  => '3',
            'unit' => '個',
            'price'=> '150'
        ]];
        $formData = [
            'itemDescription' => '產品簡介',
            'items'           => $items,
            'paymentMethod'   => 'Credit',
            'userId'          => 1
        ];
        $invData = [
            'Items'        => $items,
            'UserId'       => 1,
            'CustomerName' => 'User Name',
            'CustomerAddr' => 'ABC Road',
            'CustomerEmail'=> 'email@address.com'
        ];
        return $this->checkout->setPostData($formData)->withInvoice($invData)->send();
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

    public function sendOrderWithInvoice2()
    {
        $items = [[
            'name' => '產品333',
            'qty'  => '3',
            'unit' => '個',
            'price'=> '150'
        ]];
        $formData = [
            'itemDescription' => '產品簡介',
            'items'           => $items,
            'paymentMethod'   => 'Credit',
            'userId'          => 1
        ];
        $invData = [
            'Items'        => $items,
            'UserId'       => 1,
            'CustomerName' => 'User Name',
            'CustomerAddr' => 'ABC Road',
            'CustomerEmail'=> 'email@address.com'
        ];
        return $this->checkout->setPostData($formData)->withInvoice($invData)->send();
    }




}
