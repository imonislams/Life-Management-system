<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers the "Remove current profile photo" option on Settings -> Profile.
 *
 * The option must (a) delete the file from the public disk, (b) clear
 * users.profile_photo_path so the avatar falls back to the initial letter,
 * and (c) never touch the photo of a user who did not ask for removal.
 */
class ProfilePhotoRemovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    protected function userWithPhoto(string $name = 'Jane Doe'): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => uniqid('photo', true) . '@example.test',
            'profile_photo_path' => "avatars/{$name}.jpg",
        ]);
    }

    public function test_profile_page_shows_the_remove_option_only_when_a_photo_exists(): void
    {
        $withPhoto = $this->userWithPhoto('Has Photo');
        Storage::disk('public')->put($withPhoto->profile_photo_path, 'fake-image');

        $this->actingAs($withPhoto)
            ->get('/settings/profile')
            ->assertOk()
            ->assertSee('Remove current profile photo');

        $withoutPhoto = $this->userWithPhoto('No Photo');
        $withoutPhoto->update(['profile_photo_path' => null]);

        $this->actingAs($withoutPhoto)
            ->get('/settings/profile')
            ->assertOk()
            ->assertDontSee('Remove current profile photo');
    }

    public function test_checking_remove_photo_deletes_the_file_and_clears_the_path(): void
    {
        $user = $this->userWithPhoto();
        Storage::disk('public')->put($user->profile_photo_path, 'fake-image');

        $this->actingAs($user)->put('/settings/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_photo' => '1',
        ])->assertRedirect(route('settings.profile'));

        Storage::disk('public')->assertMissing('avatars/Jane Doe.jpg');

        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_photo_survives_a_save_that_does_not_request_removal(): void
    {
        $user = $this->userWithPhoto();
        Storage::disk('public')->put($user->profile_photo_path, 'fake-image');

        $this->actingAs($user)->put('/settings/profile', [
            'name' => 'Jane Renamed',
            'email' => $user->email,
        ])->assertRedirect(route('settings.profile'));

        Storage::disk('public')->assertExists('avatars/Jane Doe.jpg');

        $user->refresh();
        $this->assertEquals('avatars/Jane Doe.jpg', $user->profile_photo_path);
        $this->assertEquals('Jane Renamed', $user->name);
    }

    public function test_uploading_a_new_photo_replaces_the_old_file(): void
    {
        $user = $this->userWithPhoto();
        Storage::disk('public')->put($user->profile_photo_path, 'fake-image');

        $this->actingAs($user)->put('/settings/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('new-avatar.jpg', 128, 128),
        ])->assertRedirect(route('settings.profile'));

        Storage::disk('public')->assertMissing('avatars/Jane Doe.jpg');

        $newPath = $user->refresh()->profile_photo_path;
        $this->assertNotEquals('avatars/Jane Doe.jpg', $newPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_topbar_falls_back_to_the_initial_after_removal(): void
    {
        $user = $this->userWithPhoto('Ada Lovelace');
        Storage::disk('public')->put($user->profile_photo_path, 'fake-image');

        $this->actingAs($user)->put('/settings/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_photo' => '1',
        ]);

        // The layout no longer finds a file, so it renders the letter avatar.
        $this->actingAs($user->refresh())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>A</span>', false);
    }
}
