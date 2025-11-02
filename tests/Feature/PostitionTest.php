<?php

namespace Tests\Feature;

use App\Models\Position;

it("can be created", function () {
    $postion = Position::factory()->create();
    expect($postion->id)->toBeGreaterThan(0);
});
