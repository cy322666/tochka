<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Message;
use App\Models\Staff;
use App\Models\Talk;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CalculateAvgOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:avg {talk_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $messages = Message::query()
            ->select(['id', 'msg_at', 'type', 'msg_time_at', 'msg_date_at', 'responsible_user_id'])
            ->where('talk_id', $this->argument('talk_id'))
            ->whereBetween('msg_time_at', ['09:00:00', '21:00:00'])
            ->orderByDesc('msg_at')
            ->get();

        $responsible = $messages->first(
            fn($message) => $message->type == 'out')?->responsible_user_id;

        for ($outMsgInfo = [];
             $messages->count() > 0;
             $messages = static::getSliceMsgCollection($messages, $msgOut ?? null)
        ) {

            $msgIn  = static::getFirstIn($messages);
            $msgOut = static::getFirstOut($messages, $msgIn);

            if (!$msgIn) break;

            if (!$msgOut) break; //TODO висяки?

            $responsible = $msgOut->responsible_user_id;

            $outMsgInfo[] = [
                'out_id' => $msgOut->id ?? null,
                'in_id'  => $msgIn->id ?? null,
                'out_at' => $msgOut->msg_at ?? null,
                'in_at'  => $msgIn->msg_at ?? null,
                'time'   => static::getAvgMsg($msgIn, $msgOut)?->format('%s') ?? null,
                'talk_id' => $this->argument('talk_id'),
                'responsible_user_id' => $responsible,
            ];
        }

        if (count($outMsgInfo) > 0) {

            foreach ($outMsgInfo as $info) {

                try {

                    Talk::query()->create($info);

                } catch (\Throwable $e) {

                    continue;
                }
            }
        }

        return CommandAlias::SUCCESS;
    }

    private static function getSliceMsgCollection(Collection $messages, ?Message $msgOut): Collection
    {
        if ($msgOut)
            return $messages->filter(function($message) use ($msgOut) {

                return $message->msg_at < $msgOut->msg_at;
            });
        else
            return $messages;
    }

    /**
     * @param Collection $messages
     * @return ?Message
     */
    private static function getFirstOut(Collection $messages, ?Message $firstIn) : ?Message
    {
        return
            $firstIn ?
            $messages
                ->where('type', 'out')
                ->where('msg_at', '<', $firstIn->msg_at)
                ->first()
            : null;
    }

    private static function getFirstIn(Collection $messages) : ?Message
    {
        return $messages->where('type', 'in')->first();
    }

    private static function getAvgMsg(?Message $inMsg, ?Message $outMsg) : ?\DateInterval
    {
        return $inMsg && $outMsg ? Carbon::parse($inMsg->msg_at)->diff($outMsg->msg_at) : null;
    }
}
