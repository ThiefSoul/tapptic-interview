<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class ApiControllerTest extends TestCase
{

    /**
     * @test
     */
    public function whenUnknownActionIsPerformedThenBadRequestErrorShouldBeReturned()
    {
        // when
        $response = $this->postJson(route('handle'), [
            'action' => 'not existing action',
        ]);

        // then
        $response->assertStatus(400);
    }

}
