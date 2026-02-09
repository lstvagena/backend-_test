<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_parameters', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_minimum_password_length_enabled')->default(0);
            $table->tinyInteger('minimum_password_length')->length(5)->default(8);

            $table->boolean('is_maximum_password_length_enabled')->default(0);
            $table->tinyInteger('maximum_password_length')->length(5)->default(12);

            $table->boolean('is_minimum_lowercase_required_enabled')->default(0);
            $table->tinyInteger('minimum_lowercase_required')->length(5)->default(1);

            $table->boolean('is_minimum_uppercase_required_enabled')->default(0);
            $table->tinyInteger('minimum_uppercase_required')->length(5)->default(1);

            $table->boolean('is_minimum_numeric_required_enabled')->default(0);
            $table->tinyInteger('minimum_numeric_required')->length(5)->default(1);

            $table->boolean('is_minimum_special_char_required_enabled')->default(0);
            $table->tinyInteger('minimum_special_char_required')->length(5)->default(1);

            $table->boolean('is_maximum_login_attempts_enabled')->default(0);
            $table->tinyInteger('maximum_login_attempts')->length(5)->default(5);

            $table->boolean('is_idle_logout_time_enabled')->default(0);
            $table->tinyInteger('idle_logout_time')->length(5)->default(1); //minutes

            $table->boolean('is_admin_password_expire_after_enabled')->default(0);
            $table->integer('admin_password_expire_after')->length(5)->default(90); //days

            $table->boolean('is_user_password_expire_after_enabled')->default(0);
            $table->integer('user_password_expire_after')->length(5)->default(180); //days

            $table->boolean('is_remind_password_expiration_enabled')->default(0);
            $table->tinyInteger('remind_password_expiration_after')->length(5)->default(5); //days

            $table->boolean('is_disable_unused_accounts_after_enabled')->default(0);
            $table->tinyInteger('disable_unused_accounts_after')->length(5)->default(10);

            // Other flags, stored as boolean (acting as boolean) with defaul0
            $table->boolean('is_restrict_username_as_password')->default(0);
            $table->boolean('is_restrict_sequential_char_as_password')->default(0);
            $table->boolean('is_restrict_repetitive_char_as_password')->default(0);

            $table->boolean('is_force_login_allowed')->default(0);
            $table->boolean('do_lock_account_on_dual_login')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_parameters');
    }
};
