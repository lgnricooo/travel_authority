<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('positions', function (Blueprint $t) {
            $t->id();
            $t->string('title');                 // printed on the travel authority
            $t->string('note')->nullable();      // e.g. "CID Chief", "Utility Worker / Watchman"
            $t->string('group');                 // dropdown group, e.g. "School Heads"
            $t->string('salary_grade')->nullable();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) { $t->dropConstrainedForeignId('position_id'); });
        Schema::dropIfExists('positions');
    }
};
