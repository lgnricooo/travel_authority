<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('travel_requests', function (Blueprint $t) {
            $t->id();
            $t->string('tracking_no')->nullable()->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('recommender_id')->constrained('users');
            $t->foreignId('approver_id')->constrained('users');
            $t->text('purpose');
            $t->string('destination');
            $t->date('date_from');
            $t->date('date_to');
            $t->string('fund_source')->default('No expense to the government');
            $t->string('memo_path')->nullable();
            $t->string('status')->default('pending_recommendation'); // pending_recommendation|pending_approval|approved|rejected
            $t->string('requester_signature')->nullable();
            $t->string('recommender_signature')->nullable();
            $t->string('approver_signature')->nullable();
            $t->text('recommender_remarks')->nullable();
            $t->text('approver_remarks')->nullable();
            $t->timestamp('recommended_at')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('travel_requests'); }
};
