<?php

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Form;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Show;

/**
 * Dcat-admin - admin builder based on Laravel.
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Dcat\Admin\Form::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Column::extend('php', PHPEditor::class);
 * Dcat\Admin\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

//數據表單初始化設定
Form::resolving(function (Form $form) {
    
    //禁止使用繼續編輯功能
    //$form->disableEditingCheck();
    
    //禁止使用繼續建立功能
    //$form->disableCreatingCheck();
    
    //禁止顯示(查看)資料詳情
    $form->disableViewCheck();
    
    $form->tools(function (Form\Tools $tools) {
        
        //禁止使用刪除功能
        //$tools->disableDelete();
        
        //禁用查看功能
        $tools->disableView();
        
        //禁用列表按鈕功能
        //$tools->disableList();
    });
});
    
//數據表格初始化設定
Grid::resolving(function (Grid $grid) {
    //圖片自動大小
    Admin::style('img { max-width: 100%; height: auto !important;}'); 
    //禁止操作全部功能
    //$grid->disableActions();
    
    //禁止分頁
    //$grid->disablePagination();
    
    //禁止創建按鈕
    //$grid->disableCreateButton();
    
    //禁止使用篩選功能
    //$grid->disableFilter();
    
    //禁止使用每筆資料前面都會加的checkbox功能
    //$grid->disableRowSelector();
    
    //禁止使用表單上面所有功能(如篩選/重新整理/新增功能)
    //$grid->disableToolbar();
    
    //最右邊三個點時，會顯示編輯/刪除/查看，以下的是禁止使用查看功能
    
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        $actions->disableView();
        // $actions->disableEdit();
        //$actions->disableDelete();
    });
        
        //设置列选择器 (字段显示或隐藏 showColumnSelector)
        $grid->showColumnSelector();
});