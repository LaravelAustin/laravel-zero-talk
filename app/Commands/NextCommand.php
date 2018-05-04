<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class NextCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'next';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get the next Laravel-Austin meetup details';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $meetups = collect(
            json_decode(
                (string)$this->client->get('/Laravel-Austin/events', [
                    'query' => [
                        'page' => 20,
                        'status' => 'upcoming',
                    ],
                ])->getBody(),
                true
            )
        );

        $meetup = $meetups->first();

        $this->info('Meetup ID: ' . array_get($meetup, 'id'));
        $this->info('The next Laravel Austin Meetup is ' . array_get(
                $meetup,
                'local_date'
            ) . ' at ' . array_get($meetup, 'local_time'));
        $this->info('The meetup will be about: ' . strip_tags(array_get($meetup, 'description')));
        $this->info('The meetup will be at ' . array_get($meetup, 'venue.name'));
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
