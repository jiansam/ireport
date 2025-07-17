<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Order(), function (Grid $grid) {

            $grid->export();
            
            $grid->column('created_at')->sortable();
            $grid->column('transaction_id', '金流交易編號'); // 待補
            $grid->column('order_status', '訂單狀態'); // 待補
            $grid->column('member_name', '會員名稱')->display(function () {
                return $this->member ? $this->member->name : '';
            });
            
            $grid->column('pay_type', '付費方式')->using([
                Order::PAY_TYPE_ONE_GREEN      => '綠界單次',
                Order::PAY_TYPE_PERIOD_GREEN   => '綠界週期',
                Order::PAY_TYPE_ONE_PAYPAL     => 'Paypal單次',
                Order::PAY_TYPE_PERIOD_PAYPAL  => 'Paypal週期',
            ]);


            $grid->column('total_price', '總金額'); // 待補
            $grid->column('invoice_id'); // 待確認
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('transaction_id', '金流交易編號');
                $filter->equal('order_status', '訂單狀態'); // 待補
                $filter->like('member_name', '會員名稱');
                $filter->equal('pay_type', '付費方式')->select([
                    Order::PAY_TYPE_ONE_GREEN      => '綠界單次',
                    Order::PAY_TYPE_PERIOD_GREEN   => '綠界週期',
                    Order::PAY_TYPE_ONE_PAYPAL     => 'Paypal單次',
                    Order::PAY_TYPE_PERIOD_PAYPAL  => 'Paypal週期',
                ]);
                $filter->between('total_price', '總金額');
                $filter->like('invoice_id', '發票編號');
                $filter->between('created_at', '建立時間')->datetime();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Order(), function (Show $show) {
            $show->field('id');
            $show->field('member_id');
            $show->field('status');
            $show->field('price');
            $show->field('name');
            $show->field('phone');
            $show->field('email');
            $show->field('address');
            $show->field('pay_type');
            $show->field('point');
            $show->field('plan');
            $show->field('period');
            $show->field('invoice_id');
            $show->field('memo');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Order(), function (Form $form) {
            $form->display('id');
            $form->text('member_id')->required();
            $form->select('status')->options([
                Order::STATUS_PAY => '付款',
                Order::STATUS_NOT_PAY => '未付款',
                Order::STATUS_AUTHORIZE => '授權',
                Order::STATUS_NOT_AUTHORIZE => '尚未授權',
                Order::STATUS_EXPIRED => '逾期',
                Order::STATUS_FAIL => '付款失敗',
            ])->default(Order::STATUS_PAY);

            $form->select('price')
                ->options([
                    Order::PLAN_NORMAL_PRICE_1 => '基礎方案-495',
                    Order::PLAN_NORMAL_PRICE_2 => '基礎方案-999',
                    Order::PLAN_NORMAL_PRICE_3 => '基礎方案-9900',
                    Order::PLAN_HIGHT_PRICE_1 => '高用量-750',
                    Order::PLAN_HIGHT_PRICE_2 => '高用量-1500',
                    Order::PLAN_HIGHT_PRICE_3 => '高用量-15000',
                ])
                ->default(Order::PLAN_NORMAL_PRICE_1)
                ->required()
                ->help('請選擇金額');

            $form->text('name')->required();
            
            $form->text('phone')
                ->required()
                ->help('請輸入正確電話號碼(7~20字元,可含 +、-、空白、括號)');

            $form->text('email')->required()->help('請輸入正確 Email');
            $form->text('address')->required();
            
            $form->select('pay_type')->options([
                Order::PAY_TYPE_ONE_GREEN => '綠界單次',
                Order::PAY_TYPE_PERIOD_GREEN => '綠界週期',
                Order::PAY_TYPE_ONE_PAYPAL => 'Paypal單次',
                Order::PAY_TYPE_PERIOD_PAYPAL => 'Paypal週期',
            ])->default(Order::PAY_TYPE_ONE_GREEN);
            
            $form->text('point')->required()->help('請輸入點數（整數）');
            
            $form->select('plan')->options([
                Order::PLAN_POINT => '單次方案',
                Order::PLAN_NORMAL => '基礎方案',
                Order::PLAN_HIGHT => '高用量',
            ])->default(Order::PLAN_POINT);;
            
            $form->select('period')->options([
                'M' => '月(M)',
                'Y' => '年(Y)',
            ])->required()->help('請選擇訂購期間：月(M) 或 年(Y)');

            $form->text('invoice_id')
                ->help('格式:2碼英文+8碼數字,例如 AB12345678，可空');

            $form->textarea('memo')->rows(5)->help('可輸入多行備註');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
