<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Platform\Order;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function consultations(Request $request)
    {
        $account = Account::query()
            ->where('subdomain', 'matematikandrei')
            ->first();

        $amoApi = (new Client($account))->init();

        $contact = Contacts::search([
            'Телефон' => Contacts::clearPhone($request->phone),
            'Почта'   => $request->email ?: null,
        ], $amoApi);

        if (!$contact)
            $contact = Contacts::create($amoApi, $request->name);

        $contact = Contacts::update($contact, [
            'Почта' => $request->email,
            'Телефоны' => [$request->phone],
        ]);

        $lead = Leads::create($contact, [
            'responsible_user_id' => 5998951,
            'pipeline_id' => Order::OP_PIPELINE_ID,
        ], 'Новая заявка с сайта на консультацию');

        $lead->cf('Источник обращения')->setValue('Заявка с сайта на консультацию');

        $lead->attachTag('Сайт');
        $lead->save();

        Log::info(__METHOD__.' lead_id : '.$lead->id);

        $lead->cf('utm_source')->setValue($request->utm_source);
        $lead->cf('utm_content')->setValue($request->utm_content);
        $lead->cf('utm_medium')->setValue($request->utm_medium);
        $lead->cf('utm_campaign')->setValue($request->utm_campaign);
        $lead->cf('utm_term')->setValue($request->utm_term);
        $lead->save();

        $text = [
            'Новая заявка на консультацию!',
            '-----------------------------',
            ' - Имя : '. $request->name ?: '-',
            ' - Почта : '. $request->email ?: '-',
            ' - Телефон : '. $request->phone ?: '-',
            '-----------------------------',
            ' - Способ связи : '.$request->social_type ?: '-',
            '-----------------------------'
        ];

        $note = $lead->createNote($type = 4);
        $note->text = implode("\n", $text);
        $note->element_type = 2;
        $note->element_id = $lead->id;
        $note->save();
    }
}
