<?php

namespace App\Console\Commands\Date;

use App\Models\Date;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FillHideDayMonthFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'date:fill-hide-day-and-month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Комманда заполняет hide_day и hide_mount в таблице date у полей, где значение месяца или даты = 1';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды обработки дат');

        $dates = Date::query()->get();

        foreach ($dates as $date) {
            $carbonParse = Carbon::parse($date->date);
            $saveChange = false;

//            $date->hide_month = false;
//            $date->hide_day = false;
//            $saveChange = true;

            if ($carbonParse->day === 1) {
                $date->hide_day = true;

                if ($carbonParse->month === 1) {
                    $date->hide_month = true;
                }

                $saveChange = true;
            }

            if ($saveChange) {
                $this->line('Изменение данных в дате ' . $date->date);
                $date->save();
            }
        }

        $this->line('КОНЕЦ работы комманды обработки дат');

        return 0;
    }
}
