<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dns_queries', function (Blueprint $table) {
            $table->id();

            $table->string('query_id', 64)->unique();

            $table->dateTime('event_time', precision: 6)->index();

            $table->string('client_ip', 45)->index();
            $table->string('client_name')->default('')->index();
            $table->string('vlan_name')->default('')->index();
            $table->string('client_proto', 50)->default('');

            $table->string('domain', 253)->index();

            $table->string('query_class', 20)->default('');
            $table->string('query_type', 20)->default('')->index();

            $table->string('status', 100)->default('')->index();
            $table->string('reason', 100)->default('')->index();

            $table->unsignedBigInteger('filter_id')->default(0)->index();
            $table->text('matched_rule')->nullable();

            $table->boolean('cached')->default(false)->index();

            $table->double('elapsed_ms')->default(0);

            $table->string('upstream', 255)->default('')->index();

            $table->boolean('answer_dnssec')->default(false);
            $table->boolean('disallowed')->default(false)->index();

            $table->text('disallowed_rule')->nullable();

            $table->longText('client_whois_json')->nullable();
            $table->longText('answers_json')->nullable();
            $table->longText('rules_json')->nullable();
            $table->longText('raw_json')->nullable();

            $table->timestamp('ingested_at')->useCurrent();

            /*
             * Composite indexes untuk meniru pola filter yang nantinya
             * sering digunakan pada Query Log.
             */
            $table->index(
                ['event_time', 'client_ip'],
                'idx_dns_queries_time_client'
            );

            $table->index(
                ['event_time', 'vlan_name'],
                'idx_dns_queries_time_vlan'
            );

            $table->index(
                ['event_time', 'status'],
                'idx_dns_queries_time_status'
            );

            $table->index(
                ['event_time', 'disallowed'],
                'idx_dns_queries_time_disallowed'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dns_queries');
    }
};