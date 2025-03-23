<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_design_signatory_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('order');
            $table->foreignId('activity_design_id')->constrained(indexName: 'activity_design_signatory_group_foreign')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_design_signatory_groups');
    }
};
