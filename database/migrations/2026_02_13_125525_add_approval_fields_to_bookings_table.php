<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Cek dulu apakah column sudah ada
            if (!Schema::hasColumn('bookings', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }

            if (!Schema::hasColumn('bookings', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // tu_note udah ada di migration sebelumnya, jadi skip
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'approved_by')) {
                $table->dropColumn('approved_by');
            }

            if (Schema::hasColumn('bookings', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};