<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('shift_id')
                ->constrained()->nullOnDelete();

            // Check-in geo
            $table->decimal('check_in_lat', 10, 7)->nullable()->after('check_in');
            $table->decimal('check_in_lng', 10, 7)->nullable()->after('check_in_lat');
            $table->unsignedInteger('check_in_distance_m')->nullable()->after('check_in_lng')
                ->comment('Distance from assigned location in meters');
            $table->enum('check_in_method', ['gps','qr','qr+gps','manual'])
                ->default('gps')->after('check_in_distance_m');

            // Check-out geo
            $table->decimal('check_out_lat', 10, 7)->nullable()->after('check_out');
            $table->decimal('check_out_lng', 10, 7)->nullable()->after('check_out_lat');
            $table->unsignedInteger('check_out_distance_m')->nullable()->after('check_out_lng');
            $table->enum('check_out_method', ['gps','qr','qr+gps','manual'])
                ->default('gps')->after('check_out_distance_m');

            $table->string('device_info')->nullable()->after('source');
        });
    }

    public function down(): void {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn([
                'check_in_lat','check_in_lng','check_in_distance_m','check_in_method',
                'check_out_lat','check_out_lng','check_out_distance_m','check_out_method',
                'device_info',
            ]);
        });
    }
};