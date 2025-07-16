<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * @desc
     * 綠界金流信用卡訂閱
     *
     * @method POST
     * @param Request $request
     * @param member_id 用戶ID
     * @param plan   方案 1|2|3
     * @param point  方案一 需帶入point點數
     * @param period 方案二、三 帶入訂閱 月或年 參數 M/Y
     * @param memo 備註
     *
     * @example
     *  1.一次性訂閱
     *  http://127.0.0.1/ireport/ecpay?member_id=79414369368760e7e1c086&plan=1&point=10&memo=test
     *  2.月訂閱
     *  http://127.0.0.1/ireport/ecpay?member_id=79414369368760e7e1c086&period=M&memo=test
     */
    public function ecpay(Request $request)
    {
        $member = Member::find($request->member_id);

        if ($member == null) {
            abort(403 , "錯誤，找不到會員帳號。");
        }

        if (!$request->plan){
            abort(403 , "參數錯誤，Plan為必需值");
        }else if ($request->plan == 1 && !point) {
            abort(403 , "參數錯誤，Plan一次性付款，需帶入point");
        } else if (($request->plan == 2 || $request->plan =3)  && !period) {
            abort(403 , "參數錯誤，Plan訂閱制，需帶入period");
        }

        $order = new Order();
        $order->member_id = $request->member_id;
        $order->name =  $member->name;
        $order->phone = $member->phone;
        $order->email = $member->email;
        $order->address = $member->address;

        $order->plan  = $request->plan;
        $order->memo = $request->memo;
        $order->period = $request ->period;
        $order->point =  0;

        $callback = url("ecpay/callback");
        $itemDescription ="";
        $itemName ="";

        //找尋是否有首購
        $orderFirstFn = function() use($request) {
            return  Order::where("member_id" ,$request->member_id)
            ->whereIn("plan", [Order::PLAN_NORMAL , Order::PLAN_HIGHT])
            ->where("status" , Order::STATUS_AUTHORIZE)->count();
        };

        switch ($order->plan) {
            case Order::PLAN_POINT :
                $itemName ="單次方案";
                $itemDescription ="Pay-per-report (單次方案)";
                $order->pay_type = Order::PAY_TYPE_ONE_GREEN;
                $order->status = Order::STATUS_NOT_PAY;
                $order->point =  $request->point;
                $order->price = $request->point * 10;
                break;
            case Order::PLAN_NORMAL :
                $itemName ="基礎方案";
                $itemDescription ="基礎方案 訂閱費";
                $order->pay_type = Order::PAY_TYPE_PERIOD_GREEN;
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
                $order->pay_type = Order::PAY_TYPE_PERIOD_GREEN;
                $order->status = Order::STATUS_NOT_AUTHORIZE;

                if ($order->period  =="M") {
                    $order->price = $orderFirstFn() ? Order::PLAN_HIGHT_PRICE_2 : Order::PLAN_HIGHT_PRICE_1;
                } else {
                    $order->price =  Order::PLAN_HIGHT_PRICE_3;
                }
                break;
            default:
                abort(403 ,"錯誤方案類別($request->plan)");
        }

        $order->save();

        $formData = [
            'UserId' => $request->member_id, // 用戶ID , Optional
            'ItemDescription' =>$itemDescription,
            'ItemName' => $itemName,
            'TotalAmount' =>  $order->price,
            'PaymentMethod' => 'Credit', // ALL, Credit, ATM, WebATM
            "CustomField1"=> $order->id,
        ];
        Log::info("save order:" ,$order);
        Log::info("ECPay send order:" ,$formData);
        return $this->checkout->setReturnUrl($callback)->setPostData($formData)->send();
    }


    /**
     *綠界回clll
     *
     *會傳參數
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
    public function ecpayCallback(Request $request){
        $post = $request->post();
        Log::info('ECPay callback:', $post);


        if (!isset($post["RtnCode"])) {
            Log::error('ECPay callback missing RtnCode');
            return;
        }

        if ($post["RtnCode"] == "1") { //成功
            $orderId = $post["CustomField1"];

            $order = Order::find($orderId);
            $member = $order->member;

            $member->order_id = $order->id; //綁定現行訂單

            $order->trade_no = $post["TradeNo"];
            $order->pay_date =  $post['PaymentDate'];
            switch ($order->plan) {
                case Order::PLAN_POINT :
                    $order->status = Order::STATUS_PAY;
                    $member->status = Member::STATUS_PAY;
                    $member->start_time =null;
                    $member->end_time =null;
                    $member->point += $order->point;
                    break;
                case Order::PLAN_NORMAL :
                case Order::PLAN_HIGHT:
                    $order->status = Order::STATUS_AUTHORIZE;
                    if ($order->period  =="M") {
                        $order->status = Order::STATUS_AUTHORIZE;
                        $member->status = Member::STATUS_MONTH;
                        $member->start_time =  date('Y-m-d');
                        $member->end_time = date('Y-m-d', strtotime( $member->start_time . ' +1 month +2 day'));
                    } else {
                        $order->status = Order::STATUS_AUTHORIZE;
                        $member->status = Member::STATUS_YEAR;
                        $member->start_time = date('Y-m-d');
                        $member->end_time =date('Y-m-d', strtotime( $member->start_time . ' +1 year +2 day'));
                    }
                    break;
            }

            DB::transaction(function () use ($order, $member) {
                $order->save();
                $member->save();
            });

            Log::info("ECPay callback done TradeNo:$order->trade_no");
        } else {
            // 付款失敗處理
            $orderId = $post["CustomField1"] ?? null;

            if ($orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->status = Order::STATUS_FAIL;
                    $order->save();
                    Log::warning("ECPay callback：付款失敗訂單 ID: $orderId");
                }
            }
        }
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
