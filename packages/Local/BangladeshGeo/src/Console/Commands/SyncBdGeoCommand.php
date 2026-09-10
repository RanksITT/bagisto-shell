<?php

namespace Local\BangladeshGeo\Console\Commands;

use Illuminate\Console\Command;
use Local\BangladeshGeo\Services\BdGeoImporter;
use Throwable;

class SyncBdGeoCommand extends Command
{
    protected $signature = 'bd-geo:sync
                            {--dry-run : Report what would change without writing}
                            {--prune : Retire upstream rows no longer present (never touches curated local rows)}';

    protected $description = 'Import/refresh the Bangladesh administrative hierarchy (divisions, districts, upazilas, unions).';

    public function handle(BdGeoImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'bd-geo:sync (dry run)' : 'bd-geo:sync');

        try {
            $importer->dryRun($dryRun)->run((bool) $this->option('prune'));
        } catch (Throwable $e) {
            $this->error('Import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($importer->stats() as $key => $value) {
            $this->line(sprintf('  %-24s %d', $key, $value));
        }

        foreach ($importer->notes() as $note) {
            $this->comment('  '.$note);
        }

        $this->info('done');

        return self::SUCCESS;
    }
}
