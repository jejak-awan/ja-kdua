<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create radius schema if not exists (Postgres specific)
        if (config('database.default') === 'pgsql') {
            DB::statement('CREATE SCHEMA IF NOT EXISTS radius');
        }

        $connection = 'radius';

        // 2. Table: nas
        Schema::connection($connection)->create('radius.nas', function (Blueprint $table) {
            $table->id();
            $table->string('nasname', 128)->index();
            $table->string('shortname', 32)->nullable();
            $table->string('type', 30)->default('other');
            $table->integer('ports')->nullable();
            $table->string('secret', 60);
            $table->string('server', 64)->nullable();
            $table->string('community', 50)->nullable();
            $table->string('description', 200)->default('radius device');
        });

        // 3. Table: radcheck (Auth Check)
        Schema::connection($connection)->create('radius.radcheck', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->index();
            $table->string('attribute', 64);
            $table->char('op', 2)->default('==');
            $table->string('value', 253);
        });

        // 4. Table: radreply (Auth Reply)
        Schema::connection($connection)->create('radius.radreply', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->index();
            $table->string('attribute', 64);
            $table->char('op', 2)->default(':=');
            $table->string('value', 253);
        });

        // 5. Table: radgroupcheck
        Schema::connection($connection)->create('radius.radgroupcheck', function (Blueprint $table) {
            $table->id();
            $table->string('groupname', 64)->index();
            $table->string('attribute', 64);
            $table->char('op', 2)->default('==');
            $table->string('value', 253);
        });

        // 6. Table: radgroupreply
        Schema::connection($connection)->create('radius.radgroupreply', function (Blueprint $table) {
            $table->id();
            $table->string('groupname', 64)->index();
            $table->string('attribute', 64);
            $table->char('op', 2)->default(':=');
            $table->string('value', 253);
        });

        // 7. Table: radusergroup
        Schema::connection($connection)->create('radius.radusergroup', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->index();
            $table->string('groupname', 64)->index();
            $table->integer('priority')->default(1);
        });

        // 8. Table: radacct (Accounting)
        Schema::connection($connection)->create('radius.radacct', function (Blueprint $table) {
            $table->bigIncrements('radacctid');
            $table->string('acctsessionid', 64)->index();
            $table->string('acctuniqueid', 32)->unique();
            $table->string('username', 64)->index();
            $table->string('groupname', 64)->nullable();
            $table->string('realm', 64)->nullable();
            $table->string('nasipaddress', 15)->index();
            $table->string('nasportid', 15)->nullable();
            $table->string('nasporttype', 32)->nullable();
            $table->timestamp('acctstarttime')->nullable()->index();
            $table->timestamp('acctupdatetime')->nullable();
            $table->timestamp('acctstoptime')->nullable()->index();
            $table->integer('acctinterval')->nullable();
            $table->bigInteger('acctsessiontime')->nullable();
            $table->string('acctauthentic', 32)->nullable();
            $table->string('connectinfo_start', 50)->nullable();
            $table->string('connectinfo_stop', 50)->nullable();
            $table->bigInteger('acctinputoctets')->nullable();
            $table->bigInteger('acctoutputoctets')->nullable();
            $table->string('calledstationid', 50)->nullable();
            $table->string('callingstationid', 50)->nullable();
            $table->string('acctterminatecause', 32)->nullable();
            $table->string('servicetype', 32)->nullable();
            $table->string('framedprotocol', 32)->nullable();
            $table->string('framedipaddress', 15)->nullable();
        });

        // 9. Table: radpostauth (Post Authentication logs)
        Schema::connection($connection)->create('radius.radpostauth', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->index();
            $table->string('pass', 64)->nullable();
            $table->string('reply', 32)->nullable();
            $table->timestamp('authdate')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = 'radius';
        Schema::connection($connection)->dropIfExists('radius.radpostauth');
        Schema::connection($connection)->dropIfExists('radius.radacct');
        Schema::connection($connection)->dropIfExists('radius.radusergroup');
        Schema::connection($connection)->dropIfExists('radius.radgroupreply');
        Schema::connection($connection)->dropIfExists('radius.radgroupcheck');
        Schema::connection($connection)->dropIfExists('radius.radreply');
        Schema::connection($connection)->dropIfExists('radius.radcheck');
        Schema::connection($connection)->dropIfExists('radius.nas');
        
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP SCHEMA IF EXISTS radius CASCADE');
        }
    }
};
