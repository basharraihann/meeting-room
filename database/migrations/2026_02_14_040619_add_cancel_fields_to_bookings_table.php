<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cancel_reason')->nullable()->after('tu_note');
            $table->timestamp('canceled_at')->nullable()->after('cancel_reason');
            $table->foreignId('canceled_by')->nullable()->constrained('users')->nullOnDelete()->after('canceled_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('canceled_by');
            $table->dropColumn(['cancel_reason', 'canceled_at']);
        });
    }
};
