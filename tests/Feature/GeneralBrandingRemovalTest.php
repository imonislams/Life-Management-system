<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers the "Remove current logo / favicon" checkboxes on Settings -> General.
 *
 * Selecting one must delete the file from the public disk and clear the stored
 * path, so the sidebar falls back to the brand name and the page stops
 * advertising a file that is gone.
 */
class GeneralBrandingRemovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    protected function userWithBranding(): array
    {
        $user = User::factory()->create();

        $settings = Setting::forUser($user->id);
        $settings->update([
            'logo_path' => 'branding/logo.png',
            'favicon_path' => 'branding/favicon.png',
        ]);

        Storage::disk('public')->put('branding/logo.png', 'fake-image');
        Storage::disk('public')->put('branding/favicon.png', 'fake-image');

        return [$user, $settings];
    }

    /** Payload that satisfies the required general settings fields. */
    protected function basePayload(User $user, array $extra = []): array
    {
        $settings = Setting::forUser($user->id);

        return array_merge([
            'app_name' => $settings->app_name,
            'language' => $settings->language,
            'timezone' => $settings->timezone,
            'date_format' => $settings->date_format,
            'time_format' => $settings->time_format,
            'week_start' => $settings->week_start,
        ], $extra);
    }

    public function test_remove_options_show_only_when_branding_exists(): void
    {
        [$user] = $this->userWithBranding();

        $this->actingAs($user)
            ->get('/settings/general')
            ->assertOk()
            ->assertSee('Remove current logo')
            ->assertSee('Remove current favicon');

        $bare = User::factory()->create();

        $this->actingAs($bare)
            ->get('/settings/general')
            ->assertOk()
            ->assertDontSee('Remove current logo')
            ->assertDontSee('Remove current favicon');
    }

    public function test_checking_remove_logo_deletes_the_file_and_clears_the_path(): void
    {
        [$user, $settings] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'remove_logo' => '1',
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertMissing('branding/logo.png');
        $this->assertNull($settings->refresh()->logo_path);

        // The favicon was not selected, so it must survive untouched.
        Storage::disk('public')->assertExists('branding/favicon.png');
        $this->assertEquals('branding/favicon.png', $settings->favicon_path);
    }

    public function test_checking_remove_favicon_deletes_the_file_and_clears_the_path(): void
    {
        [$user, $settings] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'remove_favicon' => '1',
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertMissing('branding/favicon.png');
        $this->assertNull($settings->refresh()->favicon_path);

        Storage::disk('public')->assertExists('branding/logo.png');
        $this->assertEquals('branding/logo.png', $settings->logo_path);
    }

    public function test_both_can_be_removed_in_one_save(): void
    {
        [$user, $settings] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'remove_logo' => '1',
            'remove_favicon' => '1',
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertMissing('branding/logo.png');
        Storage::disk('public')->assertMissing('branding/favicon.png');

        $settings->refresh();
        $this->assertNull($settings->logo_path);
        $this->assertNull($settings->favicon_path);
    }

    public function test_branding_survives_a_save_that_does_not_request_removal(): void
    {
        [$user, $settings] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'app_name' => 'Renamed Workspace',
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertExists('branding/logo.png');
        Storage::disk('public')->assertExists('branding/favicon.png');

        $settings->refresh();
        $this->assertEquals('branding/logo.png', $settings->logo_path);
        $this->assertEquals('branding/favicon.png', $settings->favicon_path);
        $this->assertEquals('Renamed Workspace', $settings->app_name);
    }

    public function test_a_new_upload_replaces_the_old_file(): void
    {
        [$user, $settings] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'logo' => UploadedFile::fake()->image('new-logo.png', 64, 64),
            'favicon' => UploadedFile::fake()->image('new-favicon.png', 32, 32),
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertMissing('branding/logo.png');
        Storage::disk('public')->assertMissing('branding/favicon.png');

        $settings->refresh();
        Storage::disk('public')->assertExists($settings->logo_path);
        Storage::disk('public')->assertExists($settings->favicon_path);
    }

    public function test_upload_wins_over_removal_in_the_same_submission(): void
    {
        [$user, $settings] = $this->userWithBranding();

        // A fresh file plus the remove checkbox: the upload is the clear intent,
        // and the old file must still be replaced rather than orphaned.
        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'remove_logo' => '1',
            'logo' => UploadedFile::fake()->image('replacement.png', 64, 64),
        ]))->assertRedirect(route('settings.general'));

        Storage::disk('public')->assertMissing('branding/logo.png');

        $newPath = $settings->refresh()->logo_path;
        $this->assertNotNull($newPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_sidebar_falls_back_to_the_brand_name_after_removal(): void
    {
        [$user] = $this->userWithBranding();

        $this->actingAs($user)->put('/settings/general', $this->basePayload($user, [
            'remove_logo' => '1',
        ]));

        // The layout can no longer find a logo file, so it renders the brand text.
        $this->actingAs($user->refresh())
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('branding/logo.png');
    }
}
