<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
        Schema::table('to_dos', function (Blueprint $table) {
            $table->boolean('completed')->default(false)->after('type');
            $table->timestamp('completed_at')->nullable()->after('completed');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
        Schema::table('to_dos', function (Blueprint $table) {
            $table->dropColumn(['completed', 'completed_at']);
        });
    }
};