<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep slide-to-subcategory links, remove direct slide-to-experience links.
        DB::table('slides')
            ->whereNotNull('slide_subcategory_id')
            ->update(['slide_set_id' => null]);
    }

    public function down(): void
    {
        // Restore direct links from subcategory->experience if rollback is needed.
        $rows = DB::table('slides as s')
            ->join('slide_subcategories as sc', 'sc.id', '=', 's.slide_subcategory_id')
            ->select('s.id', 'sc.slide_set_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('slides')
                ->where('id', $row->id)
                ->update(['slide_set_id' => $row->slide_set_id]);
        }
    }
};

