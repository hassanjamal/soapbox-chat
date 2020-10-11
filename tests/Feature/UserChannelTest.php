<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserChannelTest extends TestCase
{
    protected $firstUser;
    protected $secondUser;
    protected $workspace;
    protected $channel;
    public function setUp(): void
    {
        parent::setUp();
        $this->workspace = Workspace::factory(['name' => 'ABC Org'])
                              ->has(Channel::factory(['name' => 'ABC Channel']))
                              ->has(User::factory()->count(2))
                              ->create();
        $this->firstUser = collect($this->workspace->users)->first();
        $this->secondUser = collect($this->workspace->users)->last();
        $this->channel = collect($this->workspace->channels)->first();
    }

    use RefreshDatabase;

    /** @test */
    function a_channel_can_have_multiple_users()
    {
        $workSpace = Workspace::factory(['name' => 'XYG Org'])
                              ->has(Channel::factory(['name' => 'XYG Channel']))
                              ->create();
        $channel = $workSpace->channels()->first();
        $users = User::factory()->count(2)->create();

        $channel->users()->attach($users->pluck('id'));

        tap(Channel::with('users')->find($channel->id)->users, function($users) {
            $this->assertCount(2, $users);
        });
    }


    /** @test */
    function a_user_can_belongs_to_multiple_channels()
    {
        $workSpace = Workspace::factory(['name' => 'XYG Org'])
                              ->has(Channel::factory([])->count(2))
                              ->create();
        $user = User::factory([])->create();

        $user->channels()->attach($workSpace->channels()->pluck('id'));

        tap(User::with('channels')->find($user->id)->channels, function($channels) {
            $this->assertCount(2, $channels);
        });
    }

    /** @test */
    function a_user_can_add_other_users_from_the_workspace_to_the_channel_within_same_workspace()
    {

        $this->channel->users()->attach($this->firstUser->id);

        $this->actingAs($this->firstUser)->json('POST',
            route('channels.users.store'),
            [
                'user_id' => $this->secondUser->id,
                'channel_id' => $this->channel->id,
                'workspace_id' => $this->workspace->id
            ])
             ->assertOk();

        $this->assertTrue($this->channel->is($this->secondUser->channels()->first()));
    }

    /** @test */
    function a_user_can_not_add_other_users_from_the_workspace_to_the_channel_where_he_does_have_access__within_same_workspace()
    {
        $this->actingAs($this->firstUser)->json('POST',
            route('channels.users.store'),
            [
                'user_id' => $this->secondUser->id,
                'channel_id' => $this->channel->id,
                'workspace_id' => $this->workspace->id
            ])
             ->assertStatus(422);
    }

    /** @test */
    function a_user_can_not_add_other_users_from_the_another_workspace_when_another_user_does_not_have_access_to_be_added_workspace()
    {
        $this->channel->users()->attach($this->firstUser->id);

        $anotherWorkSpace = Workspace::factory(['name' => 'Another Org'])
                                    ->has(User::factory())
                                    ->create();
        $userFromAnotherWorkspace = $anotherWorkSpace->users()->first();

        $this->actingAs($this->firstUser)->json('POST',
            route('channels.users.store'),
            [
                'user_id' => $userFromAnotherWorkspace->id,
                'channel_id' => $this->channel->id,
                'workspace_id' => $this->workspace->id
            ])
             ->assertStatus(422);
    }

    /** @test */
    function a_user_can_see_all_channels_from_his_workspace()
    {
        Channel::factory(['workspace_id' => $this->workspace->id])->create();

        $this->actingAs($this->firstUser)->json('GET',
            route('channel.index',['workspace_id' => $this->workspace->id]))
            ->assertOk()
            ->assertJsonCount(2);

    }

    /** @test */
    function a_user_can_see_all_activity_from_the_channels_he_belongs()
    {
        $this->channel->users()->attach($this->firstUser->id);

        $this->actingAs($this->firstUser)->json('GET',
            route('channel.activity',['workspace_id' => $this->workspace->id, 'channel_id' => $this->channel->id]))
             ->assertOk()
            ->assertJsonCount(2)
            ->assertExactJson([
                1 => 'First Activity',
                2 => 'Second Activity'
            ]);
        // TODO - Implement activities for a channel - For time being some array is being returned from respective controller

    }

    /** @test */
    function a_user_can_not_see_activity_from_the_channels_he_does_not_belong()
    {

        $this->actingAs($this->firstUser)->json('GET',
            route('channel.activity',['workspace_id' => $this->workspace->id, 'channel_id' => $this->channel->id]))
             ->assertStatus(422);
    }
}
