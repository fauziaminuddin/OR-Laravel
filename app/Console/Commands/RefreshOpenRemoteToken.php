<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OpenRemoteService;

class RefreshOpenRemoteToken extends Command
{
    protected $signature = 'openremote:refresh-token';
    protected $description = 'Refresh OpenRemote API token';
    protected $openRemoteService;

    public function __construct(OpenRemoteService $openRemoteService)
    {
        parent::__construct();
        $this->openRemoteService = $openRemoteService;
    }

    public function handle()
    {
        $this->info('Refreshing OpenRemote API token...');
        $this->openRemoteService->refreshTokenIfNeeded();
        $this->info('Token refreshed successfully.');
    }
}
