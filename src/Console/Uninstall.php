<?php

namespace Qihucms\App\Console;

use App\Plugins\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Uninstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qihucms-app:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'qihucms app plugin uninstall command.';

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
        DB::table('migrations')->where('migration','like','%_app_menus_table')->delete();
        Schema::dropIfExists('app_menus');
        $root = DB::table('admin_menu')->where('uri','plugins/qihucms/app/config')->value('parent_id');
        DB::table('admin_menu')->where('parent_id',$root)->delete();
        DB::table('admin_menu')->where('id',$root)->delete();
        // 清除插件缓存
        (new Plugin())->clearPluginCache('qihucms-app');
        $this->info('uninstall successed.');
    }
}
