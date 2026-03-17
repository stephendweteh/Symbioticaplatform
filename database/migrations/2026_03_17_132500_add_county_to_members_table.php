<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('county')->nullable()->after('role');
        });

        // Backfill county from additional_data for existing rows where available.
        DB::table('members')
            ->select(['id', 'additional_data'])
            ->orderBy('id')
            ->chunkById(200, function ($members): void {
                foreach ($members as $member) {
                    if (! $member->additional_data) {
                        continue;
                    }

                    $decoded = json_decode((string) $member->additional_data, true);
                    $county = is_array($decoded) ? ($decoded['county'] ?? null) : null;
                    if (! is_string($county) || trim($county) === '') {
                        continue;
                    }

                    DB::table('members')
                        ->where('id', $member->id)
                        ->update(['county' => trim($county)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('county');
        });
    }
};

