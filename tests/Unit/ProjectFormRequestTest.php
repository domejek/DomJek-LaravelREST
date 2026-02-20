<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_project_request_authorize_gibt_true_zurueck(): void
    {
        $request = new StoreProjectRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_store_project_request_rules_enthaelt_felder(): void
    {
        $request = new StoreProjectRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('description', $rules);
    }

    public function test_store_project_request_name_validation(): void
    {
        $request = new StoreProjectRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('string', $rules['name']);
        $this->assertStringContainsString('max:255', $rules['name']);
    }

    public function test_store_project_request_description_validation(): void
    {
        $request = new StoreProjectRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['description']);
        $this->assertStringContainsString('string', $rules['description']);
    }

    public function test_update_project_request_authorize_gibt_true_zurueck(): void
    {
        $request = new UpdateProjectRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_update_project_request_rules_verwendet_sometimes(): void
    {
        $request = new UpdateProjectRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('sometimes', $rules['name']);
    }

    public function test_update_project_request_name_validation(): void
    {
        $request = new UpdateProjectRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('string', $rules['name']);
        $this->assertStringContainsString('max:255', $rules['name']);
    }

    public function test_update_project_request_description_validation(): void
    {
        $request = new UpdateProjectRequest;
        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['description']);
        $this->assertStringContainsString('string', $rules['description']);
    }
}
