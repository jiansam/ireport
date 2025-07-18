<?php
namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TsaiYiHua\ECPay\Checkout;
use TsaiYiHua\ECPay\Invoice;
use TsaiYiHua\ECPay\Constants\ECPayCarruerType;
use TsaiYiHua\ECPay\Constants\ECPayDonation;

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
     *
     * 綠界金流信用卡訂閱
     *
     * @method POST
     * @param Request $request
     * @param member_id 用戶ID
     * @param plan 方案 1|2|3
     * @param point 方案一 需帶入point點數
     * @param period 方案二、三 帶入訂閱 月或年 參數 M/Y
     * @param invoice_type 2:二聯式發票, 3:三聯式發票
     * @param invoice_title 買受人title
     * @param invoice_uniform_number 買受人統編
     *
     * @example 1.一次性訂閱
     *          http://127.0.0.1/ireport/ecpay?member_id=8879a65b8a6b68932928&plan=1&point=10&invoice_type=2
     *
     *          2.月訂閱
     *          http://127.0.0.1/ireport/ecpay?member_id=8879a65b8a6b68932928&period=M&invoice_type=2
     *
     *          測試卡號
     *          4311-9522-2222-2222
     */
    public function ecpay(Request $request)
    {
        $member = Member::find($request->member_id);

        if ($member == null) {
            abort(403, "錯誤，找不到會員帳號。");
        }

        if (! $request->plan) {
            abort(403, "參數錯誤，Plan為必需值");
        } else if ($request->plan == 1 && ! $request->point) {
            abort(403, "參數錯誤，Plan一次性付款，需帶入point");
        } else if (($request->plan == 2 || $request->plan == 3) && ! $request->period) {
            abort(403, "參數錯誤，Plan訂閱制，需帶入period");
        }

        $order = new Order();
        $order->no = date('YmdHis') . rand(1000, 9999);
        $order->member_id = $request->member_id;
        $order->name = $member->name;
        $order->phone = $member->phone;
        $order->email = $member->email;
        $order->address = $member->address;
        $order->plan = $request->plan;
        $order->memo = $request->memo;
        $order->period = $request->period;
        $order->invoice_title = $request->invoice_title;
        $order->invoice_uniform_number = $request->invoice_uniform_number;
        $order->invoice_type = $request->invoice_type;

        $order->point = 0;

        $callback = url("ecpay/callback");

        $itemDescription = "";
        $itemName = "";

        // 找尋是否有首購
        $orderFirstFn = function () use ($request) {
            return Order::where("member_id", $request->member_id)->whereIn("plan", [
                Order::PLAN_NORMAL,
                Order::PLAN_HIGHT
            ])
                ->where("status", Order::STATUS_AUTHORIZE)
                ->count();
        };

        switch ($order->plan) {
            case Order::PLAN_POINT:
                $itemName = "單次點數方案";
                $itemDescription = "Pay-per-report (單次方案)";
                $order->pay_type = Order::PAY_TYPE_ONE_GREEN;
                $order->status = Order::STATUS_NOT_PAY;
                $order->point = $request->point;
                $order->price = $request->point * 10;
                $order->title =$itemName;
                break;
            case Order::PLAN_NORMAL:
                $itemName = "基礎方案";
                $itemDescription = "基礎方案 訂閱費";
                $order->pay_type = Order::PAY_TYPE_PERIOD_GREEN;
                $order->status = Order::STATUS_NOT_AUTHORIZE;
                $order->title =$itemName;

                if ($order->period == "M") { // 月訂閱 首購優惠
                                             // 找尋是否有訂閱 無訂購首月折購
                    $order->price = $orderFirstFn() ? Order::PLAN_NORMAL_PRICE_2 : Order::PLAN_NORMAL_PRICE_1;
                } else { // 年訂閱
                    $order->price = Order::PLAN_NORMAL_PRICE_3;
                }
                break;
            case Order::PLAN_HIGHT:
                $itemName = "高用量方案";
                $itemDescription = "高用量方案 訂閱費";
                $order->pay_type = Order::PAY_TYPE_PERIOD_GREEN;
                $order->status = Order::STATUS_NOT_AUTHORIZE;
                $order->title =$itemName;

                if ($order->period == "M") {
                    $order->price = $orderFirstFn() ? Order::PLAN_HIGHT_PRICE_2 : Order::PLAN_HIGHT_PRICE_1;
                } else {
                    $order->price = Order::PLAN_HIGHT_PRICE_3;
                }
                break;
            default:
                abort(403, "錯誤方案類別($request->plan)");
        }

        $order->save();

        $formData = [
            'MerchantTradeNo' => $order->no,
            'UserId' => $request->member_id, // 用戶ID , Optional
            'ItemDescription' => $itemDescription,
            'ItemName' => $itemName,
            'TotalAmount' => $order->price,
            'PaymentMethod' => 'Credit', // ALL, Credit, ATM, WebATM
            "CustomField1" => $order->id
        ];

        Log::info("save order:", $order->toArray());
        Log::info("ECPay send order:", $formData);
        return $this->checkout->setReturnUrl($callback)->setPostData($formData)->send();
    }



    /**
     * 綠界回clll
     *
     * 會傳參數
     * array:17 [▼ /
     * "CustomField1" => null
     * "CustomField2" => null
     * "CustomField3" => null
     * "CustomField4" => null
     * "MerchantID" => "3002607"
     * "MerchantTradeNo" => "O175254249240136158"
     * "PaymentDate" => "2025/07/15 09:26:29"
     * "PaymentType" => "Credit_CreditCard"
     * "PaymentTypeChargeFee" => "2"
     * "RtnCode" => "1"
     * "RtnMsg" => "Succeeded"
     * "SimulatePaid" => "0"
     * "StoreID" => null
     * "TradeAmt" => "10"
     * "TradeDate" => "2025/07/15 09:21:32"
     * "TradeNo" => "2507150921330121"
     * "CheckMacValue" => "205AC832F1F59CCC56523F98EFED50FBC100A84A1CBE6D8BC062C9CDA7F039B3"
     * ]
     */
    public function callback(Request $request)
    {
        $post = $request->post();

        Log::info('ECPay callback:', $post);

        if (! isset($post["RtnCode"])) {
            Log::error('ECPay callback missing RtnCode');
            return;
        }

        if ($post["RtnCode"] == "1") { // 成功
            $orderId = $post["CustomField1"];

            $order = Order::find($orderId);
            $member = $order->member;

            $member->order_id = $order->id; // 綁定現行訂單

            $order->trade_no = $post["TradeNo"];
            $order->pay_date = $post['PaymentDate'];
            switch ($order->plan) {
                case Order::PLAN_POINT:
                    $order->status = Order::STATUS_PAY;
                    $member->status = Member::STATUS_PAY;
                    $member->start_time = null;
                    $member->end_time = null;
                    $member->point += $order->point;
                    break;
                case Order::PLAN_NORMAL:
                case Order::PLAN_HIGHT:
                    if ($order->period == "M") {
                        $order->status = Order::STATUS_AUTHORIZE;
                        $member->status = Member::STATUS_MONTH;
                        $member->start_time = date('Y-m-d');
                        $member->end_time = date('Y-m-d', strtotime($member->start_time . ' +1 month +2 day'));
                    } else {
                        $order->status = Order::STATUS_AUTHORIZE;
                        $member->status = Member::STATUS_YEAR;
                        $member->start_time = date('Y-m-d');
                        $member->end_time = date('Y-m-d', strtotime($member->start_time . ' +1 year +2 day'));
                    }
                    break;
            }


            DB::transaction(function () use ($order, $member) {
                $order->save();
                $member->save();
            });


            //開立發票
            $this->sendInvoice($order);

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

    /**
     * 開立發票
     *
     * 參數 型別 必填 說明
     * MerchantID String(10) 是 特店編號。
     * RelateNumber String(50) 是 廠商自訂唯一編號，不可重複。
     * ChannelPartner String(1) 否 通路商編號；1=蝦皮，其餘值無效。
     * CustomerID String(20) 否 客戶編號（英文、數字、底線）。
     * ProductServiceID String(10) 否 產品服務代號（啟用多組字軌時使用）。
     * CustomerIdentifier String(8) 否 統一編號（8 碼數字）。
     * CustomerName String(60) 條件必填 買受人名稱；Print=1 時必填。
     * CustomerAddr String(100) 條件必填 買受人地址；Print=1 時必填。
     * CustomerPhone String(20) 條件必填 手機號碼；CustomerEmail 空時必填。
     * CustomerEmail String(80) 條件必填 電子郵件；CustomerPhone 空時必填。
     * ClearanceMark String(1) 條件必填 通關方式；TaxType=2 或 9 時必填（1=非經海關出口，2=經海關出口）。
     * Print String(1) 是 列印註記；0=不列印，1=列印。
     * Donation String(1) 是 捐贈註記；0=不捐贈，1=捐贈。
     * LoveCode String(7) 條件必填 捐贈碼；Donation=1 時必填。
     * CarrierType String(1) 否 載具類別；空字串/1/2/3/4/5（詳見載具使用規則）。
     * CarrierNum String(64) 條件必填 載具編號；依 CarrierType 而定，如手機條碼需先驗證後帶入8碼編號。
     * CarrierNum2 String(64) 條件必填 第二載具編號；`CarrierType=4
     * TaxType String(1) 是 課稅類別；1/2/3/4/9（依 InvType 而定）。
     * ZeroTaxRateReason String(2) 條件必填 零稅率原因；`TaxType=2
     * SpecialTaxType Number 條件必填 特種稅額類別；`TaxType=3
     * SalesAmount Number 是 發票總金額（含稅），整數（新台幣）。
     * TaxAmount Number 否 稅額合計；整數，未填由綠界計算（Stage環境測試中）。
     * InvoiceRemark String(200) 否 發票備註；目前限 100 字以內。
     * vat String(1) 否 價格是否含稅；1=含稅（預設）、0=未稅。
     * InvType String(2) 是 字軌類別；07=一般稅額發票，08=特種稅額發票。
     * Items Array<Object> 是（至少1） 商品明細陣列，最多支援 999 項，每項結構如下：
     * Items[*] 子項目說明
     * 子參數 型別 必填 說明
     * ItemSeq Int 是 商品序號，1–999
     * ItemName String(500) 是 商品名稱
     * ItemCount Number 是 商品數量（支援整數8位、小數7位）
     * ItemWord String(6) 是 商品單位
     * ItemPrice Number 是 商品單價（支援整數10位、小數7位）
     * ItemTaxType String(1) 否 商品課稅別；TaxType=9 時必填（1/2/3），其他情形可省略。
     * ItemAmount Number 是 商品含稅小計（12位整數、小數7位）
     * ItemRemark String(120) 否 商品備註
     */
    public function sendInvoice($order)
    {

        $qty = 0;
        $price = 0;
        switch ($order->plan) {
            case Order::PLAN_POINT:
                $qty = $order->point;
                $price = 10;
                break;
            case Order::PLAN_NORMAL:
                $qty = 1;
                $price = $order->price;
                break;
            case Order::PLAN_HIGHT:
                $qty = 1;
                $price = $order->price;
                break;
        }

        $items = [
            [
                "ItemSeq" => 1,
                'name' => $order->title,
                'qty' =>$qty,
                'unit' => '個',
                'price' => $price
            ]
        ];

        $invoiceData = [
            'OrderId' => $order->no,
            'Items' => $items,
            'UserId' => $order->member_id,
            'CustomerAddr' => $order->address,
            'CustomerEmail' => $order->email,
            "CustomerPhone" => $order->phone,
            'SalesAmount' => $order->price,
            "Print" => 0,
            'CarruerType' => ECPayCarruerType::Member,
            'Donation' => ECPayDonation::No
        ];

        if ($order->invoice_type ==2 ) {
            $invoiceData["CustomerName"] = $order->name ;
        } else {
            $invoiceData["CustomerName"] = $order->invoice_title ;
            $invoiceData["CustomerIdentifier"] = $order->invoice_uniform_number ;
        }

        Log::info("ECPay invoice send orderId : $order->no " , $invoiceData);

        $result = $this->invoice->setPostData($invoiceData)->send();

        Log::info("ECPay invoice result orderId : $order->no " , $result);

        if (! isset($result["RtnCode"])) {
            Log::error('ECPay invoice missing RtnCode');
            return;
        }

        if ($result["RtnCode"] == "1") { // 成功
            $model = new \App\Models\Invoice();
            $model->order_id = $order->id;
            $model->number = $result["InvoiceNumber"];
            $model->type = $order->invoice_type;
            $model->buyer_name =  $order->invoice_type ==2 ?$order->name :  $order->invoice_title ;
            $model->buyer_uniform_number= $order->invoice_type ==2 ?"": $order->invoice_uniform_number ; //買方統一編號
         // $model->carrier_type =""; //載具類型（如手機條碼、會員載具）
          //$model->carrier_number=""; //載具類型（如手機條碼、會員載具）
            $model->amount = $order->price;
            $model->random_number =  $result["RandomNumber"];
            $model->time=  $result["InvoiceDate"];
            $model->save();
        } else {
            Log::info("ECPay invoice result fail : $order->no " , $result);
        }
    }

    /*
     * FirstAmount ：首期收款金額（如首購優惠價）
     * PeriodAmount ：後續每期金額
     * PeriodType ：週期型態（D=天、M=月、Y=年）
     * Frequency ：幾個單位週期進行一次扣款（如每1月）
     * ExecTimes ：期數，設大一點即可視為長期訂
     */
    public function subscribe()
    {
        $formData = [
            'UserId' => 1,
            'ItemDescription' => '訂閱會員首購優惠',
            'ItemName' => '會員訂閱費',
            'TotalAmount' => 599, // 後續每期金額
            'PaymentMethod' => 'Credit'
        ];
        $periodAmt = [
            'PeriodAmount' => 599,
            'PeriodType' => 'M', // M=月
            'Frequency' => 1, // 每1個月
            'ExecTimes' => 12, // 期數
            'PeriodReturnURL' => route('ecpay.period_return'),
            'FirstAmount' => 499 // 首期金額
        ];
        return $this->checkout->setPostData($formData)
            ->withPeriodAmount($periodAmt)
            ->send();
    }

}
