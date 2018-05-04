<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class AttendanceCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'attendance 
                            { meetup_id : The ID of the meetup you want attendance for }
                            { --upcoming : List the attendees of an upcoming meetup }
                            { --debug : Prints out debug output }';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Gets the attendance of the provided meetup';

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
        $apiUrl = 'Laravel-Austin/events/{meetup_id}/';
        $apiUrl .= $this->option('upcoming') ? 'rsvps' : 'attendance';

        $attendees = collect(
            json_decode(
                (string)$this->client->get(Str::replaceFirst('{meetup_id}', $this->argument('meetup_id'), $apiUrl), [
                    'order' => 'name',
                ])->getBody(),
                true
            )
        );

        if ($this->option('debug')) {
            dd($attendees);
        }

        $rsvpKey = $this->option('upcoming') ? 'response' : 'rsvp.response';
        $guestsKey = $this->option('upcoming') ? 'guests' : 'rsvp.guests';

        $this->table(['Name', 'RSVP', 'Guests'], $attendees->map(function (array $attendee) use ($rsvpKey, $guestsKey) {
            return [
                'Name' => array_get($attendee, 'member.name'),
                'RSVP' => array_get($attendee, $rsvpKey) === 'yes' ? '<info>✔</info>' : '<error>X</error>',
                'Guests' => array_get($attendee, $guestsKey),
            ];
        }));
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
