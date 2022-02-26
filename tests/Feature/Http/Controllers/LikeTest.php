<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LikeTest extends TestCase
{

    private User $user;
    private User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    }

    // liking

    /**
     * @test
     */
    public function whenLikeIsPerformedThenLikeBetweenUsersShouldBeCreated()
    {
        // when
        $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertTrue($this->user->doesLikeUser($this->anotherUser));
        $this->assertTrue($this->anotherUser->isLikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnDislikedUserThenUserBecomesLikedInsteadOfDisliked()
    {
        // given
        $this->user->dislikeUser($this->anotherUser);

        // when
        $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertFalse($this->user->doesDislikeUser($this->anotherUser));
        $this->assertFalse($this->anotherUser->isDislikedByUser($this->user));
        // and
        $this->assertTrue($this->user->doesLikeUser($this->anotherUser));
        $this->asserttrue($this->anotherUser->isLikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnUserWhoDislikedAlreadyThenLikeBetweenUsersShouldBeCreated()
    {
        // given
        $this->anotherUser->dislikeUser($this->user);

        // when
        $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertTrue($this->anotherUser->doesDislikeUser($this->user));
        $this->assertTrue($this->user->isDislikedByUser($this->anotherUser));
        // and
        $this->assertTrue($this->user->doesLikeUser($this->anotherUser));
        $this->assertTrue($this->anotherUser->isLikedByUser($this->user));
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnAlreadyLikedUserThenNothingShouldHappen()
    {
        // given
        $this->user->likeUser($this->anotherUser);

        // when
        $response = $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $response->assertOk();
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnAlreadyPairedUsersThenUnprocessableEntityErrorShouldBeReturned()
    {
        // given
        $this->user->pairWithUser($this->anotherUser);

        // when
        $response = $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $response->assertUnprocessable();
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnNotExistingUserThenNotFoundErrorShouldBeReturned()
    {
        // when
        $response = $this->performLike($this->user->id, 'not existing user');

        // then
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function userCanNotLikeHimself()
    {
        // when
        $response = $this->performLike($this->user->id, $this->user->id);

        // then
        $response->assertUnprocessable();
    }

    // pairing

    /**
     * @test
     */
    public function whenLikeIsPerformedOnAnotherUserWhoLikesUserThenPairShouldBeCreated()
    {
        // given
        $this->anotherUser->likeUser($this->user);

        // when
        $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertTrue($this->user->isPairedWithUser($this->anotherUser));
        $this->assertTrue($this->anotherUser->isPairedWithUser($this->user));
    }

    /**
     * @test
     */
    public function whenLikeIsPerformedOnAnotherUserWhoLikesUserThenLikeShouldBeRemoved()
    {
        // given
        $this->anotherUser->likeUser($this->user);

        // when
        $this->performLike($this->user->id, $this->anotherUser->id);

        // then
        $this->assertFalse($this->user->doesLikeUser($this->anotherUser));
        $this->assertFalse($this->user->isLikedByUser($this->anotherUser));
    }

    // UTILS

    private function performLike(int|string $performerId, int|string $likedId): TestResponse
    {
        return $this->postJson(route('handle'), [
            'userA' => $performerId,
            'userB' => $likedId,
            'action' => 'like',
        ]);
    }

}
