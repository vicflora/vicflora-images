<?php

namespace App\Commands;

use App\Actions\InsertNewImageRecords;
use App\Actions\SoftDeleteOldImageRecords;
use App\Actions\UpdateExistingImageRecords;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class UpdateImageTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:update-image-tables {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates images and taxon_concept_images tables in VicFlora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->task('Get image metadata from API', function () {
            if ($this->option('all')) {
                $this->callSilent('images:get-image-metadata-from-api');
                return true;
            }
            else {
                $from = DB::table('canto.images')->max('updated_at');
                $this->callSilent('images:get-image-metadata-from-api', [
                    '--date' => $from
                ]);
                return true;
            }
        });

        $this->task('Insert new images', function () {
            (new InsertNewImageRecords)();
            return true;
        });

        $this->task('Update existing images', function () {
            (new UpdateExistingImageRecords)();
            return true;
        });

        if ($this->option('all')) {
            $this->task('Soft-delete old images', function () {
                (new SoftDeleteOldImageRecords)();
                return true;
            });

            $this->task('Reset all taxon concept–image links', function() {
                $this->callSilent('app:recreate-taxon-concept-images');
                return true;
            });
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command('images:update-image-tables')
            ->everyThirtyMinutes()->withoutOverlapping();

        $schedule->command('images:update-image-tables', ['--all' => true])
            ->dailyAt('04:00')->timezone('Australia/Melbourne');
    }
}
