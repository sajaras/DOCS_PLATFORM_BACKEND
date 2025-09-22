<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password');
            $table->string('phone_number',10)->unique();
            $table->datetime('phone_verified_at')->nullable();
            $table->boolean('is_platform_admin')->default(false);
            $table->foreignId('organization_id')->constrained('organizations');
            $table->text('profile_pic_path')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

            // Add a CHECK constraint to enforce the business rule
        DB::statement('
            ALTER TABLE users
            ADD CONSTRAINT check_user_role_organization
            CHECK (
                (is_platform_admin = TRUE AND organization_id IS NULL) OR
                (is_platform_admin = FALSE AND organization_id IS NOT NULL)
            )
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}