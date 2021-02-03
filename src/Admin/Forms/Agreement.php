<?php

namespace Qihucms\App\Admin\Forms;

use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Agreement extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'APP用户协议';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        foreach ($data as $key => $value) {
            Cache::put($key, $value);
        }
        admin_success('更新成功');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->radio('config_agreement_show', '是否启用')
            ->options(['不启用', '启用'])
            ->default(1)->help('APP打开后提示，同意后不会再提示，直到版本号变更时会重新提示。');
        $this->number('config_agreement_version','协议版本号')->default(1)->help('请输入整数，当客户端版本号与服务器版本号不一致时会提示用户同意相关协议。');
        $this->UEditor('config_agreement_alert', '提示内容');
        $this->number('config_agreement_register', '注册协议内容ID')->help('在【内容】【帮助】中发布协议文章，将文章ID填入即可。');
        $this->number('config_agreement_privacy', '隐私条例内容ID')->help('在【内容】【帮助】中发布协议文章，将文章ID填入即可。');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return [
            'config_agreement_show' => Cache::get('config_agreement_show'),
            'config_agreement_version' => Cache::get('config_agreement_version'),
            'config_agreement_alert' => Cache::get('config_agreement_alert'),
            'config_agreement_register' => Cache::get('config_agreement_register'),
            'config_agreement_privacy' => Cache::get('config_agreement_privacy'),
        ];
    }
}
