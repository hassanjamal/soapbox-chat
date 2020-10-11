<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WorkSpaceTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    /** @test */
    function user_can_create_a_workspace()
    {
        $this->withoutExceptionHandling();
        $user = User::factory([])->create();

        $this->actingAs($user)->json('POST', route('workspace.store'), [
            'name' => 'ABC Org'
        ])->assertOk();

        $this->assertDatabaseHas('workspaces', [
            'name' => 'ABC Org'
        ]);
    }

    /** @test */
    function name_is_required_to_create_a_workspace()
    {

        $user = User::factory([])->create();

        $this->actingAs($user)->json('POST',
            route('workspace.store'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors('name');

    }

    /** @test */
    function name_is_unique_for_a_workspace()
    {
        $workSpaceA = Workspace::factory(['name' => 'ABC Org'])->create();

        tap(Workspace::first(), function($workspace) use ($workSpaceA) {
            $this->assertEquals($workSpaceA->name, $workspace->name);
        });

        $user = User::factory([])->create();

        $this->actingAs($user)->json('POST',
            route('workspace.store'),
            [
                'name' => $workSpaceA->name
            ])
             ->assertStatus(422)
             ->assertJsonValidationErrors('name');
    }

    /** @test */
    function a_workspace_can_have_multiple_users()
    {
        $workSpaceA = Workspace::factory(['name' => 'ABC Org'])->create();
        $users = User::factory()->count(2)->create();

        $workSpaceA->users()->attach($users->pluck('id'));

        tap(Workspace::with('users')->first()->users, function($users) {
            $this->assertCount(2, $users);
        });
    }


    /** @test */
    function a_user_can_belongs_to_multiple_workspace()
    {
        $workSpaces = Workspace::factory()->count(2)->create();
        $user = User::factory([])->create();

        $user->workspaces()->attach($workSpaces->pluck('id'));

        tap(User::with('workspaces')->first()->workspaces, function($workspaces) {
            $this->assertCount(2, $workspaces);
        });
    }

    /** @test */
    function a_workspace_can_have_multiple_channels()
    {
        $workSpace = Workspace::factory(['name' => 'ABC Org'])
                              ->has(Channel::factory()->count(2))
                              ->create();
        $this->assertCount(2, $workSpace->channels);
    }
}
