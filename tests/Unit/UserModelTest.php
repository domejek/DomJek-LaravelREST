<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_benutzer_hat_viele_aufgaben(): void
    {
        $benutzer = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $benutzer->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $benutzer->tasks);
        $this->assertCount(3, $benutzer->tasks);
    }

    public function test_ist_admin_gibt_true_fuer_admin_zurueck(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_ist_admin_gibt_false_fuer_normalen_benutzer_zurueck(): void
    {
        $benutzer = User::factory()->create(['role' => 'user']);

        $this->assertFalse($benutzer->isAdmin());
    }

    public function test_benutzer_hat_standard_rolle(): void
    {
        $benutzer = User::factory()->create();

        $this->assertEquals('user', $benutzer->role);
    }

    public function test_benutzer_hat_ausfuellbare_attribute(): void
    {
        $benutzer = new User;
        $fillable = $benutzer->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
    }
}
