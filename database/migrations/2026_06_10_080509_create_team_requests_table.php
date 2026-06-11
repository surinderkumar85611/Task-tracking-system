<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
{
    if (!Schema::hasTable('team_requests')) {

        Schema::create('team_requests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('leader_id');

            $table->string('type'); // add / remove
            $table->string('member_email');

            $table->string('department')->nullable();
            $table->text('eligibility')->nullable();
            $table->text('reason')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();
        });

    }
}

    public function down(): void
    {
        Schema::dropIfExists('team_requests');
    }
};