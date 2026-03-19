<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend enum set to include the new "consent" type.
        DB::statement("ALTER TABLE registration_fields MODIFY COLUMN field_type ENUM('text','email','tel','number','date','textarea','select','consent') NOT NULL DEFAULT 'text'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum set without 'consent'.
        DB::statement("ALTER TABLE registration_fields MODIFY COLUMN field_type ENUM('text','email','tel','number','date','textarea','select') NOT NULL DEFAULT 'text'");
    }
};

