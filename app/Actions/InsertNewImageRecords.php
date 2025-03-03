<?php
// Copyright 2025 Royal Botanic Gardens Board
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use stdClass;

class InsertNewImageRecords {

    public function __invoke()
    {
        $findTaxon = new FindTaxon;
        $inserts = DB::table('canto.images as i')
                ->leftJoin('images as i2', 'i.id', '=', 'i2.canto_content_id')
                ->whereNotNull('i.thumbnail_url')
                ->whereNull('i2.id')
                ->select(
                    'i.created_at',
                    'i.updated_at',
                    'i.creation_date',
                    'i.caption',
                    'i.catalog_number',
                    'i.copyright_owner',
                    'i.country',
                    'i.creation_date',
                    'i.creator',
                    'i.id as canto_content_id',
                    'i.canto_file_name',
                    'i.is_hero as hero_image',
                    'i.license',
                    'i.locality',
                    'i.modified',
                    'i.pixel_x_dimension',
                    'i.pixel_y_dimension',
                    'i.rating',
                    'i.recorded_by',
                    'i.record_number',
                    'i.rights',
                    'i.scientific_name',
                    'i.source',
                    'i.state_province',
                    'i.subject_category',
                    'i.subtype',
                    'i.title',
                    'i.type',
                    'i.direct_url_original',
                    DB::raw("replace(lower(i.thumbnail_url), '.jpeg', '.jpg') as thumbnail_url"),
                    DB::raw("replace(lower(i.preview_url), '.jpeg', '.jpg') as preview_url"),
                    DB::raw("replace(lower(i.highres_url), '.jpeg', '.jpg') as highres_url")
                )
                ->get();

        foreach ($inserts as $insert) {
            $insert = (array) $insert;
            $insert['rating'] = (int) $insert['rating'];
            $taxon = false;

            $imageId = DB::table('images')->insertGetId($insert);

            if ($insert['scientific_name']) {
                $taxon = $findTaxon(trim($insert['scientific_name']));

                if ($taxon) {
                    DB::table('taxon_concept_image')->insert([
                        'taxon_concept_id' => $taxon->taxon_id,
                        'image_id' => $imageId,
                    ]);
                }
            }

        }
        return 0;
    }
}
