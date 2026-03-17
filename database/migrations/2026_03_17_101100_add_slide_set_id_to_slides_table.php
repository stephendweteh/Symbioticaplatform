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
            $table->foreignId('slide_set_id')
                ->nullable()
                ->after('id')
                ->constrained('slide_sets')
                ->nullOnDelete();
        });

        $defaultSetId = DB::table('slide_sets')->insertGetId([
            'title' => 'General Slide Set',
            'description' => 'Default grouping for existing slides.',
            'order_number' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('slides')
            ->whereNull('slide_set_id')
            ->update(['slide_set_id' => $defaultSetId]);
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('slide_set_id');
        });
    }
};

