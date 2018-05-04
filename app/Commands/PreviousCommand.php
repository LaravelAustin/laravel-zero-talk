<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class PreviousCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'previous';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get the previous Laravel-Austin meetup';

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
                        'status' => 'past',
                        'desc' => true,
                    ],
                ])->getBody(),
                true
            )
        );

        $meetup = $meetups->first();

        $this->info('Meetup ID: ' . array_get($meetup, 'id'));
        $this->info('The last Laravel Austin Meetup was ' . array_get(
                $meetup,
                'local_date'
            ) . ' at ' . array_get($meetup, 'local_time'));
        $this->info('The meetup was about: ' . strip_tags(array_get($meetup, 'description')));
        $this->info('The meetup was at ' . array_get($meetup, 'venue.name'));
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
