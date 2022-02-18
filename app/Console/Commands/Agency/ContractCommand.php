<?php

namespace App\Console\Commands\Agency;

use Illuminate\Console\Command;
use App\Services\ContractService;
use DB;
use Exception;
use Log;

class ContractCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agency:contract {option}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '旅行会社に対する契約関連コマンド';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ContractService $contractService)
    {
        parent::__construct();
        $this->contractService = $contractService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(300);

        $option = $this->argument("option");

        if ($option == 'renewal') { // 契約更新処理
            try {
                $totals = DB::transaction(function () {
                    return $this->contractService->renewal();
                });
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
