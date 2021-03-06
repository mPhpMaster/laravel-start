<?php
/*
 * Copyright © 2021. mPhpMaster(https://github.com/mPhpMaster) All rights reserved.
 */

namespace mPhpMaster\LaravelStart\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/**
 * Class JSRoutes
 *
 * @package mPhpMaster\LaravelStart\Controllers
 */
class JSRoutes
{
    /**
     * Returns routes.
     *
     * @return \Illuminate\Support\Collection
     */
    protected static function getRoutes()
    {
        $routeCollection = Route::getRoutes();
        return collect($routeCollection)->mapWithKeys(function ($value) {
            if (!trim($routeName = $value->getName())) return [false];

            return [
                $routeName => $value->uri
            ];
        })->filter();
    }

    /**
     * Returns javascript code includes the routes.
     *
     * @return \Illuminate\Http\Response
     */
    public function routes()
    {
        $ret = self::getRoutes();

        $contents = View::make('js_routes')->with('content', $ret);
        $response = Response::make($contents, 200);
        $response->header('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * Print out the routes table
     *
     * @param Request $request
     *
     * @param null|string $name find route by name
     *
     * @return \Illuminate\Http\Response
     */
    public function print_routes(Request $request, $name = null)
    {
        $params = collect($request->all());
        $_params = collect();
        $contents = "<span><a href='".($ccURL = $request->getUri())."'>HOME</a></span><span><b> | </b></span>";
        $contents .= $name ? "<span><a href='".str_before($request->getUri(), "/{$name}")."'>../{$name}</a></span><span><b> | </b></span>" : "";
        $contents .= "<span><a href='".($ccURL = $request->fullUrlWithQuery(['--columns' => implode('|', ['Method', 'Name', 'URI', 'Action'])]))."'>Compact Columns</a></span><span><b> | </b></span>";
        $contents .= "<span><a href='javascript:void(0);' onclick='var u=prompt(\"Show Only Columns: \", \"Method|Name|URI|Action|Middleware\");u && (location.href = \"".\route('print_routes_no_ns', '')."?--columns=\"+u);'>Select Columns</a></span><span><b> | </b></span>";
        $contents .= "<span><a href='javascript:void(0);' onclick='var u=prompt(\"Select Name: \", \"{$name}\");u && (location.href = \"".\route('print_routes_no_ns', '')."/\"+u+\"?{$request->getQueryString()}\");'>Select Name</a></span>";

        if ($first_param = $params->first()) {
            $delimiter = stripos($first_param, ";") === false ? (stripos($first_param, "&") === false ? " " : "&") : ";";
            $_params = collect(explode($delimiter, $first_param));
            $first_key = $params->keys()->first();
            if (!is_null($first_key)) {
                $_val = $_params->shift();
                $params->put($first_key, $_val);
            }

            $_params = $_params->flatMap(function ($value) {
                $value = collect(explode("=", $value));
                $_val = $value->first() ?: true;
                return [
                    $value->shift() => $_val
                ];
            });
        }

        if (!is_null($name)) {
            $params->put('--name', $name);
        }

        $command_params = $params->merge($_params)->mapWithKeys(function ($v, $k) {
            if($k === '-cc') {
                return ['--columns' => ['Method','Name','URI','Action']];
            }
            $_val = stripos($v, '|') === false ? $v : explode('|', $v);
            return [$k => $_val];
        })->toArray();

//        if (empty($command_params)) {
            Artisan::call("route:list", ['--help' => 1]);

            $contents .= "<div style='overflow: auto; font-size: small;'><pre>";
            $contents .= Artisan::output();
            $contents .= "</pre></div>";
//        }

        Artisan::call("route:list", $command_params ?: ['--columns' => ['Method','Name','URI','Action']]);

        $contents .= "<div style='overflow: auto; background-color: black; color: white; font-weight: bold; line-height: 2em; font-size: 18px;'><pre>";
        $contents .= Artisan::output();
        $contents .= "</pre></div>";

        $response = Response::make($contents, 200);
        $response->header('Content-Type', 'text/html');

        return $response;
    }
}
