<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->timestamp('signature_undertaking_at')->nullable();   // user accepted the e-signature undertaking
            $t->timestamp('signature_verified_at')->nullable();      // focal person verified identity + signature
            $t->foreignId('signature_verified_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('travel_requests', function (Blueprint $t) {
            $t->string('verification_code')->nullable()->unique();   // printed on the copy, used on /verify/{code}
            $t->json('snapshot')->nullable();                        // name/position/office of each signatory at signing time
        });
        Schema::create('signature_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('travel_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->string('event');                                     // requested | recommended | approved | rejected
            $t->json('parties');
            $t->string('signature_sha256', 64)->nullable();
            $t->string('document_hash', 64);
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent')->nullable();
            $t->timestamp('signed_at');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('signature_logs');
        Schema::table('travel_requests', function (Blueprint $t) { $t->dropColumn(['verification_code', 'snapshot']); });
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('signature_verified_by');
            $t->dropColumn(['signature_undertaking_at', 'signature_verified_at']);
        });
    }
};
