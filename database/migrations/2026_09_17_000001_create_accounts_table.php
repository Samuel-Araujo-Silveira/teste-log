<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->unsignedBigInteger('establishment_id');
            $table->unsignedTinyInteger('status_account_id');
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['establishment_id', 'status_account_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
