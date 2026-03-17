<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('field_key')->unique();
            $table->string('field_type', 50)->default('textarea');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (! Schema::hasColumn('surveys', 'additional_data')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->json('additional_data')->nullable()->after('comments');
            });
        }

        DB::table('survey_fields')->insert([
            [
                'label' => 'How was your overall exhibition experience?',
                'field_key' => 'question_1',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'Was the presentation informative?',
                'field_key' => 'question_2',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'Which booth/session did you find most useful?',
                'field_key' => 'question_3',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'Was the engagement process easy to follow?',
                'field_key' => 'question_4',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'Would you recommend this exhibition to others?',
                'field_key' => 'question_5',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'label' => 'Additional comments or suggestions',
                'field_key' => 'comments',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('surveys', 'additional_data')) {
            Schema::table('surveys', function (Blueprint $table) {
                $table->dropColumn('additional_data');
            });
        }

        Schema::dropIfExists('survey_fields');
    }
};
