<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDummySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan content:test
     */
    protected $signature = 'content:test';

    /**
     * The console command description.
     */
    protected $description = 'Generate a dummy summary Markdown file for ContentAgent testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Path: ../content relative to Laravel root
        $dir = base_path('../content');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = $dir . '/summary-' . date('Ymd-His') . '.md';

        $content = <<<MD
# Dummy Log Summary

Generated on **{$this->now()}**

## Sample Entries
- 2025-09-14 10:00:00 [DevAgent] ✅ Completed → Scaffolded FooController
- 2025-09-14 10:05:00 [ContentAgent] 📤 In Progress → Writing documentation
- 2025-09-14 10:06:00 [ContentAgent] ✅ Completed → Documentation draft created

## Notes
This is a **test Markdown file** created by \`php artisan content:test\`.
MD;

        file_put_contents($filename, $content);

        $this->info("✅ Dummy summary created: " . basename($filename));
        return Command::SUCCESS;
    }

    protected function now()
    {
        return date('Y-m-d H:i:s');
    }
}
