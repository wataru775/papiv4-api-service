<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAwsCache extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aws_cache', function (Blueprint $table) {
            $table->string('uri');
            $table->string('payload');
            $table->string('target');
            $table->string('data',1024);
            $table->timestamps();

            $table->primary(['uri', 'payload','target']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aws_cache');
    }
}
