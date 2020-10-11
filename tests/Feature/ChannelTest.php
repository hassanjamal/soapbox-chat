<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Emoji\Emoji;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $workSpace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([])->create();
        $this->workSpace = Workspace::factory()->create();
        $this->workSpace->users()->attach($this->user->id);
    }

    /** @test */
    function user_can_create_a_channel()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user)->json('POST', route('channel.store'), [
            'name' => 'ABC Channel',
            'description' => 'This is description for ABC channel',
            'workspace_id' => $this->workSpace->id
        ])->assertOk();

        $this->assertDatabaseHas('channels', [
            'name' => 'ABC Channel',
            'description' => 'This is description for ABC channel'
        ]);
    }

    /** @test */
    function a_channel_belongs_to_workspace()
    {
        $this->withoutExceptionHandling();

        Channel::factory([
            'workspace_id' => $this->workSpace->id
        ])->create();

        tap(Channel::first(), function($channel) {
            $this->assertTrue($this->workSpace->is($channel->workspace));
        });

    }

    /** @test */
    function channel_name_is_required_to_create_a_channel()
    {
        $this->actingAs($this->user)->json('POST',
            route('channel.store'), [
                'name' => '',
                'description' => 'Channel Description',
                'workspace_id' => $this->workSpace->id
            ])
             ->assertStatus(422)
             ->assertJsonValidationErrors('name');

    }

    /** @test */
    function workspace_is_required_to_create_a_channel()
    {
        $this->actingAs($this->user)->json('POST',
            route('channel.store'), [
                'name' => 'ABC Channel',
                'description' => 'Channel Description',
                'workspace_id' => ''
            ])
             ->assertStatus(422);

    }

    /** @test */
    function it_can_store_emoji_in_the_channel_description()
    {
        $this->actingAs($this->user)->json('POST',
            route('channel.store'), [
                'name' => 'ABC Channel',
                'description' => Emoji::smilingFaceWithHearts() . ' Emoji Works',
                'workspace_id' => $this->workSpace->id
            ])
             ->assertOk();

        tap(Channel::first(), function($channel) {
            $this->assertSame('ABC Channel', $channel->name);
            $this->assertSame(Emoji::smilingFaceWithHearts() . ' Emoji Works', $channel->description);
        });
    }
}
