<?php

namespace App\Commands;

use App\Actions\FindTaxon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class RecreateTaxonConceptImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recreate-taxon-concept-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $findTaxon = new FindTaxon;

        DB::table('taxon_concept_image')->delete();

        DB::table('images')
            ->select('id', 'scientific_name')
            ->orderBy('id')
            ->chunk(100, function(Collection $images) use ($findTaxon) {
                foreach ($images as $image) {
                    if ($image->scientific_name) {
                        $taxon = $findTaxon(trim($image->scientific_name));
        
                        if ($taxon) {
                            DB::table('taxon_concept_image')->insert([
                                'taxon_concept_id' => $taxon->taxon_id,
                                'image_id' => $image->id,
                            ]);
                        }
                    }
                }
            });
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
