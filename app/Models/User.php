<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function swipes(): HasMany
    {
        return $this->hasMany(Swipe::class, 'author_id', 'id');
    }

    public function likes(): HasMany
    {
        return $this->swipes()->where('attitude', Swipe::ATTITUDE_LIKE);
    }

    public function dislikes(): HasMany
    {
        return $this->swipes()->where('attitude', Swipe::ATTITUDE_DISLIKE);
    }

    public function pairs(): Builder
    {
        return Pair::query()
            ->where('user_1_id', $this->id)
            ->orWhere('user_2_id', $this->id);
    }

    public function likeUser(User $user): void
    {
        if ($this->id === $user->id) {
            abort(422, 'User can\'t like himself.');
        }

        if ($this->doesLikeUser($user)) {
            return;
        }

        if ($this->isPairedWithUser($user)) {
            abort(422, sprintf('User id:%d is already paired with user id:%d.', $this->id, $user->id));
        }

        if ($this->isLikedByUser($user)) {
            $this->pairWithUser($user);
            return;
        }

        if ($this->doesDislikeUser($user)) {
            $this->dislikes()->where('receiver_id', $user->id)->update([
                'attitude' => Swipe::ATTITUDE_LIKE
            ]);
        }

        $this->swipes()->create(['receiver_id' => $user->id, 'attitude' => Swipe::ATTITUDE_LIKE]);
    }

    public function isLikedByUser(User $user): bool
    {
        return $user->likes()->where('receiver_id', $this->id)->exists();
    }

    public function doesLikeUser(User $user): bool
    {
        return $this->likes()->where('receiver_id', $user->id)->exists();
    }

    public function isPairedWithUser(User $user): bool
    {
        return $this->pairs()
            ->where('user_1_id', $user->id)
            ->orWhere('user_2_id', $user->id)
            ->exists();
    }

    public function pairWithUser(User $user): void
    {
        $user->swipes()->where('receiver_id', $this->id)->delete();
        Pair::query()->create([
            'user_1_id' => $this->id,
            'user_2_id' => $user->id,
        ]);
    }

    public function dislikeUser(User $user): void
    {
        if ($this->id === $user->id) {
            abort(422, 'User can\'t dislike himself.');
        }

        if ($this->isPairedWithUser($user)) {
            abort(422, sprintf('User id:%d is already paired with user id:%d.', $this->id, $user->id));
        }

        if ($this->doesLikeUser($user)) {
            $this->likes()->where('receiver_id', $user->id)->update([
                'attitude' => Swipe::ATTITUDE_DISLIKE
            ]);
        }

        $this->swipes()->create(['receiver_id' => $user->id, 'attitude' => Swipe::ATTITUDE_DISLIKE]);
    }

    public function isDislikedByUser(User $user): bool
    {
        return $user->dislikes()->where('receiver_id', $this->id)->exists();
    }

    public function doesDislikeUser(User $user): bool
    {
        return $this->dislikes()->where('receiver_id', $user->id)->exists();
    }
}
