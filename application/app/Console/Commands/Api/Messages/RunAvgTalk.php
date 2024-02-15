<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Message;
use App\Models\Talk;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunAvgTalk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:talk {talk_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $talks = Talk::query()
            ->select(['time'])
            ->where('talk_id', $this->argument('talk_id'))
            ->whereBetween('out_at', [
                Carbon::now()->subDays(1000)->format('Y-m-d H:i:s'),
                Carbon::now()->format('Y-m-d H:i:s'),
            ])
            ->get();

        $info = [
            'name'  => 'name',
            'avg'   => $talks->count() > 0 ? round($talks->sum('time') / $talks->count(), 1) : 0,
            'count' => Message::query()
//                ->where('responsible_user_id', $staff->staff_id)
                ->where('talk_id', $this->argument('talk_id'))
                ->where('type', 'out')
                ->count(),
        ];

        dd($info);

        return Command::SUCCESS;
    }
}
