<?php
// Copyright 2022 Royal Botanic Gardens Board
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

class FindTaxon {

    public function __invoke(string $name)
    {
        return DB::table('taxon_concepts as tc')
            ->join('taxon_names as tn', 'tc.taxon_name_id', '=', 'tn.id')
            ->join('taxonomic_statuses as ts', 'tc.taxonomic_status_id', '=', 'ts.id')
            ->whereIn('ts.name', ['accepted', 'homotypic synonym', 'heterotypic synomym', 'synonym'])
            ->where('tn.full_name', $name)
            ->select('tc.id as taxon_id', 'tc.accepted_id')
            ->first();
    }
}
