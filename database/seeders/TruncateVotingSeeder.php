<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruncateVotingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('voting')->truncate();

        $this->command?->info('Seluruh hasil voting berhasil dikosongkan.');
    }
}
