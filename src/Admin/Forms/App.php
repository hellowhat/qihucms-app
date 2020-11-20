<?php

namespace Qihucms\App\Admin\Forms;

use App\Plugins\Plugin;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class App extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'APP配置';

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(Request $request)
    {
        $data = $request->all();

        $message = '保存成功';

        $plugin = new Plugin();

        // 授权激活
        if ($request->has('qihucms-appLicenseKey') && Cache::store('file')->get('qihucms-appLicenseKey') != $data['qihucms-appLicenseKey']) {
            $result = $plugin->registerPlugin('qihucms-app', $data['qihucms-appLicenseKey']);
            if ($result) {
                $message .= '；授权激活成功';
            } else {
                $message .= '；授权激活失败';
            }
        }

        unset($data['qihucms-appLicenseKey']);

        foreach ($data as $key => $value) {

            Cache::put($key, $value);
        }

        admin_success('保存成功');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('qihucms-appLicenseKey', '插件授权')->help('购买授权地址：<a href="http://ka.qihucms.com/product/" target="_blank">http://ka.qihucms.com</a>');
    }

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function data()
    {
        return [
            'qihucms-appLicenseKey' => Cache::store('file')->get('qihucms-appLicenseKey'),
            //'app_config_menu_one' => Cache::get('app_config_menu_one'),
        ];
    }
}