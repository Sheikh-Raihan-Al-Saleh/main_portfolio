<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class MigrateMedia extends Command
{
    protected $signature = 'media:migrate';

    protected $description = 'Move uploaded files from storage/app/public to public/uploads so they are served statically and tracked by git';

    public function handle(): int
    {
        $source = storage_path('app/public');
        $target = public_path('uploads');

        if (! is_dir($source)) {
            $this->info('Nothing to migrate: storage/app/public does not exist.');

            return self::SUCCESS;
        }

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $moved = 0;
        $skipped = 0;

        $finder = (new Finder)
            ->in($source)
            ->files()
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);

        foreach ($finder as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));

            // Keep the directory skeleton's .gitignore files where they are.
            if (basename($relative) === '.gitignore') {
                continue;
            }

            $destination = $target.'/'.$relative;

            if (file_exists($destination)) {
                $skipped++;

                continue;
            }

            $dir = dirname($destination);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            rename($file->getPathname(), $destination);
            $moved++;
        }

        $this->info("Moved {$moved} file(s) to public/uploads ({$skipped} already there).");
        $this->line('public/uploads is tracked by git, so these files now ship with every deploy.');

        if ($skipped > 0 || $moved > 0) {
            $this->line('Commit them: git add public/uploads && git commit');
        }

        return self::SUCCESS;
    }
}
