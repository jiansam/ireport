<?php

namespace App\Admin\Controllers;

use App\Models\Member;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class MemberController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Member(), function (Grid $grid) {
            $grid->column('id');
            $grid->column('name');
            $grid->column('phone');
            $grid->column('email');
            $grid->column('address');
            $grid->column('carrier_num');
            $grid->column('tax_id');
            $grid->column('account');
            $grid->column('google_id');
            $grid->column('status')->using([
                Member::STATUS_TEST   => '試用期',
                Member::STATUS_YEAR   => '年訂閱',
                Member::STATUS_MONTH  => '月訂閱',
                Member::STATUS_PAY    => '已購買',
                Member::STATUS_FREE   => '一般免費',
            ]);
            $grid->column('point');
            $grid->column('login_time');
            $grid->column('start_time');
            $grid->column('end_time');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name', '姓名');
                $filter->like('phone', '電話');
                $filter->like('email', 'Email');

                $filter->equal('status', '狀態')->select([
                    Member::STATUS_TEST   => '試用期',
                    Member::STATUS_YEAR   => '年訂閱',
                    Member::STATUS_MONTH  => '月訂閱',
                    Member::STATUS_PAY    => '已購買',
                    Member::STATUS_FREE   => '一般免費',
                ]);
                $filter->between('start_time', '開始時間')->datetime();
                $filter->between('end_time', '結束時間')->datetime();
                $filter->between('created_at', '註冊時間')->datetime();
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
        return Show::make($id, new Member(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('phone');
            $show->field('email');
            $show->field('address');
            $show->field('carrier_num');
            $show->field('tax_id');
            $show->field('account');
            $show->field('password');
            $show->field('google_id');
            $show->field('status');
            $show->field('point');
            $show->field('login_time');
            $show->field('start_time');
            $show->field('end_time');
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
        return Form::make(new Member(), function (Form $form) {
            $form->display('id');
            $form->text('name')->required();
            $form->text('phone')
                ->required()
                ->help('請輸入正確電話號碼(7~20字元,可含 +、-、空白、括號)');

            $form->text('email')->rules('required|email')->required();
            $form->text('address')->required();
            $form->text('carrier_num');

            $form->text('tax_id')
                ->rules('nullable|integer|min:10000000|max:99999999')
                ->help('統一編號請輸入 8 碼數字（可空）');
            
            $form->text('account')
                ->rules(['required', 'regex:/^[A-Za-z0-9\x{4e00}-\x{9fa5}]{6,20}$/u']);

            $pwdRules = request()->route()->getActionMethod() === 'store'
                ? 'required|min:6'
                : 'nullable|min:6';

            $form->password('password')
                ->customFormat(function () {
                    return '';
                })
                ->rules($pwdRules)
                ->help('如需修改密碼請輸入新密碼，否則請留空');

            $form->saving(function (Form $form) {
                $password = $form->password;
                $form->deleteInput('password');
                if ($password) {
                    if (\Illuminate\Support\Str::startsWith($password, '$2y$') === false) {
                        $form->password = bcrypt($password);
                    } else {
                        $form->password = $password;
                    }
                }
                if ($form->start_time && $form->end_time && $form->start_time > $form->end_time) {
                    throw new \Exception('開始時間不能大於結束時間');
                }
            });

            $form->text('google_id');
            $form->select('status')->options([
                Member::STATUS_TEST   => '試用期',
                Member::STATUS_YEAR   => '年訂閱',
                Member::STATUS_MONTH  => '月訂閱',
                Member::STATUS_PAY    => '已購買',
                Member::STATUS_FREE   => '一般免費',
            ])->default(Member::STATUS_TEST)
                ->required();
            $form->text('point');
            $form->datetime('login_time')->required();
            $form->datetime('start_time')->required();
            $form->datetime('end_time')->required();
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
