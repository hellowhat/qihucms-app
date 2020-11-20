<?php

namespace Qihucms\App\Admin\Controllers;

use Qihucms\App\Models\AppMenu;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AppMenuController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '会员中心菜单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AppMenu());

        $grid->column('id', trans('qihu-app::app.Id'));
        $grid->column('title', trans('qihu-app::app.Title'));
        $grid->column('line', trans('qihu-app::app.Line'));
        $grid->column('created_at', trans('qihu-app::app.created_at'));
        $grid->column('updated_at', trans('qihu-app::app.updated_at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AppMenu::findOrFail($id));

        $show->field('id', trans('qihu-app::app.Id'));
        $show->field('title', trans('qihu-app::app.Title'));
        $show->field('line', trans('qihu-app::app.Line'));
        $show->field('config', trans('qihu-app::app.Config'));
        $show->field('created_at', trans('qihu-app::app.created_at'));
        $show->field('updated_at', trans('qihu-app::app.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AppMenu());

        $form->text('title', trans('qihu-app::app.Title'));
        $form->number('line', trans('qihu-app::app.Line'))->default(1);
        $form->table('config',trans('qihu-app::app.Config'), function ($table) {
            $table->text('name',trans('qihu-app::app.Menu.name'));
            $table->url('icon',trans('qihu-app::app.Menu.icon'));
            $table->text('path',trans('qihu-app::app.Menu.path'));
            $table->select('type',trans('qihu-app::app.Menu.type'))->options(trans('qihu-app::app.Menu.type_options'));
        });
        return $form;
    }
}
