<?php

namespace App\Commands;

use App\Actions\QueryRestApiImages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class GetImageMetadataFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:get-image-metadata-from-api {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets image metadata from the Canto Integration API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date');

        $imagesQuery = new QueryRestApiImages;

        $perPage = 1000;
        $page = 1;
        $lastPage = 1;

        if (!$date) {
            $this->info('Reloading all image metadata...');
            DB::delete("DELETE FROM canto.images");
        }
        else {
            $this->info('Updating metadata for records created or updated after ' . $date . '...');
        }

        while ($page <= $lastPage) {
            $result = $imagesQuery($perPage, $page, $date);
            $lastPage = $result['last_page'];
            $page++;

            $images = $result['data'];

            $data = Arr::map($images, function($image) {
                return [
                    'id' => $image['id'],
                    'preview_url' => $image['previewUrl'],
                    'thumbnail_url' => $image['thumbnailUrl'],
                    'highres_url' => $image['highestResUrl'],
                    'pixel_x_dimension' => $image['pixelXDimension'],
                    'pixel_y_dimension' => $image['pixelYDimension'],
                    'caption' => $image['caption'],
                    'creation_date' => $image['creationDate'],
                    'creator' => $image['creator'],
                    'license' => $image['license'],
                    'copyright_owner' => $image['copyrightOwner'],
                    'subtype' => $image['subType'],
                    'catalog_number' => $image['catalogNumber'],
                    'country' => $image['country'],
                    'canto_file_name' => $image['cantoFileName'],
                    'decimal_latitude' => $image['decimalLatitude'],
                    'decimal_longitude' => $image['decimalLongitude'],
                    'feature' => $image['feature'],
                    'locality' => $image['locality'],
                    'modified' => $image['modified'],
                    'original_scientific_name' => $image['originalScientificName'],
                    'product' => $image['product'],
                    'rating' => $image['rating'],
                    'recorded_by' => $image['recordedBy'],
                    'record_number' => $image['recordNumber'],
                    'rights' => $image['rights'],
                    'scientific_name' => $image['scientificName'],
                    'source' => $image['source'],
                    'state_province' => $image['stateProvince'],
                    'subject_category' => $image['subjectCategory'],
                    'title' => $image['title'],
                    'type' => $image['type'],
                    'direct_url_original' => $image['directUrlOriginal'],
                    'image_extension' => $image['imageExtension'],
                    'is_hero' => $image['isHero'],
                    'created_at' => $image['created_at'],
                    'updated_at' => $image['updated_at'],
                ];
            });

            $insertData = collect($data)->filter(fn ($row) => $row['id']);



            if (!$date) {
                $this->info("Loading $result[from] to $result[to] of $result[total]");
                DB::table('canto.images')->insertOrIgnore($insertData->toArray());
            }
            else {
                $updateFields = [
                    'preview_url',
                    'thumbnail_url',
                    'highres_url',
                    'pixel_x_dimension',
                    'pixel_y_dimension',
                    'caption',
                    'creation_date',
                    'creator',
                    'license',
                    'copyright_owner',
                    'subtype',
                    'catalog_number',
                    'country',
                    'canto_file_name',
                    'decimal_latitude',
                    'decimal_longitude',
                    'feature',
                    'locality',
                    'modified',
                    'original_scientific_name',
                    'product',
                    'rating',
                    'recorded_by',
                    'record_number',
                    'rights',
                    'scientific_name',
                    'source',
                    'state_province',
                    'subject_category',
                    'title',
                    'type',
                    'direct_url_original',
                    'image_extension',
                    'is_hero',
                    'updated_at',
                ];
                $this->info("Updating $result[from] to $result[to] of $result[total]");
                DB::table('canto.images')->upsert($insertData->unique('id')->toArray(), 'id', $updateFields);
            }

        }
        return 0;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        //
    }
}
