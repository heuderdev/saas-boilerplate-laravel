<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // your custom columns may go here

            // Dados customizados do Tenant
            $table->string('name')->nullable();

            // O Dono do Tenant (Quem paga a conta)
            // Como a tabela users já existe, podemos criar a FK direto aqui
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');

            // --- COLUNAS DO STANCL (Obrigatórias) ---
            $table->json('data')->nullable();

            // --- COLUNAS DO CASHIER (Consolidadas aqui - Opção A) ---
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_tenant_id']);
        });

        Schema::dropIfExists('tenants');
    }
}
