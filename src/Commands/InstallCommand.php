<?php

namespace Monstrex\AveSite\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'ave-site:install {--force : Force installation in production}';

    protected $description = 'Install Ave Site CMS package';

    public function handle(): int
    {
        $this->info('🚀 Installing Ave Site CMS Package...');
        $this->newLine();

        // Run migrations
        $this->info('📦 Running migrations...');
        $this->call('migrate', [
            '--force' => $this->option('force'),
        ]);
        $this->newLine();

        // Publish config
        $this->info('⚙️  Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'ave-site-config',
            '--force' => true,
        ]);
        $this->newLine();

        // Publish views (optional)
        if ($this->confirm('Publish views to resources/views/vendor/ave-site?', false)) {
            $this->call('vendor:publish', [
                '--tag' => 'ave-site-views',
                '--force' => true,
            ]);
            $this->newLine();
        }

        $this->info('✅ Ave Site CMS package installed successfully!');
        $this->newLine();

        $this->comment('Next steps:');
        $this->line('  1. Create block regions in admin panel');
        $this->line('  2. Create pages and blocks');
        $this->line('  3. Configure settings via admin panel');
        $this->newLine();

        return self::SUCCESS;
    }
}
