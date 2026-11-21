<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('teacher')->after('password'); // admin|teacher|school_head|unit_personnel|unit_head|asds|sds
            $t->string('position')->nullable();
            $t->string('office')->nullable(); // school or SDO unit
            $t->foreignId('recommender_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('signature_path')->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('recommender_id');
            $t->dropColumn(['role', 'position', 'office', 'signature_path']);
        });
    }
};
