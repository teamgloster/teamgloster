<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Rooms module removed. Existing databases drop the table in
     * 2026_08_15_000003_drop_rooms_and_section_room_id.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
