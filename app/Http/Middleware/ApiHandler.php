<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use DB;
use Config;
use Illuminate\Support\Facades\Log;
use AppLog;
use App\Services\Traits\ResponseCodeTrait;

class ApiHandler
{
    use ResponseCodeTrait;

     /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $timeStart = microtime(true);

        /* check for X-Request-ID header */
        $request_id = ( isset($request->header()['x-request-id'][0]) ) ? $request->header()['x-request-id'][0]: NULL;
        if( empty($request_id) ){
            $result = $this->getResponseCode(103);
            $http_code = $result['http_code'];
            unset($result['http_code']);
            return response()->json($result,$http_code);
        }

        AppLog::setRequestId($request_id);

        /* Check for query logging */
        $enableQueryLog = Config::get('constants.enable_query_logging');
        if ($enableQueryLog === true) {
            DB::enableQueryLog();
        }

        /* Logging Api params,headers,server values */
        Log::channel('apilog')->info($request_id.'|request_url', [$request->server()['REQUEST_URI']]);
        if( !empty($request->all()) ){
            Log::channel('apilog')->info($request_id.'|request_parameters', $request->all());
        }
        Log::channel('apilog')->debug($request_id.'|header_parameters', $request->header());
        Log::channel('apilog')->debug($request_id.'|server_parameters', $request->server());


        /* handover to route and controller */
        $response = $next($request);

        /* log queries */
        if ($enableQueryLog === true) {
            $queries = DB::getQueryLog();
            $count = count($queries);
            if($count > 0){
                Log::channel('querieslog')->debug($request_id.'|queries',$queries);
            }
        }

        /* calculate Bendmarks */
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        $memory = (memory_get_peak_usage(true)/1024/1024);

        Log::channel('apilog')->info($request_id.'|reponse', (array) json_decode($response->content(),true) );
        Log::channel('apilog')->info($request_id.'|benchmarks',['execution_time'=>$time.' Secs','memory'=>$memory.' MiB']);

        return $response;
    }

}
