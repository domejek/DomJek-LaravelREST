<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_gehoert_zu_benutzer(): void
    {
        $benutzer = User::factory()->create();
        $aufgabe = Task::factory()->create(['user_id' => $benutzer->id]);

        $this->assertInstanceOf(User::class, $aufgabe->user);
        $this->assertEquals($benutzer->id, $aufgabe->user->id);
    }

    public function test_task_gehoert_zu_projekt(): void
    {
        $projekt = Project::factory()->create();
        $aufgabe = Task::factory()->create(['project_id' => $projekt->id]);

        $this->assertInstanceOf(Project::class, $aufgabe->project);
        $this->assertEquals($projekt->id, $aufgabe->project->id);
    }

    public function test_task_kann_faelligkeitsdatum_haben(): void
    {
        $faelligkeitsdatum = now()->addDays(7);
        $aufgabe = Task::factory()->create(['deadline' => $faelligkeitsdatum]);

        $this->assertNotNull($aufgabe->deadline);
        $this->assertInstanceOf(\Carbon\Carbon::class, $aufgabe->deadline);
    }

    public function test_task_hat_ausfuellbare_attribute(): void
    {
        $aufgabe = new Task;
        $fillable = $aufgabe->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('project_id', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('deadline', $fillable);
    }

    public function test_task_kann_mit_factory_erstellt_werden(): void
    {
        $aufgabe = Task::factory()->create();

        $this->assertDatabaseHas('tasks', [
            'id' => $aufgabe->id,
            'title' => $aufgabe->title,
        ]);
    }

    public function test_task_hat_standard_status(): void
    {
        $aufgabe = Task::factory()->create();

        $this->assertEquals('todo', $aufgabe->status);
    }
}
