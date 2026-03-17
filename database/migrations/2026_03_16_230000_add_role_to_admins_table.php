<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('role', 50)->default('admin')->after('password');
        });

        // Ensure there is at least one super admin.
        $superAdminExists = DB::table('admins')->where('role', 'super_admin')->exists();
        if (! $superAdminExists) {
            $firstAdminId = DB::table('admins')->orderBy('id')->value('id');
            if ($firstAdminId) {
                DB::table('admins')->where('id', $firstAdminId)->update(['role' => 'super_admin']);
            }
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
