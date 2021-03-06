<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\HouseExporter;
use App\Housings;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class HouseController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('房源管理')
            ->description('列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('新增房源')
            ->description('添加')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Housings);

        $grid->id('ID')->sortable();
        $grid->title('标题')->modal('更多', function ($model) {

            $comments = $model->take(1)->get()->map(function ($comment) {
                return $comment->only(['address','desc', 'remark']);
            });

            return new Table(['地址', '描述', '备注'], $comments->toArray(),['table-hover']);
        });

        $grid->rentsale('租售')->display(function ($released) {
            switch ($released){
                case 1:
                    $str='出售';
                    break;
                case 2:
                    $str= '出租';
                    break;
            }
            return $str;
        });
        $grid->purpose('用途')->display(function ($released) {
            switch ($released){
                case 1:
                    $str='住宅';
                    break;
                case 2:
                    $str= '别墅';
                    break;
                case 3:
                    $str= '商铺';
                    break;
                case 4:
                    $str= '写字楼';
                    break;
            }
            return $str;
        });
        $grid->type('类型')->display(function ($released) {
            switch ($released){
                case 1:
                    $str='新房';
                    break;
                case 2:
                    $str= '二手房';
                    break;
            }
            return $str;
        });
        $grid->owner('业主');
        $grid->phone('联系方式');
        $grid->years('修建年份');

        $grid->direction('朝向');
        $grid->room('房');
        $grid->hall('厅');
        $grid->toilet('卫');
        $grid->area('面积');
        $grid->price('价格');
        $grid->renovation('装修类型')->display(function ($released) {
            switch ($released){
                case 1:
                    $str='精装修';
                    break;
                case 2:
                    $str= '简装';
                    break;
                case 3:
                    $str= '清水房';
                    break;
            }
            return $str;
        });
        $grid->floor('楼层');
        $grid->t_floor('总楼层');
        $grid->created_at(trans('admin.created_at'));
       // $grid->updated_at(trans('admin.updated_at'));
        //$grid->disableExport();//禁用导出
        $grid->exporter(new HouseExporter());
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
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
        $show = new Show(Housings::findOrFail($id));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Housings);

        $form->text('title', '标题')->rules('required|min:3');
        $form->radio('rentsale', '租售类型')->options([1 => '出售', 2 => '出租'])->rules('required');
        $form->radio('type', '房源类型')->options([1 => '新房', 2 => '二手房'])->rules('required');
        $form->radio('purpose', '用途')->options([1 => '住宅', 2 => '别墅', 3 => '商铺', 4 => '写字楼'])->rules('required');
        $form->text('owner', '业主姓名')->rules('required');
        $form->mobile('phone', '联系方式')->rules('required');
        $form->datetime('years', '修建年份')->format('YYYY')->rules('required');

        $form->text('direction', '朝向')->placeholder('填写朝向,如:坐南朝北,南,等')->rules('required');
        $form->slider('room', '房')->options(['max' => 10, 'min' => 1, 'step' => 1, 'postfix' => '房'])->rules('required');
        $form->slider('hall', '厅')->options(['max' => 10, 'min' => 1, 'step' => 1, 'postfix' => '厅'])->rules('required');
        $form->slider('toilet', '卫')->options(['max' => 10, 'min' => 1, 'step' => 1, 'postfix' => '卫'])->rules('required');
        $form->decimal('area', '面积')->default(0.0)->rules('required');
        $form->decimal('price', '价格')->default(0.00)->rules('required');
        $form->radio('renovation', '装修类型')->options([1 => '精装修', 2 => '简装', 3 => '清水房'])->rules('required');
        $form->number('floor', '楼层')->min(1)->max(100)->rules('required');
        $form->number('t_floor', '总楼层')->min(1)->max(100)->rules('required');
        $form->text('address', '地址')->rules('required');
        $form->latlong('latitude', 'longitude', 'Position');
        $form->textarea('desc', '描述');
        $form->textarea('remark', '备注');
        // 多图
        $form->multipleImage('pictures','图片')->removable()->sortable()->uniqueName();
        $form->radio('setup', '设置')->options([0=>'不设置',1 => '热门']);
        $form->footer(function ($footer) {

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();

        });
        return $form;
    }
}
