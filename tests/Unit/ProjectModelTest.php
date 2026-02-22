<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_projekt_hat_viele_aufgaben(): void
    {
        $benutzer = User::factory()->create();
        $projekt = Project::factory()->create();
        Task::factory()->count(3)->create(['project_id' => $projekt->id, 'user_id' => $benutzer->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $projekt->tasks);
        $this->assertCount(3, $projekt->tasks);
    }

    public function test_projekt_hat_ausfuellbare_attribute(): void
    {
        $projekt = new Project;
        $fillable = $projekt->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('description', $fillable);
    }

    public function test_projekt_kann_mit_factory_erstellt_werden(): void
    {
        $projekt = Project::factory()->create();

        $this->assertDatabaseHas('projects', [
            'id' => $projekt->id,
            'name' => $projekt->name,
        ]);
    }

    public function test_projekt_kann_null_beschreibung_haben(): void
    {
        $projekt = Project::factory()->create(['description' => null]);

        $this->assertNull($projekt->description);
    }
}
