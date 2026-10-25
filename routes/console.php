<?php

use Database\Seeders\AppliedCatalogUpgradeSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:upgrade-applied {--apply : Apply only recognized, unedited catalog upgrades}', function () {
    $report = (new AppliedCatalogUpgradeSeeder)->upgrade((bool) $this->option('apply'));
    foreach ($report as $slug => $result) {
        $this->line($slug.': '.$result);
    }
})->purpose('Preview or safely apply versioned applied-demo content upgrades (local/testing only)');
