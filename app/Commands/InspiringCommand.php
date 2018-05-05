<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use NunoMaduro\LaravelConsoleMenu\Menu;

class InspiringCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'inspiring {name=Artisan}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Getting the next meetup for Laravel-Austin');
        /** @var Client $client */
        $client = app(Client::class);

        $meetup = collect(
            json_decode((string)$client->get('/Laravel-Austin/events', [
                'query' => [
                    'page' => '20',
                    'status' => 'past',
                    'desc' => true,
                ],
            ])->getBody(), true)
        )->first();

        $options = [
            'List Attendance',
            'RSVP',
            'Comment',
        ];

        /** @var Menu $menu */
        $menu = $this->menu('Meetup Options');
        $option = $menu->addStaticItem(
            'Next meetup is: ' . array_get($meetup, 'local_date', '') . ' at ' . array_get($meetup, 'local_time', '')
        )->addStaticItem(
            'Description: ' . strip_tags(array_get($meetup, 'description', ''))
        )->addOptions($options)->open();

        if ($option !== 0) {
            return;
        }

        $attendees = collect(
            json_decode((string)$client->get('/Laravel-Austin/events/' . array_get($meetup, 'id') . '/attendance', [
                'query' => [
                    'order' => 'name',
                ],
            ])->getBody(), true)
        );

        $this->table(['Name', 'RSVP', 'Guests'], $attendees->map(function (array $attendee) {
            return [
                'Name' => array_get($attendee, 'member.name'),
                'RSVP' => array_get($attendee, 'rsvp.response'),
                'Guests' => array_get($attendee, 'rsvp.guests'),
            ];
        }));
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
