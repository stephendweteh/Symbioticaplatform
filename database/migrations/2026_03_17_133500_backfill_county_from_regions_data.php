<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')
            ->select(['id', 'county', 'additional_data'])
            ->orderBy('id')
            ->chunkById(200, function ($members): void {
                foreach ($members as $member) {
                    if (! blank($member->county) || blank($member->additional_data)) {
                        continue;
                    }

                    $decoded = json_decode((string) $member->additional_data, true);
                    $county = is_array($decoded)
                        ? ($decoded['county'] ?? $decoded['regions'] ?? null)
                        : null;

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
        // No-op: data backfill migration should not destroy user data.
    }
};

