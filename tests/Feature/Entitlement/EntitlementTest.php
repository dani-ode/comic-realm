<?php

use App\Domain\Comic\Models\Chapter;
use App\Domain\Comic\Models\Comic;
use App\Domain\Entitlement\Actions\GrantChapterEntitlement;
use App\Domain\User\Models\User;

it('grants chapter entitlement to user and shows in library', function () {
    $user = User::factory()->create();
    $comic = Comic::factory()->create();
    $paidChapter = Chapter::factory()->paid(5000)->create(['comic_id' => $comic->id]);

    $grantAction = app(GrantChapterEntitlement::class);
    $entitlement = $grantAction->execute($user, $comic->id, $paidChapter->id);

    expect($entitlement)->not->toBeNull()
        ->and($entitlement->user_id)->toBe($user->id);

    $this->assertDatabaseHas('entitlements', [
        'user_id' => $user->id,
        'chapter_id' => $paidChapter->id,
    ]);

    $response = $this->actingAs($user)->get('/library');

    $response->assertStatus(200);
});
