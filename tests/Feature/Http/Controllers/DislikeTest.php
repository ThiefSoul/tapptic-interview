<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DislikeTest extends TestCase
{

    private User $user;
    private User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedThenDislikeShouldBeCreated()
    {
        // when
        $this->performDislike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertTrue($this->user->doesDislikeUser($this->anotherUser));
        $this->assertTrue($this->anotherUser->isDislikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedOnLikedUserThenUserBecomesDislikedInsteadOfLiked()
    {
        // given
        $this->user->likeUser($this->anotherUser);

        // when
        $this->performDislike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertFalse($this->user->doesLikeUser($this->anotherUser));
        $this->assertFalse($this->anotherUser->isLikedByUser($this->user));
        // and
        $this->assertTrue($this->user->doesDislikeUser($this->anotherUser));
        $this->asserttrue($this->anotherUser->isDislikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedOnUserWhoLikedAlreadyThenDislikeBetweenUsersShouldBeCreated()
    {
        // given
        $this->anotherUser->likeUser($this->user);

        // when
        $this->performDislike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertTrue($this->anotherUser->doesLikeUser($this->user));
        $this->assertTrue($this->user->isLikedByUser($this->anotherUser));
        // and
        $this->assertTrue($this->user->doesDislikeUser($this->anotherUser));
        $this->assertTrue($this->anotherUser->isDislikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedOnAlreadyDislikedUserThenNothingShouldHappen()
    {
        // given
        $this->user->dislikeUser($this->anotherUser);

        // when
        $response = $this->performDislike($this->user->id, $this->anotherUser->id);

        // then
        $response->assertOk();
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedOnAlreadyPairedUsersThenUnprocessableEntityErrorShouldBeReturned()
    {
        // given
        $this->user->pairWithUser($this->anotherUser);

        // when
        $response = $this->performDislike($this->user->id, $this->anotherUser->id);

        // then
        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function whenDislikeIsPerformedOnNotExistingUserThenNotFoundErrorShouldBeReturned()
    {
        // when
        $response = $this->performDislike($this->user->id, 'not existing user');

        // then
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function userCanNotDislikeHimself()
    {
        // when
        $response = $this->performDislike($this->user->id, $this->user->id);

        // then
        $response->assertUnprocessable();
    }

    // UTILS

    private function performDislike(int|string $performerId, int|string $dislikedId): TestResponse
    {
        return $this->postJson(route('handle'), [
            'userA' => $performerId,
            'userB' => $dislikedId,
            'action' => 'dislike',
        ]);
    }

}
