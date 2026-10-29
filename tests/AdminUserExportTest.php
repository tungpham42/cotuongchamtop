<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserExportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guests_cannot_export(): void
    {
        $this->get(route('admin.users.export'))->assertRedirect();
    }

    public function test_non_admins_cannot_export(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.users.export'))
            ->assertStatus(403);
    }

    public function test_admin_downloads_csv_with_name_and_email(): void
    {
        User::factory()->create(['name' => 'Nguyễn Văn A', 'email' => 'a@example.com']);

        $response = $this->actingAs($this->admin())->get(route('admin.users.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Name,Email', $csv);
        $this->assertStringContainsString('Nguyễn Văn A,a@example.com', $csv);
    }

    public function test_export_respects_search_and_role_filters(): void
    {
        User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.users.export', ['search' => 'alice']))
            ->streamedContent();

        $this->assertStringContainsString('alice@example.com', $csv);
        $this->assertStringNotContainsString('bob@example.com', $csv);
    }

    public function test_formula_injection_is_neutralised(): void
    {
        User::factory()->create(['name' => '=HYPERLINK("http://evil")', 'email' => 'x@example.com']);

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.users.export'))
            ->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }
}
