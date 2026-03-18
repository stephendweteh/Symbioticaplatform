<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->foreignId('slide_subcategory_id')
                ->nullable()
                ->after('slide_set_id')
                ->constrained('slide_subcategories')
                ->nullOnDelete();
        });

        $setIds = DB::table('slides')
            ->whereNotNull('slide_set_id')
            ->distinct()
            ->pluck('slide_set_id');

        foreach ($setIds as $setId) {
            $subcategoryId = DB::table('slide_subcategories')->insertGetId([
                'slide_set_id' => $setId,
                'title' => 'General Sub Category',
                'description' => 'Default sub category for existing slides.',
                'order_number' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('slides')
                ->where('slide_set_id', $setId)
                ->whereNull('slide_subcategory_id')
                ->update(['slide_subcategory_id' => $subcategoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('slide_subcategory_id');
        });
    }
};

