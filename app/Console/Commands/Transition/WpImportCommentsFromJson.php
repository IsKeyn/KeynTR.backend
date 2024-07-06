<?php

namespace App\Console\Commands\Transition;

use App\Models\Comments;
use App\Models\OldSiteMember;
use App\Services\UserAgentService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class WpImportCommentsFromJson extends Command
{
    protected $signature = 'wp:importCommentsFromJson {entityType} {entityId}';
    protected $description = 'Команда позволяет импортировать комментария из WordPress, комманда принимает entityType и entityId';

    public function handle()
    {
        $adminEmail = 'keyn-artur@yandex.ru';
        $adminId = 2;

        $this->line('НАЧАЛО загрузки комментариев в сущность ' . $this->argument('entityType') . 'c' . 'ID:' . $this->argument('entityId'));

        $wpComments = json_decode(Storage::disk('json')->get('comments.json'));

        $thunderComments = [];
        $count = 0;

        foreach ($wpComments as $wpComment) {
            $thunderComment = [
                'name' => $wpComment->comment_author,
                'email' => $wpComment->comment_author_email,
                'url' => $wpComment->comment_author_url,
                'message' => $wpComment->comment_content,
                'created_at_gmt' => $wpComment->comment_date_gmt,
            ];

            if ($wpComment->comment_date) {
                $thunderComment['created_at'] = $wpComment->comment_date;
            }

            if (strtolower($wpComment->comment_author_email) === strtolower($adminEmail)) {
                $thunderComment['created_by'] = $adminId;
            }

            $thunderComment['entity_type'] = $this->argument('entityType');
            $thunderComment['entity_id'] = $this->argument('entityId');

            $userAgentData = [
                'ip' => $wpComment->comment_author_IP,
                'user_agent' => $wpComment->comment_agent,
            ];

            if ($comment = Comments::create($thunderComment)) {
                UserAgentService::create($userAgentData['ip'], '', $userAgentData['user_agent'], $comment);
            }

            $count++;
            $this->setOldSiteMember($wpComment);
        }

        $this->line('КОНЕЦ загрузки комментариев, добавлено ' . $count . ' комментариев');
        return Command::SUCCESS;
    }

    public function setOldSiteMember($wpComment)
    {
        $oldSiteMember = OldSiteMember::where('email', $wpComment->comment_author_email)->where('type', OldSiteMember::FROM_COMMENTS)->first();

        if ($oldSiteMember) {
            if (!in_array($wpComment->comment_author, $oldSiteMember->names)) {
                $oldSiteMember->names = array_merge($oldSiteMember->names, [$wpComment->comment_author]);
            }

            $date1 = Carbon::parse($oldSiteMember->first_message_date);
            $date2 = Carbon::parse($wpComment->comment_date);

            if ($date2 < $date1) {
                $oldSiteMember->first_message_date = $wpComment->comment_date;
            }

            $oldSiteMember->message_count = ++$oldSiteMember->message_count;
            $oldSiteMember->save();
        } else {
            OldSiteMember::create([
                'names' => [$wpComment->comment_author],
                'email' => $wpComment->comment_author_email,
                'type' => OldSiteMember::FROM_COMMENTS,
                'first_message_date' => $wpComment->comment_date,
                'message_count' => 1,
            ]);
        }
    }
}
