<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;

class SeedSystemSettings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:seed {--force : Force overwrite existing settings}';

    /**
     * The console command description.
     */
    protected $description = 'Seed default system settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        if (! $force && SystemSetting::count() > 0) {
            if (! $this->confirm('System settings already exist. Do you want to continue?')) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info('Seeding system settings...');

        try {
            SystemSetting::seedDefaults();
            $this->info('✅ System settings seeded successfully!');

            $count = SystemSetting::count();
            $this->info("📊 Total settings: {$count}");

            // Show categories
            $categories = SystemSetting::select('category')
                ->groupBy('category')
                ->pluck('category');

            $this->info('📁 Categories: '.$categories->implode(', '));

        } catch (\Exception $e) {
            $this->error('❌ Failed to seed settings: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
