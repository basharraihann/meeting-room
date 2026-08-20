<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // phone di users sudah ada, skip

        // Ganti applicant_email jadi applicant_phone di bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('applicant_email');
            $table->string('applicant_phone')->nullable()->after('pic_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('applicant_phone');
            $table->string('applicant_email')->nullable()->after('pic_user_id');
        });
    }
};