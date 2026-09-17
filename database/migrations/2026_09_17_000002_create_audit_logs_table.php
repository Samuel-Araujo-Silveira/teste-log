<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('establishment_id')->nullable();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('result');
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index('correlation_id');
            $table->index('action');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
