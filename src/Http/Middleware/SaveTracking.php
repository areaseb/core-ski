<?php

namespace Areaseb\Core\Http\Middleware;

use Closure;
use Areaseb\Core\Models\Report;

class SaveTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(request()->has('tracker'))
        {
            $report = Report::identify(request('tracker'));
            if(request()->has('link'))
            {
                $arr = [];
                $key = intval(request('link'));
                if(!is_null($report->clicks))
                {
                    $arr = $report->clicks;
                    foreach ($arr as $key => $value)
                    {
                        if($value['number'] === $key)
                        {
                            return $next($request);
                        }
                    }
                }
                $link = [
                    'number' => $key,
                    'url' => url()->current()
                ];
                $arr[] = $link;
                $report->clicks = $arr;
                $report->save();
            }
        }
        return $next($request);
    }
}
