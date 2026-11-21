<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        Schema::table('travel_requests', function (Blueprint $t) {
            $t->string('public_id', 32)->nullable()->unique(); // used in URLs instead of the numeric id
        });
        // Give existing requests an id too
        foreach (DB::table('travel_requests')->whereNull('public_id')->pluck('id') as $id) {
            DB::table('travel_requests')->where('id', $id)->update(['public_id' => strtolower((string) Str::ulid())]);
        }
    }
    public function down(): void {
        Schema::table('travel_requests', function (Blueprint $t) { $t->dropColumn('public_id'); });
    }
};
