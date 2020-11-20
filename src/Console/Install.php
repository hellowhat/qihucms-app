<?php

namespace Qihucms\App\Console;

use App\Plugins\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qihucms-app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'qihucms app plugin install command.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $plugin = new Plugin();
        $this->call('migrate');
        $root = DB::table('admin_menu')->insertGetId([
            'title' => 'APP',
            'parent_id' => 46,
            'order' => DB::table('admin_menu')->count() + 1,
            'icon' => 'fa-android',
            'uri' => null
        ]);
        DB::table('admin_menu')->insert([
            'title' => 'APP配置',
            'parent_id' => $root,
            'order' => DB::table('admin_menu')->count() + 1,
            'icon' => 'fa-cog',
            'uri' => 'plugins/qihucms/app/config'
        ]);
        DB::table('admin_menu')->insert([
            'title' => 'APP用户协议',
            'parent_id' => $root,
            'order' => DB::table('admin_menu')->count() + 1,
            'icon' => 'fa-cog',
            'uri' => 'plugins/qihucms/app/agreement'
        ]);
        DB::table('admin_menu')->insert([
            'title' => '会员中心菜单配置',
            'parent_id' => $root,
            'order' => DB::table('admin_menu')->count() + 1,
            'icon' => 'fa-cog',
            'uri' => 'plugins/qihucms/app/app_menus'
        ]);

        $plugin->setPluginVersion('qihucms-app', 100);
        $this->info('install successed.');
    }
}
