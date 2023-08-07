<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefaultF77f78796cd182080a291712956b5efe extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('webhook_calls')
            ->addColumn('created_at', 'datetime', ['nullable' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['nullable' => false, 'default' => null])
            ->addColumn('id', 'primary', ['nullable' => false, 'default' => null])
            ->addColumn('name', 'string', ['nullable' => false, 'default' => null])
            ->addColumn('url', 'string', ['nullable' => false, 'default' => null])
            ->addColumn('headers', 'json', ['nullable' => false, 'default' => null])
            ->addColumn('payload', 'json', ['nullable' => false, 'default' => null])
            ->addColumn('exception', 'json', ['nullable' => true, 'default' => null])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('webhook_calls')->drop();
    }
}
