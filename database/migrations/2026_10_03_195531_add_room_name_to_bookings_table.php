<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bookings', 'room_name')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('room_name')->nullable()->after('room_id');
            });
        }

        DB::table('bookings')
            ->whereNull('room_name')
            ->update([
                'room_name' => DB::raw('(select name from rooms where rooms.id = bookings.room_id)'),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'room_name')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('room_name');
            });
        }
    }
};