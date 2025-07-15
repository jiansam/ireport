<?php 
return [
    'labels' => [
        'Order' => '訂單',
        'order' => '訂單',
    ],
    'fields' => [
        'member_id' => '會員編號',
        'status' => '已付款、已授權、授權失敗、逾期未付、付款失敗',
        'price' => '付費金額',
        'name' => '購買人姓名',
        'phone' => '電話',
        'email' => 'email',
        'address' => '地址',
        'pay_type' => '0:paypal, 1:綠界',
        'point' => '點數',
        'plan' => '1:單次方案, 2:基礎方案, 3:高用量',
        'period' => '訂購月或年  M|Y',
        'invoice_id' => '發票編號',
        'memo' => '備註',
    ],
    'options' => [
    ],
];
