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

class UpdateExistingImageRecords {

    public function __invoke()
    {
        $findTaxon = new FindTaxon;
        $updates = DB::table('canto.images as i')
                ->leftJoin('images as i2', 'i.id', '=', 'i2.canto_content_id')
                ->whereNotNull('i2.id')
                ->whereRaw('i.updated_at > i2.updated_at')
                ->select(
                    'i2.id',
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
                    'i.original_scientific_name',
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
                    DB::raw('null as deleted_at'),
                    'i.updated_at',
                    'i.created_at',
                    'i.direct_url_original',
                    DB::raw("replace(lower(i.thumbnail_url), '.jpeg', '.jpg') as thumbnail_url"),
                    DB::raw("replace(lower(i.preview_url), '.jpeg', '.jpg') as preview_url"),
                    DB::raw("replace(lower(i.highres_url), '.jpeg', '.jpg') as highres_url")
                )
                ->get();

        foreach ($updates as $update) {
            $update = (array) $update;
            $update['rating'] = (int) $update['rating'];
            $id = $update['id'];
            array_shift($update);

            DB::table('images')->where('id', $id)->update($update);
            DB::statement("update images set version = version + 1 where id = $id");

            DB::table('taxon_concept_image')->where('image_id', $id)
                    ->delete();

            $taxon = $update['scientific_name']
                ? $findTaxon(trim($update['scientific_name'])) : null;
            if ($taxon) {
                DB::table('taxon_concept_image')->insert([
                    'taxon_concept_id' => $taxon->taxon_id,
                    'image_id' => $id,
                ]);
            }
        }
        return 0;
    }
}
