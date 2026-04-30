<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extends the shared `orders` table with CRM fulfilment fields.
     * Non-destructive — only adds columns if absent.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'packing_notes')) {
                $table->text('packing_notes')->nullable()->after('fulfilment_notes');
            }
            if (! Schema::hasColumn('orders', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('packing_notes');
            }
            if (! Schema::hasColumn('orders', 'return_received_at')) {
                $table->timestamp('return_received_at')->nullable()->after('return_reason');
            }
            if (! Schema::hasColumn('orders', 'return_handled_by')) {
                $table->foreignId('return_handled_by')->nullable()->constrained('staff')->nullOnDelete()->after('return_received_at');
            }
            if (! Schema::hasColumn('orders', 'failed_delivery_notes')) {
                $table->text('failed_delivery_notes')->nullable();
            }
            if (! Schema::hasColumn('orders', 'reship_order_id')) {
                $table->foreignId('reship_order_id')->nullable()->constrained('orders')->nullOnDelete();
            }
            // Royal Mail manifest reference
            if (! Schema::hasColumn('orders', 'manifest_batch')) {
                $table->string('manifest_batch')->nullable();
            }
        });
    }

    public function down(): void {}
};
