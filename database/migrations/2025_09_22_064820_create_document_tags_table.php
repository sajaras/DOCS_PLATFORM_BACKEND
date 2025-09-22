<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTagsTable extends Migration
{
    public function up()
    {
        Schema::create('document_tags', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');

            $table->primary(['document_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_tags');
    }
}