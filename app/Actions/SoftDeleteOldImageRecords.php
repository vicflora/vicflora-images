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

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SoftDeleteOldImageRecords {

    public function __invoke()
    {
        $deletes = DB::table('images as i')
                ->leftJoin('canto.images as ci',
                        'i.canto_content_id', '=', 'ci.id')
                ->whereNull('ci.thumbnail_url')
                ->whereNull('i.deleted_at')
                ->pluck('i.id');
        foreach ($deletes as $delete) {
            DB::table('images')->where('id', $delete)
                    ->update(['deleted_at' => Carbon::now()]);
        }
        return 0;
    }
}
