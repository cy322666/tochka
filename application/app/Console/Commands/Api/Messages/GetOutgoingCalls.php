<?php

namespace App\Console\Commands\Api\Messages;

use App\Models\Account;
use App\Models\Message;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetOutgoingCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:outgoing';

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
     * @throws \Exception
     */
    public function handle()
    {
        $amoApi = (new Client(account: Account::query()->first()))->init();

        for ($i = 1 ; ; $i++, sleep(1)) {

            try {

                $messages = $amoApi->service->ajax()->get('/api/v4/events', [
                    'limit' => 100,
                    'page'  => $i,
                    'filter' => [
                        'type' => 'outgoing_chat_message',
                        'created_at' => [
                            'from' => Carbon::now()->subDay()->timestamp,
                            'to'   => Carbon::now()->timestamp,
                        ]
                    ],
                ]);

                if (empty($messages->_embedded->events[0])) exit;

            } catch (\Throwable $e) {

//                Log::error(__METHOD__, [$e->getMessage().' '.$e->getFile().' '.$e->getLine()]);
            }

            foreach ($messages->_embedded->events as $message) {

                try {

                    if (!$message->created_by == 0 && $message->type !== 'talk_created')

                        Message::query()->create([
                            'responsible_user_id' => $message->created_by,
                            'element_type' => $message->entity_type,
                            'message_id' => !empty($message->value_after[0]) ? $message->value_after[0]->message->id : $message->id,
                            'talk_id'    => !empty($message->value_after[0]) ? $message->value_after[0]->message->talk_id : null,
                            'element_id' => $message->entity_id,
                            'entity_id'  => $message->created_at,
                            'type'       => 'out',
                            'origin'     => !empty($message->value_after[0]) ? $message->value_after[0]->message->origin : null,
                            'msg_at'     => Carbon::parse($message->created_at)->format('Y-m-d H:i:s'),
                            'msg_date_at'     => Carbon::parse($message->created_at)->format('Y-m-d'),
                            'msg_time_at'     => Carbon::parse($message->created_at)->format('H:i:s'),
                        ]);

//                    else
//                        dump($message->created_by, $message->type);

                } catch (\Throwable $e) {

//                    continue;

//                    Log::error(__METHOD__, [$e->getMessage().' '.$e->getFile().' '.$e->getLine()]);
                }
            }
        }

//        dd('kek');

        /*
{#2585
        +"id": "01h2t1n2agdefchnpr7hzf2s91"
        +"type": "incoming_chat_message"
        +"entity_id": 4140631
        +"entity_type": "lead"
        +"created_by": 0
        +"created_at": 1686648818
        +"value_after": array:1 [
          0 => {#2587
            +"message": {#2586
              +"id": "d5a4677e-8a70-4e0e-bd21-8c857289f38c"
              +"origin": "com.wazzup24.wz"
              +"talk_id": 1109
            }
          }
        ]
        }
      }


    "linkedLead" => null
    "linkedContact" => null
    "linkedCompany" => null
    "createdUser" => null
    "responsibleUser" => null
    "noteType" => null
    "element_id" => 27345667
    "element_type" => 2
    "is_editable" => false
    "note_type" => 10
    "text" => null
    "responsible_user_id" => 9475790
    "updated_at" => 1687256839
    "created_at" => 1687256724
    "created_by" => 9310902
    "attachment" => null
    "params" => {#2434
      +"PHONE": "+79162285869"
      +"UNIQ": "MToxMDEzODgzMToyMDI6ODQ1NDA0ODE4"
      +"DURATION": 58
      +"SRC": "MangoOfficeWidget"
      +"LINK": "https://amocrm.mango-office.ru/calls/recording/download/28929382/MToxMDEzODgzMToxNzc2ODIwNjE1MDow/NDAzMzc3Nzk2"
      +"call_status": 4
      +"call_result": "входящий"
    }
  ]

         */

        return Command::SUCCESS;
    }
}
