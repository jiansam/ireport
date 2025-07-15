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
            $grid->column('id')->sortable();
            $grid->column('member_id');
            $grid->column('status');
            $grid->column('price');
            $grid->column('name');
            $grid->column('phone');
            $grid->column('email');
            $grid->column('address');
            $grid->column('pay_type');
            $grid->column('point');
            $grid->column('plan');
            $grid->column('period');
            $grid->column('invoice_id');
            $grid->column('memo');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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
            $form->text('member_id');
            $form->text('status');
            $form->text('price');
            $form->text('name');
            $form->text('phone');
            $form->text('email');
            $form->text('address');
            $form->text('pay_type');
            $form->text('point');
            $form->text('plan');
            $form->text('period');
            $form->text('invoice_id');
            $form->text('memo');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
