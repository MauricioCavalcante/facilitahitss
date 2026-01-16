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
        Schema::table('ana_users', function (Blueprint $table) {
            if (!Schema::hasColumn('ana_users', 'email_ana')) {
                $table->string('email_ana')->after('user_id')->nullable()->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ana_users', function (Blueprint $table) {
            $table->dropColumn('email_ana');
        });
    }
};
