<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSchemas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement('alter schema public rename to general'); // have to do it manually
        DB::statement('CREATE SCHEMA IF NOT EXISTS operations');  
        DB::statement('CREATE SCHEMA IF NOT EXISTS hr');
        DB::statement('CREATE SCHEMA IF NOT EXISTS finance');
        DB::statement('CREATE SCHEMA IF NOT EXISTS views');
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
        // DB::statement('alter schema general rename to public'); // have to do it manually
        DB::statement('DROP SCHEMA IF EXISTS hr CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS finance CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS views CASCADE');
        
    }
}
