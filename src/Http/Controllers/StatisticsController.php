<?php

namespace Laravel\Telescope\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Storage\EntryModel;
use Laravel\Telescope\Storage\EntryQueryOptions;
use Laravel\Telescope\Storage\GlobalStatus;
use Laravel\Telescope\Watchers\StatisticsWatcher;

class StatisticsController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::STATISTICS;
    }

    /**
     * Get all of the tags being monitored.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, EntriesRepository $storage)
    {
        return response()->json([
            'statistics' => [
                'WPS' => [
                    'label' => 'Web Request Per Second',
                    'value' => $this->wps()
                ],
                'APS' => [
                    'label' => 'API Request Per Second',
                    'value' => $this->aps()
                ],
                'UPTIME' => [
                    'label' => 'MySQL Uptime',
                    'value' => $this->uptimeForHumans()
                ],
                'QPS' => [
                    'label' => 'MySQL Query Per Second',
                    'value' => GlobalStatus::qps()
                ],
                'TPS' => [
                    'label' => 'MySQL Transaction Per Second',
                    'value' => GlobalStatus::tps()
                ],
                'SLQ' => [
                    'label' => 'MySQL Slow Queries',
                    'value' => GlobalStatus::slowQueries()
                ],
            ],
            'status' => $this->status(),
        ]);
    }

    protected function uptimeForHumans()
    {
        $uptime = GlobalStatus::uptime();
        return CarbonInterval::seconds($uptime)->cascade()
            ->locale('en')->forHumans();
    }

    protected function wps()
    {
        return round($this->lastMinuteRequests()->count() / 60, 3);
    }

    protected function aps()
    {
        return round($this->lastMinuteRequests()->filter(function ($row) {
                return str_starts_with($row->content['uri'], '/api');
            })->count() / 60, 3);
    }

    protected function lastMinuteRequests()
    {
        return EntryModel::query()
            ->where('type', EntryType::REQUEST)
            ->where('created_at', '>=', Carbon::now()->subMinute())->get();
    }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher()
    {
        return StatisticsWatcher::class;
    }

}
