<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Message;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Emoji\Emoji;
use Tests\TestCase;

class MessageTest extends TestCase
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
    function a_user_can_send_a_message_to_a_channel_if_he_belongs_to_channel()
    {
        $this->channel->users()->attach($this->firstUser->id);
        $this->actingAs($this->firstUser)->json('POST', route('message.create'),
            [
                'content' => 'This is an awesome message for you',
                'workspace_id' => $this->workspace->id,
                'channel_id' => $this->channel->id
            ])
             ->assertOk();
        $this->assertDatabaseHas('messages', [
            'content' => 'This is an awesome message for you',
            'channel_id' => $this->channel->id,
            'user_id' => $this->firstUser->id
        ]);
    }

    /** @test */
    function a_user_can_not_send_a_message_to_a_channel_if_he_does_not_belongs_to_channel()
    {
        $this->channel->users()->detach($this->firstUser->id);
        $this->actingAs($this->firstUser)->json('POST', route('message.create'),
            [
                'content' => 'This is an awesome message for you',
                'workspace_id' => $this->workspace->id,
                'channel_id' => $this->channel->id
            ])
             ->assertStatus(422);
    }

    /** @test */
    function a_message_belongs_to_a_user()
    {
        $this->channel->users()->attach($this->firstUser->id);
        $message = Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->create();

        $this->assertTrue($this->firstUser->is($message->user));
    }

    /** @test */
    function a_message_belongs_to_a_channel()
    {
        $this->channel->users()->attach($this->firstUser->id);
        $message = Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->create();

        $this->assertTrue($this->channel->is($message->channel));
    }

    /** @test */
    function a_user_can_have_multiple_message()
    {
        $this->channel->users()->attach($this->firstUser->id);
        Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->count(2)->create();

        tap(User::with('messages')->find($this->firstUser->id)->messages, function($messages) {
            $this->assertCount(2, $messages);
        });
    }

    /** @test */
    function a_channel_can_have_multiple_message()
    {
        $this->channel->users()->attach($this->firstUser->id);
        Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->count(4)->create();

        tap(Channel::with('messages')->find($this->channel->id)->messages, function($messages) {
            $this->assertCount(4, $messages);
        });
    }

    /** @test */
    function a_user_can_access_all_message_of_a_channel_if_he_is_a_member_of_that_channel()
    {
        $this->channel->users()->attach([$this->firstUser->id, $this->secondUser->id]);

        Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->count(2)->create();

        Message::factory([
            'user_id' => $this->secondUser->id,
            'channel_id' => $this->channel->id
        ])->count(2)->create();

        $this->actingAs($this->firstUser)->json('GET', route('message.index', [
            'workspace_id' => $this->workspace->id,
            'channel_id' => $this->channel->id
        ]))
             ->assertOk()
            ->assertJsonCount(4);
    }

    /** @test */
    function a_user_can_not_access_message_of_a_channel_if_he_is_not_a_member_of_that_channel()
    {
        $this->channel->users()->attach([$this->firstUser->id]);

        Message::factory([
            'user_id' => $this->firstUser->id,
            'channel_id' => $this->channel->id
        ])->count(2)->create();

        $this->actingAs($this->secondUser)->json('GET', route('message.index', [
            'workspace_id' => $this->workspace->id,
            'channel_id' => $this->channel->id
        ]))
             ->assertStatus(422);
    }

    /** @test */
    function it_can_store_emoji_in_the_message_content()
    {
        $this->channel->users()->attach([$this->firstUser->id]);

        $this->actingAs($this->firstUser)->json('POST', route('message.create'),
            [
                'content' => Emoji::smilingFaceWithHearts() . ' Emoji Works',
                'workspace_id' => $this->workspace->id,
                'channel_id' => $this->channel->id
            ])
             ->assertOk();
        tap(Message::first(), function($message) {
            $this->assertSame(Emoji::smilingFaceWithHearts() . ' Emoji Works', $message->content);
        });
    }
}
