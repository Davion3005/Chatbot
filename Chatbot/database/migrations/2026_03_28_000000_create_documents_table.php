<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() : void
    {
        Schema::connection('pgsql')->create('documents', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamps();
        });

        DB::connection('pgsql')->statement("ALTER TABLE documents ADD COLUMN embedding VECTOR(768)");
    }

    public function down() : void
    {
        Schema::connection('pgsql')->dropIfExists('documents');
    }
};
