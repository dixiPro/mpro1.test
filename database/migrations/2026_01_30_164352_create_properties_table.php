<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('re_properties', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('price');

            $table->unsignedTinyInteger('bedrooms');
            $table->unsignedTinyInteger('bathrooms');
            $table->unsignedTinyInteger('storeys');
            $table->unsignedTinyInteger('garages');

            $table->timestamps();

            // index
            $table->index('price');
            $table->index('bedrooms');
            $table->index('bathrooms');
            $table->index('storeys');
            $table->index('garages');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('re_properties');
    }
};
