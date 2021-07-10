<?php


namespace Laravel\Telescope\Storage;


use Illuminate\Support\Facades\DB;

class GlobalStatus
{
    public static function qps()
    {
        $Queries = self::get('Queries');
        $qps = $Queries / self::uptime();
        return round($qps, 3);
    }
    public static function tps()
    {
        $HandlerCommit = self::get('Handler_commit');
        $HandlerRollbacks = self::get('Handler_rollbacks');
        $tps = ($HandlerCommit + $HandlerRollbacks) / self::uptime();
        return round($tps, 3);
    }
    public static function uptime()
    {
        return self::get('Uptime');
    }
    public static function slowQueries()
    {
        return self::get('Slow_Queries');
    }
    protected static function get(string $variable)
    {
        $result = DB::table('performance_schema.global_status')
            ->select('VARIABLE_VALUE')
            ->where('VARIABLE_NAME', $variable)->first();
        return $result->VARIABLE_VALUE ?? null;
    }
}
