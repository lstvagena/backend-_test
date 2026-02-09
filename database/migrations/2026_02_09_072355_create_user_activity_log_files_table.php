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
        Schema::create('user_activity_log_files', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->comment("User ID");
            $table->string('event')->comment("Event");
            $table->string('auditable_type')->default('NA')->comment("Auditable Type");
           // $table->unsignedBigInteger('auditable_id')->default(0)->comment("Auditable ID");
            $table->json('old_value')->nullable()->comment("Old Value");
            $table->json('new_value')->nullable()->comment("New Value");
            $table->string('url')->nullable()->comment("URL");
            $table->string('module_name')->default('seed')->comment("Module Name");
            $table->string('remarks')->nullable()->comment("Remarks");
            $table->string('ip_address')->nullable()->comment("IP Address");
            $table->string('user_agent')->nullable()->comment("User Agent");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_log_files');
    }
};
